<?php

namespace App\Providers\ObjectLifecycle;

use App\Relay;
use Illuminate\Support\ServiceProvider;

class RelayProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Relay::creating(function (Relay $relay) {
            $relay->setInitialProperties();
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
