<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\View\Composers\NotificationComposer;
use App\View\Composers\SupervisorBadgeComposer;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share notifications data with all views
        view()->composer('*', NotificationComposer::class);
        
        // Share supervisor badge counts with supervisor layout views
        view()->composer('Supervisor.layout.*', SupervisorBadgeComposer::class);
    }

    public function register()
    {
        //
    }
}