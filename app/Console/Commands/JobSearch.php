<?php

namespace App\Console\Commands;

use App\Jobs\JobSearch as JobsJobSearch;
use App\Models\JobSearch as ModelsJobSearch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class JobSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches job to search for upwork jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $JobSearches = Cache::remember('job_searches', 15 * 60, function () {
            return ModelsJobSearch::query()->get();
        });

        foreach($JobSearches as $search)
        {
            $lock = Cache::lock('job_service_dispatch_job_'.$search->id,30);
            if ($lock->get()) {
                $this->line('Acquired JOB Lock for Search ID:'.$search->id);
                JobsJobSearch::dispatch($search);
            }else{
                $this->line('Cannot Acquire JOB Lock for Search ID:'.$search->id);
            }
        }
    }
}
