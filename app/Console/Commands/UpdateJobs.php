<?php

namespace App\Console\Commands;

use App\Models\JobSearch;
use App\Models\Proposal;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Cache;
use Illuminate\Console\Command;

class UpdateJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:jobs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobSearches = Cache::remember('job_searches', 15 * 60, function () {
            return JobSearch::query()->get();
        });

        foreach($jobSearches as $jobSearch)
        {
            $this->line('Starting Fetching Jobs for '.$jobSearch->name);
            $this->fetchJobSearchSpecificAllReleventJobs($jobSearch);
            $this->line('Ending Fetching Jobs for '.$jobSearch->name);
        }
    }

    public function fetchJobSearchSpecificAllReleventJobs(JobSearch $jobSearch)
    {
        $lastNDays = 30 * 12;
        $maxRecords = 10000;
        $start = 0;
        $limit = 100;
        $jobs = [];
        while($start < $maxRecords)
        {
            $this->line('Fetching Jobs from '.$start);
            $cacheKey = 'upwork_job_search_'.$jobSearch->id.'_limit_'.$limit.'_start_'.$start;
            if(Cache::has($cacheKey))
            {
                $data = Cache::get($cacheKey);
            }else{
                $jobSearchArray = [];
                $jobSearchArray['q'] = $jobSearch->q;
                $jobSearchArray['limit'] = $limit;
                $jobSearchArray['start'] = $start;
                $jobSearchArray['days_posted'] = $lastNDays;
                $data = app(UpWorkService::class)->jobs($jobSearchArray);
                sleep(1);
                if(empty($data)) break;
                Cache::set($cacheKey,$data,3600);
            }
            if(empty($data)) break;
            $jobs = [...$jobs,...$data];
            $start += $limit;
        }

        app(JobService::class)->insertJobsFromApiResponse($jobs);
        app(CategoryService::class)->attachCategoriesToJobsFromApiResponse($jobs);
        app(JobActivityService::class)->insertActivitiesFromApiResponse($jobs);
    }
}
