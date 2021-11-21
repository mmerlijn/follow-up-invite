<?php

namespace mmerlijn\followUpInvite\Http\Livewire;

use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use mmerlijn\followUpInvite\Classes\MakePdf;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\MijnSaltLocation;
use mmerlijn\followUpInvite\Models\MijnSaltRequester;

class Printen extends Component
{
    use WithPagination;

    public $type;
    public $requesters;
    public $requester_groups;
    public $locations;

    public $patients = [];
    public $location_id = "";
    public $requester;
    public $versnellen = 0; //dagen
    public $vandaag;
    public $pageSize = 20;
    public $praktijk;

    //voor patient overzicht
    public $fup_id; // gebruiken we voor Patient modal

    //Dit gebruik ik om na het printen/downloaden de resultaten te updaten
    protected $listeners = ['render' => 'render'];

    public function mount($type = "fundus")
    {
        $this->type = $type;
        $this->requesters = ['' => ''];
        foreach (MijnSaltRequester::orderBy('achternaam')->whereNotNull('agbcode')->whereNotNull('actief')->get() as $requester) {
            $this->requesters[$requester->achternaam . ", " . $requester->voorletters . ' ' . $requester->tussenvoegsel . ' (' . $requester->plaats . ')'] = $requester->agbcode;
        }
        $this->locations = ['' => ''];
        foreach (MijnSaltLocation::orderBy('plaats')->whereNotNull('actief')->get() as $location) {
            $this->locations[$location->plaats . " - " . $location->naam] = $location->locatieId;
        }

        $this->requester_groups = [''];
        foreach (MijnSaltRequester::orderBy('achternaam')->whereNotNull('actief')->whereNotNull('praktijkhouder')->get() as $requester) {
            $this->requester_groups[$requester->achternaam . ", " . $requester->voorletters . ' ' . $requester->tussenvoegsel . ' (' . $requester->plaats . ')'] = $requester->agbcode;
        }
    }

    public function selectThisPage()
    {
        foreach ($this->zoek()
                     ->with(['mijnsaltpatient', 'aanvrager', 'location'])
                     ->paginate($this->pageSize) as $item) {
            $this->patients[] = $item->id;
        }
        $this->patients = array_unique($this->patients);
    }

    public function selectAll()
    {
        foreach ($this->zoek()->get() as $item) {
            $this->patients[] = $item->id;
        }
        $this->patients = array_unique($this->patients);
    }

    public function zoek(): Builder
    {
        $followUpPatient = FollowUpPatient::whereType($this->type)
            ->whereNull('wait_until')
            ->whereNull('stop');


        if ($this->requester) {
            if ($this->praktijk) {
                $requesters = [];
                foreach (MijnSaltRequester::whereIn('artsId', explode(",", (MijnSaltRequester::whereAgbcode($this->requester)->first())->artsIds))->get() as $req) {
                    if ($req->agbcode) {
                        $requesters[] = $req->agbcode;
                    }
                }
                $followUpPatient = $followUpPatient->whereIn('requester', $requesters);
            } else {
                $followUpPatient = $followUpPatient->whereRequester($this->requester);
            }
        }

        if (!$this->versnellen) {
            $this->versnellen = 0;
        }
        if ($this->vandaag) {//hetgeen vandaag geprint is tonen
            $followUpPatient = $followUpPatient->where('last_invitation_at', '=', now()->format('Y-m-d'))
                ->orWhere('last_reminder_invitation_at', '=', now()->format('Y-m-d'));

        } else {
            $followUpPatient = $followUpPatient->where('next_invitation_at', '<=', now()->addDays($this->versnellen))
                ->whereNotNull('next_invitation_at')
                ->where(fn($q) => $q->whereNull('last_invitation_at')->orWhere('last_reminder_invitation_at'))
                ->where('last_appointment_at', '<', now()->subDays(5));

        }
        if ($this->location_id) {
            $followUpPatient = $followUpPatient->whereLastVisitLocation($this->location_id);
        }
        return $followUpPatient;

    }

    public function render()
    {
        return view('fuinvite::livewire.printen',
            ['items' => $this->zoek()
                ->with(['mijnsaltpatient', 'aanvrager', 'location'])
                ->paginate($this->pageSize)
            ]);
    }

    public function print()
    {
        $this->patients = array_unique($this->patients);
        $patients = FollowUpPatient::whereIn('id', $this->patients)->with('mijnsaltpatient')->get();

        $html = "";
        foreach ($patients as $patient) {
            if (strlen($html)) {
                $html .= "<div class=\"page_break\"></div>";
            }
            $html .= (new MakePdf())->handel($patient);
        }
        $html = "<style>
.page_break {
            page-break-before: always;
        }
        .adres {
        position:absolute;
        left:375px;
        top:143px;
        }
        .content {
        margin-top:220px;
        }
        </style>
        " . $html;

        $pdf = PDF::loadHTML($html)->output();
        $this->patients = []; //lijst met te printen leeggemaakt
        $this->emit('render');
        return response()->streamDownload(fn() => print($pdf), 'followUp.pdf');
    }

    public function wis()
    {
        $this->patients = [];
        $this->location_id = "";
        $this->aanvrager = "";
        $this->versnellen = 0;
        $this->vandaag = false;
    }

    public function goto(int $id)
    {
        $this->fup_id = $id;
    }
}