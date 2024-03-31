<?php

namespace App\Jobs;

use App\Models\JobSearch as ModelsJobSearch;
use App\Repositories\JobSearchRepository;
use App\Services\JobSearchService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        app(JobService::class)->insertJobsFromApiResponse($jobs);
    }
}
