<?php

namespace App\Providers;

// Externals
use Illuminate\Support\ServiceProvider;
use TwitchApi\TwitchApi;

class TwitchApiServiceProvider extends ServiceProvider
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
        $this->app->singleton('twitchapi', function ($app)
        {
            $options = [
                'client_id' => config('twitch.client_id')
            ];
            return new TwitchApi($options);
        });
    }
}
