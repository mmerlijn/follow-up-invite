<?php

use Illuminate\Support\Facades\Route;


Route::prefix('follow-up-invite')->middleware(['web', 'auth', 'verified', 'permission:fundus'])->group(function () { //

    Route::get('/overzicht/{type?}', \mmerlijn\followUpInvite\Http\Livewire\Overzicht::class)->name('fui.overzicht');
    Route::get('/printen/{type?}', \mmerlijn\followUpInvite\Http\Livewire\Printen::class)->name('fui.printen');
    Route::get('/toekomst/{type?}', \mmerlijn\followUpInvite\Http\Livewire\Toekomstigeoproepen::class)->name('fui.toekomstige-oproepen');
    Route::get('/patient/{id}', \mmerlijn\followUpInvite\Http\Livewire\Patient::class)->name('fui.patient');
    Route::get('/{type?}', [\mmerlijn\followUpInvite\Http\Controllers\FollowUpInviteController::class, 'index'])->name('fui.index');

});

/*
//debug only, need to be scheduled
Route::get('/rundaily', function () {
    \mmerlijn\followUpInvite\Jobs\FollowUpInviteRunDailyMijnSalt::dispatch();
    echo "Run daily";
});

//debug only, need to be scheduled
Route::get('/runones', function () {
    \mmerlijn\followUpInvite\Jobs\FollowUpInviteRunOnesMijnSalt::dispatch();
    echo "Run Ones";
});
//debug only, need to be scheduled
Route::get('/runextra', function () {
    //\mmerlijn\followUpInvite\Jobs\ClearWaitUntilJob::dispatch();
    //\mmerlijn\followUpInvite\Jobs\NoResponseInvitationJob::dispatch();
    //\mmerlijn\followUpInvite\Jobs\NotActiveMijnsaltContactJob::dispatch();
    \mmerlijn\followUpInvite\Jobs\NoShowJob::dispatch();

    echo "klaar met run extra";
});
*/