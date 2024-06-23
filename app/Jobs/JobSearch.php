<?php

namespace App\Jobs;

use App\Models\JobSearch as ModelsJobSearch;
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
use Illuminate\Support\Facades\Log;

class JobSearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public ModelsJobSearch $jobSearch)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $options =  $this->jobSearch->toArray();
        $jobs = app(UpWorkService::class)->jobs($options);
        // Check if the lock exists
        if (Cache::has('job_service_dispatch_job_'.$this->jobSearch->id)) {
            // Obtain the lock instance
            $lock = Cache::lock('job_service_dispatch_job_'.$this->jobSearch->id,30);
            // Release the lock
            $lock->release();
            Log::warning("Removing Lock for $this->jobSearch->id");
        }
        app(JobService::class)->insertJobsFromApiResponse($jobs);
        app(JobService::class)->attachJobsToJobSearchesFromApiResponse($jobs,$this->jobSearch);
        app(CategoryService::class)->attachCategoriesToJobsFromApiResponse($jobs);
        app(JobActivityService::class)->insertActivitiesFromApiResponse($jobs);

    }
}
