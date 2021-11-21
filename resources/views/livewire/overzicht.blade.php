<div>

    <div class="mx-4">
        {{--
        <select wire:model="type" class="form-select text-xs rounded-lg">
            <option value="fundus">Fundus</option>
        </select>
        --}}
        <select wire:model="provider" class="form-select text-xs rounded-lg">
            @foreach(config('fuinvite.tests.'.$type.".providers") as $k=>$p)
                <option value="{{$k}}">{{$p}}</option>
            @endforeach
        </select>
        <select wire:model="location_id" class="form-select text-xs rounded-lg">
            @foreach($locations as $naam=>$id)
                <option value="{{$id}}">{{$naam}}</option>
            @endforeach
        </select>
        <select wire:model="requester" class="form-select text-xs rounded-lg">
            @foreach($requesters as $naam=>$agbcode)
                <option value="{{$agbcode}}">{{$naam}}</option>
            @endforeach
        </select>
        <label for="praktijk">
            <input type="checkbox" id="praktijk" wire:model="praktijk" class="form-checkbox"> + praktijk</label>
        <br>
        <label for="no_show">
            <input type="checkbox" id="no_show" wire:model="no_show" class="form-checkbox"> No Show</label>
        <label for="oogarts">
            <input type="checkbox" id="oogarts" wire:model="oogarts" class="form-checkbox"> Oogarts</label>
        <label for="no_diabetes">
            <input type="checkbox" id="no_diabetes" wire:model="no_diabetes" class="form-checkbox"> Geen
            diabetes</label>
        <label for="no_response">
            <input type="checkbox" id="no_response" wire:model="no_response" class="form-checkbox"> Geen reactie</label>
        <label for="stop">
            <input type="checkbox" id="wil_niet" wire:model="wil_niet" class="form-checkbox"> Wil niet</label>
        <label for="stop">
            <input type="checkbox" id="stop" wire:model="stop" class="form-checkbox"> Stop</label>
        <label for="actief">
            <input type="checkbox" id="actief" wire:model="actief" class="form-checkbox"> Actief</label>
        <label for="drp">
            <input type="checkbox" id="drp" wire:model="drp" class="form-checkbox"> DRP</label>
        <a href="#modal-select">
            <button class="bg-blue-700 text-white p-1 rounded">Wijzig kolom selectie</button>
        </a>
        <a href="#modal-export">
            <button class="bg-blue-700 text-white p-1 rounded">Export tools</button>
        </a>
        <br>
        <div class="border p-2 rounded container">
            Range filter:
            <select wire:model="range_item" class="form-select text-xs rounded-lg">
                <option value=""></option>
                <option value="last_test_at">Laatste onderzoek</option>
                <option value="last_appointment_at">Laatste afspraak</option>
                <option value="last_invitation_at">Laatste eerste uitnodiging</option>
                <option value="last_reminder_invitation_at">Laatste herhaal uitnodiging</option>
                <option value="next_invitation_at">Volgende uitnodiging</option>
                <option value="next_test_at">Volgend onderzoek</option>
                <option value="stop">Gestopt</option>
                <option value="wait_until">Wachten tot</option>
            </select>
            Van: <input type="date" wire:model="from_date" class="form-input text-xs rounded-lg">
            Tot: <input type="date" wire:model="to_date" class="form-input text-xs rounded-lg">
        </div>
    </div>

    <div class="mx-4">
        <table class="table-auto w-full bg-white">
            <thead>
            <tr class="text-left">
                @if(in_array('id',$toon))
                    <th>ID</th>
                @endif
                @if(in_array('naam',$toon))
                    <th>Naam</th>
                @endif
                @if(in_array('bsn',$toon))
                    <th>BSN</th>
                @endif
                @if(in_array('dob',$toon))
                    <th>dob</th>
                @endif
                @if(in_array('locatie',$toon))
                    <th>Locatie</th>
                @endif
                @if(in_array('aanvrager',$toon))
                    <th>Aanvrager</th>
                @endif
                @if(in_array('laatste_onderzoek',$toon))
                    <th>Laatste onderzoek</th>
                @endif
                @if(in_array('laatste_afspraak',$toon))
                    <th>Laatste afspraak</th>
                @endif
                @if(in_array('uitnodiging1',$toon))
                    <th>Uitnodiging 1</th>
                @endif
                @if(in_array('uitnodiging2',$toon))
                    <th>Uitnodiging 2</th>
                @endif
                @if(in_array('volgende_onderzoek',$toon))
                    <th>+/-Volgend onderzoek</th>
                @endif
                @if(in_array('wachten_tot',$toon))
                    <th>Wachten tot</th>
                @endif
                @if(in_array('stop',$toon))
                    <th>Stop</th>
                @endif
                @if(in_array('reden',$toon))
                    <th>Reden</th>
                @endif
                @if(in_array('test_result',$toon))
                    <th>Test resultaat</th>
                @endif
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $k=>$item)
                <tr class="border-b cursor-pointer hover:bg-yellow-200 @if($k%2) bg-blue-100 @endif" wire:click="goto({{$item->id}})">
                    @if(in_array('id',$toon))
                        <td>{{$item->mijnsaltpatient->contactId}}</td>
                    @endif
                    @if(in_array('naam',$toon))
                        <td>{{$item->mijnsaltpatient->naam}}</td>
                    @endif
                    @if(in_array('bsn',$toon))
                        <td>{{$item->mijnsaltpatient->bsn}}</td>
                    @endif
                    @if(in_array('dob',$toon))
                        <td>{{$item->mijnsaltpatient->gbdatum->format('d-m-Y')}}</td>
                    @endif
                    @if(in_array('locatie',$toon))
                        <td>{{$item->location?$item->location->afkorting:''}}</td>
                    @endif
                    @if(in_array('aanvrager',$toon))
                        <td>{{$item->aanvrager?$item->aanvrager->achternaam:$item->requester}}</td>
                    @endif
                    @if(in_array('laatste_onderzoek',$toon))
                        <td>{{$item->last_test_at?$item->last_test_at->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('laatste_afspraak',$toon))
                        <td>{{$item->last_appointment_at?$item->last_appointment_at->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('uitnodiging1',$toon))
                        <td>{{$item->last_invitation_at?$item->last_invitation_at->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('uitnodiging2',$toon))
                        <td>{{$item->last_reminder_invitation_at?$item->last_reminder_invitation_at->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('volgende_onderzoek',$toon))
                        <td>{{$item->next_test_at?$item->next_test_at->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('wachten_tot',$toon))
                        <td>{{$item->wait_until?$item->wait_until->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('stop',$toon))
                        <td>{{$item->stop?$item->stop->format('d-m-Y'):'-'}}</td>
                    @endif
                    @if(in_array('reden',$toon))
                        <td>{{implode(",",$item->reason??[])}}</td>
                    @endif
                    @if(in_array('test_result',$toon))
                        <td>{{implode(",",$item->latest_test??[])}}</td>
                    @endif
                    <td>
                        <a href="#modal-show">
                            <button class="bg-blue-700 text-white p-1 rounded">Toon</button>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
        <button class="bg-blue-700 text-white p-1 rounded" wire:click="export2excel">Export2Excel</button>
    </div>
    <x-bladeComponents::modal :name="'show'">
        @if($fup_id)

            <livewire:fui-patient :id="$fup_id" key="{{ now() }}"/>

        @endif
    </x-bladeComponents::modal>
    <x-bladeComponents::modal :name="'select'">
        <div>
            <h2 class="text-xl">Kolomselectie</h2>
            <p>Hieronder kan je aangeweven welke kolommen getoond moeten worden</p>
            @foreach($toon_mogelijk as $item)
                <label for="{{ $loop->index }}"><input type="checkbox" wire:model="toon" value="{{$item}}" id="{{ $loop->index }}" class="form-checkbox"> {{$item}}
                </label><br>
            @endforeach
        </div>
    </x-bladeComponents::modal>

    <x-bladeComponents::modal :name="'export'">
        <div>
            <h2 class="text-xl">Vaak gemaakte exports</h2>
            <p>Dit zijn voorgedefinieerde exports. Op verzoek kan dit worden uitgebreid.</p>
            <div>SAG maandelijksexport (selecteer een dag van de betreffende maand)<br>
                <input type="date" wire:model.lazy="exportDateSAG">
                <button class="bg-blue-700 text-white p-1 rounded" wire:click="exportSAG">Export2Excel</button>
            </div>
        </div>
    </x-bladeComponents::modal>
</div>


