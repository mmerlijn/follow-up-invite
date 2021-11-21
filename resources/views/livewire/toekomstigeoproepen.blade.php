<div>
    <div>
        <span class="text-xl font-bold m-4">Verwachting aantal fundus oproepen</span>
    </div>
    <div class="mx-4">
        <select wire:model="type" class="form-select text-xs rounded-lg">
            <option value="fundus">Fundus</option>
        </select>
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
    </div>
    <div class="mx-4">
        <table class="table-auto w-full bg-white">
            <thead>
            <tr class="text-left">
                <th>Wanneer</th>
                <th>Locatie</th>
                <th>Aantal</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $k=>$item)
                <tr class="border-b @if($k%2) bg-blue-100 @endif">
                    <td>{{$item->new_date}}
                    </td>
                    <td>{{$location->naam}}</td>
                    <td>{{$item->data}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>