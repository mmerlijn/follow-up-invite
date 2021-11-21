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

class FollowUpInviteRunDailyMijnSalt implements \Illuminate\Contracts\Queue\ShouldQueue
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

        $mijnsalt_db = "db_salt12";

        ///////////////////////////////////////
        //Zoek alle appointments in de toekomst, die VOOR de last_appointment_at zijn
        // - update doen van de fuipatient tabel
        foreach (\DB::table($mijnsalt_db . '.agd_afspraak')
                     ->join($mijnsalt_db . '.agd_planning', $mijnsalt_db . '.agd_afspraak.planningId', $mijnsalt_db . '.agd_planning.planningId')
                     ->join(config('fuinvite.tables.fuipatients'), $mijnsalt_db . '.agd_afspraak.contactId', config('fuinvite.tables.fuipatients') . '.mijnsalt_id')
                     ->where($mijnsalt_db . '.agd_afspraak.activiteitId', config('fuinvite.tests.fundus.activity_id'))
                     ->where($mijnsalt_db . '.agd_planning.datum', '>=', now()->subDays(5)->format('Y-m-d'))
                     ->where(function ($q) use ($mijnsalt_db) {
                         $q = $q->whereRaw(config('fuinvite.tables.fuipatients') . '.last_appointment_at < ' . $mijnsalt_db . '.agd_planning.datum');
                         $q->orWhereRaw(config('fuinvite.tables.fuipatients') . '.last_appointment_at IS NULL');
                     })
                     ->where(config('fuinvite.tables.fuipatients') . '.type', 'fundus')
                     ->select('agd_afspraak.*', 'agd_planning.datum', 'id')
                     ->orderBy('datum')
                     ->cursor() as $afspraak) {
            //zoeken van bijhorende fuipatient
            $patient = FollowUpPatient::where('mijnsalt_id', $afspraak->contactId)->first();
            //$patient = FollowUpPatient::find($afspraak->id);
            $patient->last_appointment_at = $afspraak->datum;

            $reason = [];
            $details = $patient->detail->data;
            $details['appointments'][$afspraak->datum] = ['note' => 'open', 'at' => $afspraak->datum];
            if ($afspraak->verzuim) {
                $details['appointments'][$afspraak->datum] = ['note' => 'no show', 'at' => $afspraak->datum];
                $reason[] = 'no_show';
                $patient->stop = $afspraak->datum;
            }
            if ($afspraak->mislukt) {
                $details['appointments'][$afspraak->datum] = ['note' => 'failed', 'at' => $afspraak->datum];
            }
            $patient->detail->data = $details;
            $patient->detail->save();
            $patient->reason = array_unique($reason);
            $patient->save();
        }
        ////////////////////////////////////////////////////////////
        //zoeken van alle tests, waar al een followup patient bij is
        // - update van follup patient tabel
        // - invoeren van onderzoek samenvatting (beoordeelde onderzoek, die nog niet geregistreerd zijn bij een patient
        ////////////////////////////////////////////////////////////
        $max_test_date = Carbon::create(FollowUpPatient::max('last_test_at'))->subDays(5)->format('Y-m-d');
        if (!$max_test_date) {
            $max_test_date = '2000-01-01';
        }
        foreach (\DB::table($mijnsalt_db . '.fun_onderzoek')
                     ->join(config('fuinvite.tables.fuipatients'), $mijnsalt_db . '.fun_onderzoek.contactId', config('fuinvite.tables.fuipatients') . '.mijnsalt_id')
                     ->select('fun_onderzoek.*', 'id')
                     ->where($mijnsalt_db . '.fun_onderzoek.datumonderzoek', '>=', $max_test_date)
                     ->whereNotNull($mijnsalt_db . '.fun_onderzoek.beoordeling')
                     ->where(function ($q) use ($mijnsalt_db) {
                         $q = $q->whereRaw($mijnsalt_db . '.fun_onderzoek.datumonderzoek > ' . config('fuinvite.tables.fuipatients') . '.last_test_at');
                         $q->orWhereRaw(config('fuinvite.tables.fuipatients') . '.last_test_at IS NULL');
                     })
                     ->where(config('fuinvite.tables.fuipatients') . '.type', 'fundus')
                     ->cursor() as $onderzoek) {
            $requester = \DB::table($mijnsalt_db . '.app_arts')
                ->where('artsId', $onderzoek->aanvrager_onderzoek)
                ->first();

            //zoeken naar bijhorende followup patient
            $patient = FollowUpPatient::where('mijnsalt_id', $onderzoek->contactId)->first();
            //$patient = FollowUpPatient::find($afspraak->id);
            $patient->last_test_at = $onderzoek->datumonderzoek;
            $patient->days_between = $this->fundusFollowUp[$onderzoek->followup];
            $patient->last_visit_location = $onderzoek->locatieId;
            $patient->provider = $onderzoek->zorggroep;
            $patient->requester = $requester->agbcode ?? null;
            $patient->next_invitation_at = null;
            $patient->next_test_at = null;
            $patient->stop = null;
            $patient->wait_until = null;
            $patient->last_invitation_at = null;
            $patient->last_reminder_invitation_at = null;

            if (!$this->fundusFollowUp[$onderzoek->followup]) {
                $patient->stop = $onderzoek->datumonderzoek;
                $reason = $patient->reason;
                $reason[] = 'OOGARTS';
                $patient->reason = array_unique($reason);

            } else {
                $patient->next_invitation_at = Carbon::create($onderzoek->datumonderzoek)
                    ->addDays($patient->days_between - config('fuinvite.tests.fundus.invite_days_before')); //->format('Y-m-d');
                $patient->next_test_at = Carbon::create($onderzoek->datumonderzoek)
                    ->addDays($patient->days_between);
            }
            $patient->save();
            $this->storeDetails($patient, $onderzoek, $requester->agbcode);

        }
        ///////////////////////////////////////////////////////////////////////
        //zoeken alle tests waarbij de patient nog niet in fundus_patient staat
        // - invoeren van followup patient
        // - invoeren van followup samenvatting
        ///////////////////////////////////////////////////////////////////////
        foreach (\DB::table($mijnsalt_db . '.fun_onderzoek')
                     ->whereNotExists(function ($q) use ($mijnsalt_db) {
                         $q->select(\DB::raw(1))->from(config('fuinvite.tables.fuipatients'))
                             ->whereRaw('mijnsalt_id  = ' . $mijnsalt_db . '.fun_onderzoek.contactId')
                             ->where(config('fuinvite.tables.fuipatients') . '.type', 'fundus');
                     })
                     ->whereNotNull($mijnsalt_db . '.fun_onderzoek.beoordeling')
                     ->select('fun_onderzoek.*')
                     ->where($mijnsalt_db . '.fun_onderzoek.datumonderzoek', '>=', $max_test_date)
                     ->cursor() as $onderzoek) {
            //aanvrager ophalen
            $requester = \DB::table($mijnsalt_db . '.app_arts')
                ->where('artsId', $onderzoek->aanvrager_onderzoek)
                ->first();
            //fundus patient aanmaken
            $patient = FollowUpPatient::create([
                'mijnsalt_id' => $onderzoek->contactId,
                'type' => 'fundus',
                'last_test_at' => $onderzoek->datumonderzoek,
                'days_between' => $this->fundusFollowUp[$onderzoek->followup],
                'last_visit_location' => $onderzoek->locatieId,
                'provider' => $onderzoek->zorggroep,
                'requester' => $requester->agbcode ?? null
            ]);
            $patient->next_invitation_at = null;
            $patient->next_test_at = null;

            if (!$this->fundusFollowUp[$onderzoek->followup]) {
                $patient->stop = $onderzoek->datumonderzoek;
                $reason = $patient->reason;
                $reason[] = 'OOGARTS';
                $patient->reason = array_unique($reason);
            } else {
                $patient->next_invitation_at = Carbon::create($onderzoek->datumonderzoek)
                    ->addDays($patient->days_between - config('fuinvite.tests.fundus.invite_days_before')); //->format('Y-m-d');
                $patient->next_test_at = Carbon::create($onderzoek->datumonderzoek)
                    ->addDays($patient->days_between);

            }
            $patient->save();
            $this->storeDetails($patient, $onderzoek, $requester->agbcode);

        }
        NotActiveMijnsaltContactJob::dispatch();
        NoResponseInvitationJob::dispatch();
        ClearWaitUntilJob::dispatch();

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
    private function storeDetails(FollowUpPatient $patient, $onderzoek, $agbcode)
    {
        $details = $patient->detail->data;
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
        $patient->detail->data = $details;
        $patient->detail->save();
    }
}