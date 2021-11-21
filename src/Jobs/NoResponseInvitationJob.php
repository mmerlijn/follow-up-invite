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

class NoResponseInvitationJob implements \Illuminate\Contracts\Queue\ShouldQueue
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

        $dagen = 56;
        //followUp patienten waarbij na 2x uitnodiging verstuurd is en nog geen afspraak na .. dagen op NO_REPONSE zetten
        foreach (FollowUpPatient::where('last_reminder_invitation_at', '<', now()->subDays($dagen)->format('Y-m-d'))
                     ->whereNotNull('last_reminder_invitation_at')
                     ->whereNull('stop')
                     ->get() as $patient) {
            $patient->stop = Carbon::create($patient->last_reminder_invitation_at)->addDays($dagen)->format('Y-m-d');
            $reason = $patient->reason;
            $reason[] = 'NO_RESPONSE';
            $patient->reason = array_unique($reason);
            $patient->save();
        }
    }
}