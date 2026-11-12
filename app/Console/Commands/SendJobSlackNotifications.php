<?php

namespace App\Console\Commands;

use App\Jobs\SendJobSlackNotification;
use App\Models\JobSearchPivot;
use Illuminate\Console\Command;

class SendJobSlackNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:send-slack-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-dispatches Slack notifications that were never delivered';

    public const MIN_AGE_MINUTES = 2;

    public const MAX_ATTEMPTS = 5;

    public const BATCH_SIZE = 200;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pending = JobSearchPivot::query()
            ->where('is_slack_webhook_sent', false)
            ->where('slack_attempts', '<', self::MAX_ATTEMPTS)
            ->where('created_at', '<', now()->subMinutes(self::MIN_AGE_MINUTES))
            ->orderBy('id')
            ->limit(self::BATCH_SIZE)
            ->get(['job_id', 'job_search_id']);

        foreach ($pending as $pivot) {
            SendJobSlackNotification::dispatch($pivot->job_id, $pivot->job_search_id);
        }

        $this->info("Dispatched {$pending->count()} pending Slack notification job(s).");
    }
}
