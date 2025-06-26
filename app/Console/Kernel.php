<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        if ($this->shouldRunUpworkTasks()) {

            $schedule->command('upwork:search')->everyTenSeconds();
            // $schedule->command('upwork:rss-search')->everyTenSeconds();

            $schedule->command('upwork:send-slack-notifications')->everyFiveSeconds();
            // $schedule->command('upwork:send-rss-slack-notifications')->everyFiveSeconds();

            $schedule->command('upwork:proposals')->everyFifteenMinutes();
        }
        $schedule->command('upwork:prune-telescope')->everyFifteenMinutes();
        $schedule->command('telescope:prune --hours=96')->everyThreeHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function shouldRunUpworkTasks(): bool
    {
        $timezone = 'Asia/Karachi'; // Pakistan time

        $now = Carbon::now($timezone);
        $day = strtolower($now->format('D')); // mon, tue, etc.

        $allowedDays = config('app.upwork_cron.days', []);
        if (!in_array($day, $allowedDays)) {
            return false;
        }

        $start = Carbon::createFromFormat('H:i', config('app.upwork_cron.start_time'), $timezone);
        $end = Carbon::createFromFormat('H:i', config('app.upwork_cron.end_time'), $timezone);

        
        // Handle overnight range (e.g., 21:00 to 03:00)
        if ($start->greaterThan($end)) {
            return $now->greaterThanOrEqualTo($start) || $now->lessThanOrEqualTo($end);
        }

        return $now->between($start, $end);
    }
}
