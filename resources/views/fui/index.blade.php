<x-app-layout>

    <div class="bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mx-auto py-16 sm:py-24 lg:py-32 lg:max-w-none">
                <h2 class="text-2xl font-extrabold text-gray-900">Fundus FollowUp applicatie</h2>

                <div class="mt-6 space-y-12 lg:space-y-0 lg:grid lg:grid-cols-3 lg:gap-x-6">
                    <a href="{{route('fui.overzicht')}}">
                        <div class="group relative border rounded-lg p-4 bg-blue-100 h-48">
                            <h3 class="mt-6 text-xl font-bold text-gray-500">

                                <span class="absolute inset-0"></span>
                                Overzicht

                            </h3>
                            <p class="text-base font-semibold text-gray-900">Een overzicht van alle fundus patiënten</p>
                        </div>
                    </a>
                    <a href="{{route('fui.printen')}}">
                        <div class="group relative border rounded-lg p-4 bg-blue-100 h-48">
                            <h3 class="mt-6 text-xl font-bold text-gray-500">

                                <span class="absolute inset-0"></span>
                                Printen

                            </h3>
                            <p class="text-base font-semibold text-gray-900">Oproepen voor fundus onderzoek printen</p>
                        </div>
                    </a><a href="{{route('fui.toekomstige-oproepen')}}">
                        <div class="group relative border rounded-lg p-4 bg-blue-100 h-48">
                            <h3 class="mt-6 text-xl font-bold text-gray-500">

                                <span class="absolute inset-0"></span>
                                Toekomst

                            </h3>
                            <p class="text-base font-semibold text-gray-900">Overzicht van aantallen patiënten die
                                komende
                                periode moeten worden opgeroepen</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    </x-fuinvite-app-layout>