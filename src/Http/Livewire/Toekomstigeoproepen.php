<?php

namespace mmerlijn\followUpInvite\Http\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Models\MijnSaltLocation;
use mmerlijn\followUpInvite\Models\MijnSaltRequester;

class Toekomstigeoproepen extends Component
{
    public $locations;
    public $provider;
    public $location_id = "";


    public function updated()
    {

        $this->zoek();
    }

    public function mount($type = "fundus")
    {
        $this->type = $type;
        $this->locations = ['' => ''];
        foreach (MijnSaltLocation::orderBy('plaats')->whereNotNull('actief')->get() as $location) {
            $this->locations[$location->plaats . " - " . $location->naam] = $location->locatieId;
        }
    }

    public function render()
    {
        return view('fuinvite::livewire.toekomstigeoproepen',
            [
                'items' => $this->zoek()->get(),
                'location' => MijnSaltLocation::find($this->location_id),
            ]);
    }

    protected function zoek(): Builder
    {
        $this->error = "";
        $fup = FollowUpPatient::whereType($this->type)
            ->whereNull('stop');
        if ($this->provider) {
            $fup = $fup->whereProvider($this->provider);
        }

        $fup->whereLastVisitLocation($this->location_id)
            ->whereBetween('next_test_at', [now()->format('Y-m-d'), now()->addMonths(14)->format('Y-m-d')])
            ->select(\DB::raw('count(id) as `data`'),
                \DB::raw("DATE_FORMAT(`next_test_at`, '%Y-%m') as new_date"),
            )
            ->groupby('new_date')
            ->orderBy('new_date');
        return $fup;
    }
}
