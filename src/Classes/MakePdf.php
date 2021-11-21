<?php

namespace mmerlijn\followUpInvite\Classes;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\View;
use mmerlijn\followUpInvite\Models\FollowUpPatient;

class MakePdf
{
    public function handel(FollowUpPatient $patient): string
    {
        //TODO kijken of het oproep of herhaling betreft (nu even alleen nog oproep

        //First invitation
        if (!$patient->last_invitation_at or $patient->last_invitation_at->format('Ymd') == now()->format('Ymd')) {
            if (View::exists(config('fuinvite.tests.' . $patient->type . '.invite_letter'))) {
                $html = view(config('fuinvite.tests.' . $patient->type . '.invite_letter'),
                    ['patient' => $patient])->render();

                //set print first invitation
                $patient->last_invitation_at = now();
                //set next reminder invitation
                $patient->next_invitation_at = now()->addDays(config('fuinvite.tests.fundus.invite_reminder_after_days'));
                $patient->save();
                $this->storePrintAction($patient, "first"); //first invitation
                return $html;
            }

        } else {//Second invitation
            if (View::exists(config('fuinvite.tests.' . $patient->type . '.invite_letter_reminder'))) {
                $html = view(config('fuinvite.tests.' . $patient->type . '.invite_letter_reminder'),
                    ['patient' => $patient])->render();
                $patient->last_reminder_invitation_at = now();
                $patient->save();
                $this->storePrintAction($patient, "second"); //second invitation
                return $html;
            }
        }

        return "";
    }

    private function storePrintAction(FollowUpPatient $patient, string $type)
    {
        $details = $patient->detail->data;
        $details['letters'][now()->format('Y-m-d')] = ['note' => $type, 'at' => now()->format('Y-m-d')];
    }
}