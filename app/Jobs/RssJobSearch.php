<?php

namespace App\Jobs;

use App\Models\RssJobSearches;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RssJobSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'scraping';

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public RssJobSearches $rssJobSearch)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $jobs = app(UpWorkService::class)->rssJobs($this->rssJobSearch);

        app(JobService::class)->insertRssJobs($jobs, $this->rssJobSearch);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('RssJobSearch job failed', [
            'rss_job_search_id' => $this->rssJobSearch->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
