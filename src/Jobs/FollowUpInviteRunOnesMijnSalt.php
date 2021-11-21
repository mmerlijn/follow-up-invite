<?php

namespace mmerlijn\followUpInvite\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\FollowUpTest;
use mmerlijn\followUpInvite\Models\MijnSaltPatient;
use mmerlijn\followUpInvite\Models\MijnSaltRequester;

class FollowUpInviteRunOnesMijnSalt implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter = 5;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addSeconds(60);
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //alle actieve
        $mijnsalt_db = "db_salt12";
        $patient_van = 29688;
        $patient_tot = 35000 + $patient_van;
        foreach (\DB::table($mijnsalt_db . '.fun_patient')->whereActief(1)
                     ->whereBetween('patientId', [$patient_van, $patient_tot])->get() as $patient) {
            //fundus patient aanmaken
            $reason_def = [];
            $last_test = [];
            $stop_def = null;
            if ($patient->oogarts) {
                $reason_def[] = 'OOGARTS';
                $stop_def = Carbon::create($patient->gewijzigd_op);
            }
            if ($patient->wil_niet) {
                $reason_def[] = 'WIL_NIET';
                $stop_def = Carbon::create($patient->afstel_door);
            }
            if ($patient->geen_diabetes) {
                $reason_def[] = "NO_DIABETES";
                $stop_def = Carbon::create($patient->gewijzigd_op);
            }
            if ($patient->drp) {
                $last_test[] = "DRP";
            }
            $fup = FollowUpPatient::create([
                'mijnsalt_id' => $patient->contactId,
                'type' => 'fundus',
                'last_test_at' => null,
                'days_between' => $patient->uitnodigen_om,
                'last_invitation_at' => $patient->uitnodiging,
                'last_reminder_invitation_at' => $patient->uitnodiging_her,
                'wait_until' => $patient->uitstel_oproep_na_datum,
            ]);
            $details = $fup->detail->data;
            //voorgaande invitations toevoegen aan details
            if ($patient->uitnodiging) {
                $details['letters'][$fup->last_invitation_at->format('Y-m-d')] = [
                    'note' => 'first', 'at' => $fup->last_invitation_at->format('Y-m-d')
                ];
            }
            if ($patient->uitnodiging_her) {
                $details['letters'][$fup->last_reminder_invitation_at->format('Y-m-d')] = [
                    'note' => 'second', 'at' => $fup->last_reminder_invitation_at->format('Y-m-d')
                ];
            }

            //zoeken van bijhorende afspraken
            foreach (\DB::table($mijnsalt_db . '.agd_afspraak')
                         ->join($mijnsalt_db . '.agd_planning', $mijnsalt_db . '.agd_afspraak.planningId', $mijnsalt_db . '.agd_planning.planningId')
                         ->where($mijnsalt_db . '.agd_afspraak.activiteitId', config('fuinvite.tests.fundus.activity_id'))
                         ->where('contactId', $patient->contactId)
                         ->orderBy('datum')
                         ->cursor() as $afspraak) {
                $fup->last_appointment_at = $afspraak->datum;
                $reason = [];
                $details['appointments'][$afspraak->datum] = ['note' => 'open', 'at' => $afspraak->datum];
                if ($afspraak->verzuim) {
                    $details['appointments'][$afspraak->datum] = ['note' => 'no show', 'at' => $afspraak->datum];
                    $reason[] = 'NO_SHOW';
                    $fup->stop = $afspraak->datum;
                }
                if ($afspraak->mislukt) {
                    $details['appointments'][$afspraak->datum] = ['note' => 'failed', 'at' => $afspraak->datum];
                    $fup->stop = null;
                }
                if (strstr($afspraak->opmerking, 'fake:')) {
                    $details['appointments'][$afspraak->datum] = ['note' => 'done', 'at' => $afspraak->datum];
                    $fup->last_test_at = $afspraak->datum;
                    $fup->last_visit_location = $afspraak->locatieId;

                    $fup->next_invitation_at = Carbon::create($afspraak->datum)
                        ->addDays($fup->days_between - config('fuinvite.tests.fundus.invite_days_before')); //->format('Y-m-d');
                    $fup->next_test_at = Carbon::create($afspraak->datum)
                        ->addDays($fup->days_between);
                    $arts = \DB::table($mijnsalt_db . '.app_arts')
                        ->join($mijnsalt_db . '.app_contact', $mijnsalt_db . '.app_arts.artsId', $mijnsalt_db . '.app_contact.artsId')
                        ->where('contactId', $patient->contactId)
                        ->first();
                    $fup->requester = $arts->agbcode;
                    if ($arts->sag) {
                        $fup->provider = 7; //'SAG'
                    }
                    if ($arts->sez) {
                        $fup->provider = 6; //SEZ
                    }
                    if ($arts->diazon) {
                        $fup->provider = 3; //DIAZON /HZNK
                    }
                }
            }
            //zoeken behorende onderzoeken
            foreach (\DB::table($mijnsalt_db . '.fun_onderzoek')
                         ->where('contactId', $patient->contactId)
                         ->whereNotNull($mijnsalt_db . '.fun_onderzoek.beoordeling')
                         ->orderBy('datumonderzoek')
                         ->cursor() as $onderzoek) {

                $requester = \DB::table($mijnsalt_db . '.app_arts')
                    ->where('artsId', $onderzoek->aanvrager_onderzoek)
                    ->first();
                $fup->last_test_at = $onderzoek->datumonderzoek;
                $fup->days_between = $this->fundusFollowUp[$onderzoek->followup];
                $fup->last_visit_location = $onderzoek->locatieId;
                $fup->provider = $onderzoek->zorggroep;
                $fup->requester = $requester->agbcode ?? null;

                if (!$this->fundusFollowUp[$onderzoek->followup]) {
                    $stop = $onderzoek->datumonderzoek;
                    $reason[] = 'OOGARTS';
                } else {
                    $fup->next_invitation_at = Carbon::create($onderzoek->datumonderzoek)
                        ->addDays($fup->days_between - config('fuinvite.tests.fundus.invite_days_before')); //->format('Y-m-d');
                    $fup->next_test_at = Carbon::create($onderzoek->datumonderzoek)
                        ->addDays($fup->days_between);
                }
                $this->storeOnderzoekDetails($details, $onderzoek, $fup->requester);
                if ($onderzoek->diabetische_retinopatie_l > 250 or $onderzoek->diabetische_retinopatie_r > 250) {
                    $last_test = ['DRP'];
                }
            }

            if (count($reason_def)) {
                $fup->reason = array_unique($reason_def);
                $fup->stop = $stop_def;
            } elseif (count($reason)) {
                $fup->reason = array_unique($reason);
                $fup->stop = $stop;
            } else {
                $fup->reason = null;
                $fup->stop = null;
            }
            //er is geen requester bekend dus huidige huisarts proberen toe te voegen
            if (!$fup->requester) {
                $arts = \DB::table($mijnsalt_db . '.app_arts')
                    ->join($mijnsalt_db . '.app_contact', $mijnsalt_db . '.app_contact.artsId', $mijnsalt_db . '.app_arts.artsId')
                    ->where($mijnsalt_db . '.app_contact.contactId', $patient->contactId)->first();
                $fup->requester = $arts->agbcode;
            }
            $fup->last_test = count($last_test) ? $last_test : null;
            $fup->detail->data = $details;
            $fup->detail->save();
            $fup->save();
        }
        //draai na afloop
        /*
        update fui_patients set next_invitation_at=DATE_ADD(last_invitation_at, INTERVAL 42 DAY)
        WHERE last_invitation_at IS NOT NULL and last_reminder_invitation_at is NULL

        */
    }

    private $fundusFollowUp = [
        565 => 1095, //3 jaar regulier
        386 => 730,  //2 jaar regulier
        322 => 365,  //1 jaar regulier
        619 => 30000, //geen fundus controle meer noodzakelijk
        566 => null, //binnen zes maanden oogarts
        323 => null, //binnen 3 mnd oogarts
        324 => null, //binnen 1 mnd oogarts
        325 => null, //binnen 1 wk oogarts
        326 => null, //binen 1 dag oogarts
        '' => 60, //fotos mislukt of iets dergelijks
    ];

    /*
     * DRP
     * 559
R0: geen zichtbare retinopathie
560
R1: milde achtergrondretinopathie
561
R2: pre-proliferatieve retinopathie
562
R3: proliferatieve retinopathie
89
niet te bepalen
     * */
    private function storeOnderzoekDetails(array &$details, $onderzoek, $agbcode)
    {
        $details['appointments'][$onderzoek->datumonderzoek] = ['note' => 'done', 'at' => $onderzoek->datumonderzoek];
        $details['tests'][$onderzoek->datumonderzoek] = [
            "note" => (!$this->fundusFollowUp[$onderzoek->followup]) ? "oogarts" : "followup",
            'drp_l' => $onderzoek->diabetische_retinopatie_l,
            'drp_r' => $onderzoek->diabetische_retinopatie_r,
            'followup' => $onderzoek->followup,
            'at' => $onderzoek->datumonderzoek,
            'requester' => ($agbcode) ?? null,
            'provider' => $onderzoek->zorggroep,
        ];
    }
}