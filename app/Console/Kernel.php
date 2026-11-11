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

            // Discovery only queues work. Scraping, Slack and AI run on
            // separate Horizon queues, so these commands return immediately.
            $schedule->command('upwork:search')->everyTenSeconds()->withoutOverlapping(1);
            // $schedule->command('upwork:rss-search')->everyTenSeconds()->withoutOverlapping(1);

            $schedule->command('upwork:proposals')->everyFifteenMinutes()->withoutOverlapping();
        }

        // Safety nets. They stay outside the scrape window so queued work drains.
        $schedule->command('upwork:send-slack-notifications')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('upwork:generate-ai-proposals')->everyTenMinutes()->withoutOverlapping();

        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('queue:prune-failed --hours=168')->daily();
        $schedule->command('upwork:prune-telescope')->everyFifteenMinutes();
        $schedule->command('telescope:prune --hours=8')->hourly();
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
        $isAllowed = config('app.upwork_cron.enabled', true);
        if( !$isAllowed)
        {
            return false;
        }

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
