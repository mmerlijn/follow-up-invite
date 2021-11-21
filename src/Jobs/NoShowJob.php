<?php

namespace mmerlijn\followUpInvite\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\FollowUpTest;
use mmerlijn\followUpInvite\Models\MijnSaltFundusOnderzoek;
use mmerlijn\followUpInvite\Models\MijnSaltPatient;

class NoShowJob implements \Illuminate\Contracts\Queue\ShouldQueue
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
        //Weet nog niet of dit gedaan moet worden
        /*
        //followUp patienten waarbij na 2x uitnodiging verstuurd is en nog geen afspraak na .. dagen op NO_REPONSE zetten
        foreach (FollowUpPatient::where('last_appointment_at', '<', now()->subDays(5)->format('Y-m-d'))
                     ->whereNotNull('last_appointment_at')
                     ->whereRaw('`last_appointment_at` <> `last_test_at`')
                     ->whereNull('stop')
                     ->get() as $patient) {
            //we kijken of er werkelijk geen test is op de afspraakdatum, GEEN TEST=> NO SHOW
            if (!MijnSaltFundusOnderzoek::where('contactId', $patient->mijnsalt_id)->where('datumonderzoek', $patient->last_appointment_at)->count()) {
                $patient->stop = now();
                $reason = $patient->reason;
                $reason[] = 'NO_SHOW';
                $patient->reason = array_unique($reason);
                $details = $patient->detail->data;
                dd($details);
                $details['appointments'][$patient->last_appointment_at] = ['note' => 'no show', 'at' => $patient->last_appointment_at];
                $patient->detail->data = $details;

                $patient->detail->save();
                $patient->save();
            }

        }
        */
    }
}