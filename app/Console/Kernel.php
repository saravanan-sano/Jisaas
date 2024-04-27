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
        \App\SuperAdmin\Commands\UpdateLicenseExpiry::class,
        \App\SuperAdmin\Commands\NotifyLicenseExpiryPre::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (app_type() == 'multiple') {
            $schedule->command(\App\SuperAdmin\Commands\UpdateLicenseExpiry::class)->everyTwoMinutes();
            $schedule->command(\App\SuperAdmin\Commands\NotifyLicenseExpiryPre::class)->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
