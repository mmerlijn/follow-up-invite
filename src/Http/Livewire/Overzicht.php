<?php

namespace mmerlijn\followUpInvite\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use mmerlijn\followUpInvite\Classes\Excel\OverzichtExcelExport;
use mmerlijn\followUpInvite\Classes\Excel\VisitExport;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\MijnSaltLocation;
use mmerlijn\followUpInvite\Models\MijnSaltRequester;

class Overzicht extends Component
{
    use WithPagination;

    public $type;
    public $requesters;
    public $locations;

    public $no_show;
    public $oogarts;
    public $no_diabetes;
    public $no_response;
    public $wil_niet;
    public $stop;
    public $drp;
    public $actief;

    public $toon;
    public $location_id = "";
    public $requester;
    public $praktijk;
    public $provider;
    public $noResponse;
    public $fup_id; // gebruiken we voor Patient modal
    public $range_item;
    public $from_date;
    public $to_date;

    public $toon_mogelijk = ['id', 'naam', 'dob', 'bsn', 'locatie', 'aanvrager', 'uitnodiging1', 'uitnodiging2', 'laatste_onderzoek', 'laatste_afspraak', 'volgende_onderzoek', 'wachten_tot', 'stop', 'reden', 'test_result'];
    public $exportDateSAG;

    public function updated()
    {

        $this->zoek();
    }

    public function mount($type = "fundus")
    {
        $this->type = $type;
        $this->requesters = ['' => ''];
        foreach (MijnSaltRequester::orderBy('achternaam')->whereNotNull('actief')->whereNotNull('agbcode')->get() as $requester) {
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
        if (session()->has('fuioverzicht_toon')) {
            $this->toon = session()->get('fuioverzicht_toon');
        } else {
            $this->toon = ['id', 'naam', 'locatie', 'uitnodiging1', 'uitnodiging2', 'laatste_onderzoek', 'laatste_afspraak', 'volgende_onderzoek', 'stop', 'reden'];
        }
    }

    public function updatedToon()
    {
        session()->put('fuioverzicht_toon', $this->toon);
    }

    public function render()
    {
        return view('fuinvite::livewire.overzicht',
            [
                'items' => $this->zoek()
                    ->with(['mijnsaltpatient', 'location', 'aanvrager'])
                    ->paginate(20)
            ]);
    }

    protected function zoek(): Builder
    {
        $this->error = "";
        $fup = FollowUpPatient::whereType($this->type);
        if ($this->requester) {
            if ($this->praktijk) {
                $requesters = [];
                foreach (MijnSaltRequester::whereIn('artsId', explode(",", (MijnSaltRequester::whereAgbcode($this->requester)->first())->artsIds))->get() as $req) {
                    if ($req->agbcode) {
                        $requesters[] = $req->agbcode;
                    }
                }
                $fup = $fup->whereIn('requester', $requesters);
            } else {
                $fup = $fup->whereRequester($this->requester);
            }
        }
        if ($this->location_id) {
            $fup = $fup->whereLastVisitLocation($this->location_id);
        }
        if ($this->provider) {
            $fup = $fup->whereProvider($this->provider);
        }
        if ($this->no_show) {
            $fup = $fup->whereJsonContains('reason', 'NO_SHOW');
        }
        if ($this->oogarts) {
            $fup = $fup->whereJsonContains('reason', 'OOGARTS');
        }
        if ($this->no_diabetes) {
            $fup = $fup->whereJsonContains('reason', 'NO_DIABETES');
        }
        if ($this->no_response) {
            $fup = $fup->whereJsonContains('reason', 'NO_RESPONSE');
        }
        if ($this->wil_niet) {
            $fup = $fup->whereJsonContains('reason', 'WIL_NIET');
        }
        if ($this->drp) {
            $fup = $fup->whereJsonContains('latest_test', 'DRP');
        }
        if ($this->stop) {
            $fup = $fup->whereNotNull('stop');
        }
        if ($this->actief) {
            $fup = $fup->whereNull('stop')->whereNull('wait_until');
        }
        if ($this->range_item) {
            if ($this->from_date) {
                $fup = $fup->where($this->range_item, '>=', $this->from_date);
            }
            if ($this->to_date) {
                $fup = $fup->where($this->range_item, '>=', $this->to_date);
            }
        }
        return $fup;
    }

    public function goto(int $id)
    {
        $this->fup_id = $id;
    }

    public function export2excel()
    {
        return Excel::download(
            new OverzichtExcelExport(
                $this->zoek()->with(['mijnsaltpatient', 'location', 'aanvrager']), $this->toon
            ),
            'export.xlsx');
    }

    public function exportSAG()
    {
        $year = Carbon::create($this->exportDateSAG)->format('Y');
        $month = Carbon::create($this->exportDateSAG)->format('n');
        return (new VisitExport($year, $month, 7))->download('SAG ' . $year . '-' . $month . ".xlsx");
    }
}
