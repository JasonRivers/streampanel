<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ObjectLifecycleProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\App\Providers\ObjectLifecycle\RelayProvider::class);
    }
}
