<div>
    @if($notFound)
        Patient kan niet worden gevonden {{$fup_id}}
    @else
        <div>
            <table>
                <tr>
                    <td>Id</td>
                    <td>{{$patient->mijnsaltpatient->contactId}}</td>
                </tr>
                <tr>
                    <td>Naam</td>
                    <td>{{$patient->mijnsaltpatient->naam}} ({{$patient->mijnsaltpatient->gbdatum->format('d-m-Y')}}
                        )
                    </td>
                </tr>
                @if($patient->provider)
                    <tr>
                        <td>Zorggroep</td>
                        <td>{{config('fuinvite.tests.'.$patient->type.'.providers')[$patient->provider]}}</td>
                    </tr>
                @endif
                @if($patient->last_visit_location)
                    <tr>
                        <td>Test locatie</td>
                        <td>{{$patient->location->naam}}</td>
                    </tr>
                @endif
                @if($patient->last_test_at)
                    <tr>
                        <td>Laatste onderzoek</td>
                        <td>{{$patient->last_test_at->format('d-m-Y')}}
                            ({{$patient->last_test_at->diffForHumans(now())}})
                        </td>
                    </tr>
                @endif
                @if($patient->last_test_at != $patient->last_appointment_at and $patient->last_appointment_at)
                    <tr>
                        <td>Laatste afspraak</td>
                        <td>{{$patient->last_appointment_at->format('d-m-Y')}}</td>
                    </tr>
                @endif
                @if($patient->next_invitation_at)
                    <tr>
                        <td>Uitnodigen vanaf</td>
                        <td>{{$patient->next_invitation_at->format('d-m-Y')}}</td>
                    </tr>
                @endif
                @if($patient->last_invitation_at)
                    <tr>
                        <td>1e uitnodiging</td>
                        <td>{{$patient->last_invitation_at->format('d-m-Y')}}</td>
                    </tr>
                @endif
                @if($patient->last_reminder_invitation_at)
                    <tr>
                        <td>2e uitnodiging</td>
                        <td>{{$patient->last_reminder_invitation_at->format('d-m-Y')}}

                        </td>
                    </tr>
                @endif
                @if($patient->next_test_at)
                    <tr>
                        <td>Opnieuw testen op</td>
                        <td>{{$patient->next_test_at->format('d-m-Y')}}
                            ({{$patient->next_test_at->diffForHumans(now())}})
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>Uitstellen tot</td>
                    <td><input type="date" wire:model="patient.wait_until" class="rounded text-sm"></td>
                </tr>
                @if($patient->stop)
                    <tr>
                        <td class="@if($patient->stop) text-red-600 @endif">Niet meer uitnodigen</td>
                        <td>{{$patient->stop->format('d-m-Y')}}
                            Reden:
                            {{implode(",",$patient->reason??[])}}
                        </td>
                    </tr>
                @endif

            </table>
            @error('patient.stop') <span class="error">{{ $message }}</span> @enderror
        </div>
        <div class="mt-4" x-data="{ open: 'tests'}">
            <div>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='tests'" x-bind:class="(open=='tests')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Onderzoek</span>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='appointments'" x-bind:class="(open=='appointments')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Afspraken</span>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='letters'" x-bind:class="(open=='letters')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Brieven</span>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='emails'" x-bind:class="(open=='emails')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Emails</span>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='calls'" x-bind:class="(open=='calls')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Telefooncontact</span>
                <span class="font-bold text-lg rounded p-2" x-on:click="open='actions'" x-bind:class="(open=='actions')?'border-t border-l border-r bg-white':'text-gray-400 cursor-pointer'">Acties</span>
            </div>
            <div class="bg-white w-full mt-1 rounded-lg">
                <div x-show="open =='tests'">
                    <table class="table-auto lg:w-2/3 w-full bg-white mx-4">
                        <tr class="text-left">
                            <th>Datum</th>
                            <th>Result/Type</th>
                            <th>Zorggroep</th>
                        </tr>
                        @foreach($patient->detail->data['tests']??[] as $k=>$test)
                            <tr class="border-b">
                                <td>{{$k}}</td>
                                <td>{{$test['note']}}</td>
                                <td>{{config('fuinvite.tests.'.$patient->type.'.providers')[$test['provider']??'']}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div x-show="open =='appointments'">
                    <table class="table-auto bg-white mx-4  lg:w-2/3 w-full ">
                        <tr class="text-left">
                            <th>Datum</th>
                            <th>Result</th>
                        </tr>
                        @foreach($patient->detail->data['appointments']??[] as $k=>$appointment)
                            <tr>
                                <td>{{$k}}</td>
                                <td>{{$appointment['note']}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div x-show="open =='letters'">
                    <table class="table-auto bg-white mx-4 w-2/3">
                        <tr class="text-left">
                            <th>Datum</th>
                            <th>Brief</th>
                        </tr>
                        @foreach($patient->detail->data['letters']??[] as $k=>$letter)
                            <tr>
                                <td>{{$k}}</td>
                                <td>{{$letter['note']}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div x-show="open =='emails'">
                    <table class="table-auto bg-white mx-4  lg:w-2/3 w-full ">
                        <tr class="text-left">
                            <th>Datum</th>
                            <th>Content</th>
                        </tr>
                        @foreach($patient->detail->data['emails']??[] as $k=>$email)
                            <tr>
                                <td>{{$k}}</td>
                                <td>{{$email['note']}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div x-show="open =='calls'">
                    <table class="table-auto w-full bg-white mx-4  lg:w-2/3 w-full ">
                        <tr class="text-left">
                            <th>Datum</th>
                            <th>Gespreksnotitie</th>
                            <th>Door</th>
                            <th></th>
                        </tr>
                        @foreach($patient->detail->data['calls']??[] as $k=>$call)
                            <tr>
                                <td>{{$k}}</td>
                                <td>{{$call['note']}}</td>
                                <td>{{$call['by']}}</td>
                                <td>
                                    @if(auth()->user()->id == ($call['user']??''))
                                        <button wire:click="deleteCallNote('{{$k}}')" class="bg-gray-700 text-white p-1 rounded">
                                            X
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    <div class="border-t flex flex-row justify-start items-center">
                        <div class="">
                            <span class="p-4 m-4 font-bold bg-yellow-200 rounded-lg">Telefoonnr: {{$patient->mijnsaltpatient->telefoon}} / {{$patient->mijnsaltpatient->mobiel}}</span>
                        </div>
                        <div>
                            <label for="call_note" class="font-bold mt-2">Gespreksnotitie</label><br>
                            <textarea wire:model.lazy="call_note" id="call_note"></textarea><br>
                            <button wire:click="storeCallNote" class="bg-blue-700 text-white p-1 rounded">Voeg toe
                            </button>
                        </div>
                    </div>
                </div>
                <div x-show="open =='actions'">
                    <div>
                        <table class="table-auto w-full bg-white mx-4  lg:w-2/3 w-full ">
                            <tr class="text-left">
                                <th>Datum</th>
                                <th>Type</th>
                                <th>Content</th>
                                <th>Door</th>
                                <th></th>
                            </tr>
                            @foreach($patient->detail->data['actions']??[] as $k=>$action)
                                <tr>
                                    <td>{{$k}}</td>
                                    <td>{{$action['note']}}</td>
                                    <td>{{$action['content']}}</td>
                                    <td>{{$action['by']}}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>


                    <div class="p-2">
                        <label>Patiënt opnieuw uitnodigen (let op stap terug is niet mogelijk)</label><br>
                        <span class="text-xs">Eerdere uitnodigingsbrieven en afstel zal worden gewist</span><br>
                        <button wire:click="reInvite" class="bg-red-700 text-white p-1 rounded">Opnieuw uitnodigen
                        </button>
                    </div>
                    @if(!$patient->stop)
                        <div class="p-2">
                            <label>Patiënt will niet</label><br>
                            <input type="text" wire:model.lazy="dontWantReason" class="input rounded">
                            <button wire:click="dontWant" class="bg-red-700 text-white p-1 rounded">Wil niet
                            </button>
                        </div>
                    @endif
                    @if(!$patient->stop)
                        <div class="p-2">
                            <label>Geen diabetes</label><br>
                            <button wire:click="noDiabetes" class="bg-red-700 text-white p-1 rounded">Geen diabetes
                            </button>
                        </div>
                    @endif
                    <div class="p-2">
                        <label>Verwijder patiënt</label><br>
                        <button wire:click="deletePatient" class="bg-red-700 text-white p-1 rounded">Verwijder
                        </button>
                        <span class="text-xs">(geen
                            stap terug mogelijk)</span>
                    </div>
                </div>
            </div>
        </div>


    @endif
</div>

