<?php

namespace mmerlijn\followUpInvite\Http\Livewire;

use Livewire\Component;
use mmerlijn\followUpInvite\Classes\ConvertEmptyStringsToNull;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use PhpParser\Builder\Class_;

class Patient extends Component
{

    public $fup_id;
    public FollowUpPatient $patient;
    public $notFound = false;

    public $call_note;
    public $dontWantReason;

    protected $rules = [
        'patient.wait_until' => 'nullable|date',
        'patient.stop' => 'nullable|date',
    ];

    public function updatedPatient()
    {
        $this->validate();
        $this->patient->save();
    }

    public function mount($id)
    {
        $this->fup_id = $id;
        try {
            $this->patient = FollowUpPatient::findOrFail($id);
        } catch (\Exception $e) {
            $this->notFound = true;
        }
    }

    public function render()
    {
        return view('fuinvite::livewire.patient',
            []);
    }

    public function storeCallNote()
    {
        if ($this->call_note) {
            $details = $this->patient->detail->data;
            $details['calls'][now()->format('Y-m-d')] = [
                'note' => $this->call_note,
                'at' => now()->format('Y-m-d'),
                'by' => auth()->user()->name,
                'user' => auth()->user()->id,
            ];
            $this->patient->detail->data = $details;
            $this->patient->detail->save();
            $this->call_note = "";
        }
    }

    public function deleteCallNote($date)
    {
        $details = $this->patient->detail->data;
        unset($details['calls'][$date]);
        $this->patient->detail->data = $details;
        $this->patient->detail->save();
    }

    public function reInvite()
    {
        $this->patient->wait_until = null;
        $this->patient->stop = null;
        $this->patient->last_invitation_at = null;
        $this->patient->last_reminder_invitation_at = null;
        $this->patient->reason = null;
        if (!$this->patient->next_invitation_at) {
            $this->patient->next_invitation_at = now();
            $this->patient->next_test_at = now();

        }
        //$this->patient->next_invitation_at = moet hier wel iets mee gebeuren???
        $this->patient->save();
    }

    public function dontWant()
    {
        $this->patient->stop = now();
        $reason = $this->patient->reason;
        $reason[] = 'WIL_NIET';
        $this->patient->reason = array_unique($reason);
        $this->patient->save();
        $details = $this->patient->detail->data;
        $details['actions'][now()->format('Y-m-d')] = [
            'note' => 'wil niet',
            'content' => $this->dontWantReason,
            'by' => auth()->user()->name,
            'user' => auth()->user()->id,
        ];
        $this->patient->detail->data = $details;
        $this->patient->detail->save();
        $this->dontWantReason = "";
    }

    public function noDiabetes()
    {
        $this->patient->stop = now();
        $reason = $this->patient->reason;
        $reason[] = 'NO_DIABETES';
        $this->patient->reason = array_unique($reason);
        $this->patient->save();

        $details = $this->patient->detail->data;
        $details['actions'][now()->format('Y-m-d')] = [
            'note' => 'geen diabetes',
            'content' => $this->dontWantReason,
            'by' => auth()->user()->name,
            'user' => auth()->user()->id,
        ];
        $this->patient->detail->data = $details;
        $this->patient->detail->save();
    }

    public function deletePatient()
    {
        $details = $this->patient->detail->data;
        $details['actions'][now()->format('Y-m-d')] = [
            'note' => 'Verwijder patient',
            'content' => 'Verwijderd',
            'by' => auth()->user()->name,
            'user' => auth()->user()->id,
        ];
        $this->patient->detail->data = $details;
        $this->patient->detail->save();
        $this->patient->delete();
    }
}