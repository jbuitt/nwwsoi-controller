<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Snapshot data for Horizon
        $schedule
            ->command('horizon:snapshot')
            ->everyFiveMinutes();

        // Prune telescope entries
        if ($this->app->environment('local')) {
            $schedule
                ->command('telescope:prune')
                ->daily();
        }

        // Purge old products files
        $schedule
            ->command('nwwsoi-controller:purge_old_products ' . config('nwwsoi-controller.days_to_keep_products'))
            ->daily();

        // Purge old log files
        $schedule
            ->command('nwwsoi-controller:purge_old_logs ' . config('nwwsoi-controller.days_to_keep_logs'))
            ->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
