<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\JobSearch;
use App\Models\JobSearchPivot;
use App\Services\ThirdParty\SlackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendJobSlackNotification implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'notifications';

    public $tries = 5;

    public $backoff = [5, 15, 60, 120, 300];

    public $timeout = 20;

    public $uniqueFor = 3600;

    public function __construct(
        public int $jobId,
        public int $jobSearchId,
    ) {
    }

    public function uniqueId(): string
    {
        return $this->jobId . ':' . $this->jobSearchId;
    }

    public function handle(SlackService $slack): void
    {
        $pivot = JobSearchPivot::where('job_id', $this->jobId)
            ->where('job_search_id', $this->jobSearchId)
            ->first();

        if (!$pivot || $pivot->is_slack_webhook_sent) {
            return;
        }

        $search = JobSearch::find($this->jobSearchId);
        $job = Job::find($this->jobId);

        if (!$job || !$search) {
            return;
        }

        if (empty($search->slack_webhook_url)) {
            JobSearchPivot::where('job_id', $this->jobId)
                ->where('job_search_id', $this->jobSearchId)
                ->update([
                    'slack_attempts' => $pivot->slack_attempts + 1,
                    'slack_last_error' => 'no_webhook',
                ]);

            Log::warning('Slack notification skipped: search has no webhook', [
                'job_id' => $this->jobId,
                'job_search_id' => $this->jobSearchId,
            ]);

            return;
        }

        $slack->sendMessage($search->slack_webhook_url, $job->slack_notification_message);

        JobSearchPivot::where('job_id', $this->jobId)
            ->where('job_search_id', $this->jobSearchId)
            ->update([
                'is_slack_webhook_sent' => 1,
                'slack_sent_at' => now(),
                'slack_last_error' => null,
            ]);

        $this->markJobNotifiedIfFullySent();
    }

    public function failed(Throwable $exception): void
    {
        JobSearchPivot::where('job_id', $this->jobId)
            ->where('job_search_id', $this->jobSearchId)
            ->increment('slack_attempts', 1, [
                'slack_last_error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

        Log::error('Slack notification failed permanently', [
            'job_id' => $this->jobId,
            'job_search_id' => $this->jobSearchId,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Keep the denormalized jobs.is_slack_webhook_sent flag in sync for the
     * Filament/Analytics readers that still use it.
     */
    protected function markJobNotifiedIfFullySent(): void
    {
        $stillPending = JobSearchPivot::where('job_id', $this->jobId)
            ->where('is_slack_webhook_sent', false)
            ->exists();

        if (!$stillPending) {
            Job::where('id', $this->jobId)->update(['is_slack_webhook_sent' => 1]);
        }
    }
}
