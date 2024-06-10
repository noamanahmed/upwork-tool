<?php

namespace App\Jobs;

use App\Models\Job;
use App\Repositories\JobSearchRepository;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobSearchService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class JobActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Job $upworkJob,public int $delayInSeconds)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activities = app(UpWorkService::class)->jobActivity($this->upworkJob->toArray());
        app(JobActivityService::class)->updatejobActivities($this->upworkJob,$activities,$this->delayInSeconds);
        // Check if the lock exists
        if (Cache::has('job_activity_service_dispatch_update_job_activity'.$this->upworkJob->id)) {
            // Obtain the lock instance
            $lock = Cache::lock('job_activity_service_dispatch_update_job_activity'.$this->upworkJob->id);
            // Release the lock
            $lock->release();
        }
    }
}
