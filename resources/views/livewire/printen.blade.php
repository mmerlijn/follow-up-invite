<div class="m-4">
    <div>
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
        <label for="versnellen">Versnellen</label>
        <input type="number" id="versnellen" value="0" wire:model="versnellen" class="form-input w-24 text-xs rounded-lg">

        <label for="pageSize">Aantal resultaten op pagina</label>
        <input type="number" wire:model="pageSize" class="form-input w-24 text-xs rounded-lg" id="pageSize">
        <label for="vandaag"><input id="vandaag" value="1" type="checkbox" wire:model="vandaag" class="form-checkbox">
            Vandaag geprint</label>
    </div>
    <div>
        <div class="flex">
            <button wire:click="print" @if(!count($patients)) disabled @endif
            class="m-1 bg-red-600 text-white py-1 px-2 rounded hover:text-gray-600 hover:bg-red-400 shadow-lg flex items-center">
                <span class="ml-2">Print geselecteerde ({{count($patients)}})</span>
            </button>
            <button wire:click="wis"
                    class="m-1 ml-4 bg-blue-600 text-white py-1 px-2 rounded hover:text-gray-600 hover:bg-blue-400 shadow-lg flex items-center">
                <span class="ml-2">Wis</span>
            </button>
            <button wire:click="selectThisPage"
                    class="m-1 ml-4 bg-blue-600 text-white py-1 px-2 rounded hover:text-gray-600 hover:bg-blue-400 shadow-lg flex items-center">
                <span class="ml-2">Selecteer pagina</span>
            </button>
            <button wire:click="selectAll"
                    class="m-1 ml-4 bg-blue-600 text-white py-1 px-2 rounded hover:text-gray-600 hover:bg-blue-400 shadow-lg flex items-center">
                <span class="ml-2">Selecteer alles</span>
            </button>
        </div>
        <table class="table-auto w-full bg-white">
            <thead>
            <tr class="text-left">
                <th>Print</th>
                <th>Naam</th>
                <th>Locatie</th>
                <th>Laatste aanvrager</th>
                <th>Uitnodigen op</th>
                <th>+/-vervolg onderzoek</th>
                <th>Uitnodiging 1</th>
                <th>herhaling</th>
                <th>Laatste afspraak</th>
                <th>Patiënt</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $k=>$item)
                <tr class="border-b @if($k%2) bg-blue-100 @endif">
                    <td>
                        <input type="checkbox" value="{{$item->id}}" wire:model="patients" id="{{ $loop->index }}" class="form-checkbox">
                    </td>
                    <td>{{$item->mijnsaltpatient->eigennaam}}, {{$item->mijnsaltpatient->voorletters}}</td>
                    <td>{{$item->location?$item->location->afkorting:"-"}}</td>
                    <td>{{$item->aanvrager?$item->aanvrager->achternaam:$item->requester}}</td>
                    <td>{{$item->next_invitation_at->format('d-m-Y')}}</td>
                    <td>{{$item->next_test_at?$item->next_test_at->format('d-m-Y'):''}}</td>
                    <td>{{$item->last_invitation_at?$item->last_invitation_at->format('d-m-Y'):'-'}}</td>
                    <td>{{$item->last_reminder_invitation_at?$item->last_reminder_invitation_at->format('d-m-Y'):'-'}}</td>
                    <td>{{$item->last_appointment_at?$item->last_appointment_at->format('d-m-Y'):'-'}}</td>
                    <td>
                        <a href="#modal-show">
                            <button class="bg-blue-700 text-white p-1 rounded" wire:click="goto({{$item->id}})">Toon
                            </button>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
    <x-bladeComponents::modal :name="'show'">
        @if($fup_id)

            <livewire:fui-patient :id="$fup_id" key="{{ now() }}"/>

        @endif
    </x-bladeComponents::modal>
</div>