<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobSearch;
use App\Models\Proposal;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Cache;
use Illuminate\Console\Command;

class GetProposalJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:proposal-jobs';

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
        $proposals = Cache::remember('job_searches', 15 * 60, function () {
            return Proposal::query()->get();
        });
        $upworkJobIds = [];
        foreach($proposals as $proposal)
        {
            if($proposal->job) continue;
            $this->line('Starting Fetching Jobs for Proposal with job title :' .  $proposal->job_title);
            $proposalArray = [];
            $proposalArray['title'] = $proposal->job_title;
            $jobs = app(UpWorkService::class)->jobs($proposalArray);
            app(JobService::class)->insertJobsFromApiResponse($jobs);

            if(count($jobs) > 1 || count($jobs) === 0)
            {
                $this->warn('Number of Jobs found :'.count($jobs));
            }
            app(CategoryService::class)->attachCategoriesToJobsFromApiResponse($jobs);
            app(JobActivityService::class)->insertActivitiesFromApiResponse($jobs);
            $this->line('Ending Fetching Jobs for Proposal with job title :'.  $proposal->job_title);
            $upworkJobIds[] = $proposal['upwork_job_id'];
        }

        $this->line('Attaching Jobs with Proposals');
        foreach($proposals as $proposal)
        {
            if(!is_null($proposal->job_id)) continue;
            $alreadyExistingJobs = Job::whereIn('upwork_id',$upworkJobIds)->get()->keyBy('upwork_id')->toArray();
            if(! ($alreadyExistingJobs[$proposal->upwork_job_id] ?? false) ) continue;
            $proposal->job_id = $alreadyExistingJobs[$proposal->upwork_job_id]['id'];
            $proposal->save();
        }
    }

}
