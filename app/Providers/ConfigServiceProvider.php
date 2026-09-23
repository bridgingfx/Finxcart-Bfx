<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        try {
            $timezone = getWebConfig(name: 'timezone');
            if ($timezone) {
                Config::set('timezone', $timezone);
                date_default_timezone_set($timezone);
            }
        } catch (\Exception $ex) {}
    }
}
