<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\View\Composers\NotificationComposer;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Share notifications data with all views
        view()->composer('*', NotificationComposer::class);
    }

    public function register()
    {
        //
    }
}