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

class ClearWaitUntilJob implements \Illuminate\Contracts\Queue\ShouldQueue
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
        //followUp patienten waarbij wait_until verstreken is weer activteren
        foreach (FollowUpPatient::whereNotNull('wait_until')->where('wait_until', '<=', now())->cursor() as $patient) {
            $patient->wait_until = null;
            if (!$patient->stop) {
                if (!$patient->next_test_at) {
                    $patient->next_test_at = now();
                }
                if (!$patient->next_invitation_at) {
                    $patient->next_invitation_at = now();
                }
            }
        }
    }
}