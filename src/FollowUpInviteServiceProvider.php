<?php

namespace mmerlijn\followUpInvite;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use mmerlijn\followUpInvite\Http\Livewire\Overzicht;
use mmerlijn\followUpInvite\Http\Livewire\Patient;
use mmerlijn\followUpInvite\Http\Livewire\Printen;
use mmerlijn\followUpInvite\Http\Livewire\Toekomstigeoproepen;
use mmerlijn\followUpInvite\Models\FollowUpPatient;
use mmerlijn\followUpInvite\Observers\FollowUpPatientObserver;
use mmerlijn\followUpInvite\View\Components\AppLayout;
use mmerlijn\followUpInvite\View\Components\GuestLayout;

class FollowUpInviteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/fuinvite.php' => config_path('fuinvite.php'),
            ], 'config');
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'migrations');
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/follow-up-invite'),
            ], 'views');
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/fuinvite.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'fuinvite');
        $this->loadViewComponentsAs('fuinvite', [
            AppLayout::class,
            GuestLayout::class,
            //   Alert::class,
            //   Button::class,
        ]);
        $this->livewireComponents();
        if ($this->app->runningInConsole()) {
            $this->commands([
                // registering the new command
            ]);
        }
        FollowUpPatient::observe(FollowUpPatientObserver::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/fuinvite.php',
            'fuinvite'
        );
    }

    protected function livewireComponents()
    {
        Livewire::component('fui-overzicht', Overzicht::class);
        Livewire::component('fui-printen', Printen::class);
        Livewire::component('fui-toekomstigeoproepen', Toekomstigeoproepen::class);
        Livewire::component('fui-patient', Patient::class);

    }

}