<?php

namespace App\Console\Commands;

use App\Jobs\RssJobSearch as JobsRssJobSearch;
use App\Models\RssJobSearches;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RssJobSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:rss-search';

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
        $JobSearches = Cache::remember('rss_job_searches', 15 * 60, function () {
            return RssJobSearches::query()->get();
        });

        foreach($JobSearches as $search)
        {
            $lock = Cache::lock('job_service_dispatch_rss_job_'.$search->id,30);
            if ($lock->get()) {
                $this->line('Acquired JOB Lock for RSS JOB Search ID:'.$search->id);
                JobsRssJobSearch::dispatch($search);
            }else{
                $this->line('Cannot Acquire JOB Lock for RSS Job Search ID:'.$search->id);
            }
        }
    }
}
