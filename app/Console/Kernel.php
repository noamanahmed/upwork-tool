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
        $schedule->command('upwork:search')->everyTenSeconds();
        $schedule->command('upwork:send-slack-notifications')->everyFiveSeconds();
        $schedule->command('telescope:prune --hours=96')->everyThreeHours();
        $schedule->command('upwork:prune-telescope')->everyFifteenMinutes();

        $schedule->command('upwork:rss-search')->everyTenSeconds();
        $schedule->command('upwork:send-rss-slack-notifications')->everyFiveSeconds();
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
