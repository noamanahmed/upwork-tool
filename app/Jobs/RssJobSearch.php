<?php

namespace App\Jobs;

use App\Models\RssJobSearches;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RssJobSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $options =  $this->rssJobSearch->toArray();
        $jobs = app(UpWorkService::class)->rssJobs($this->rssJobSearch);
        app(JobService::class)->insertRssJobs($jobs,$this->rssJobSearch);

        // Check if the lock exists
        if (Cache::has('job_service_dispatch_rss_job_'.$this->rssJobSearch->id)) {
            // Obtain the lock instance
            $lock = Cache::lock('job_service_dispatch_rss_job_'.$this->rssJobSearch->id);
            // Release the lock
            $lock->release();
        }
    }
}
