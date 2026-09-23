<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('sitemap:generate')->daily();
        $schedule->command('app:check-vendor-plans')->daily(); // Runs once a day at midnight
        $schedule->command('app:send-expiry-warnings')->daily(); // Runs once per day
        $schedule->command('tiers:notify-expiring')->dailyAt('09:00');
        $schedule->command('app:clean-temp-product-files')->daily();
        $schedule->command('app:send-digital-delivery-sla-reminders')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
