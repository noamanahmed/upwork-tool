<?php

namespace App\Console\Commands;

use App\Jobs\JobSearch as JobsJobSearch;
use App\Models\JobSearch as ModelsJobSearch;
use Illuminate\Console\Command;

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
        $JobSearches = ModelsJobSearch::query()->get();

        foreach($JobSearches as $search)
        {
            JobsJobSearch::dispatch($search);
        }
    }
}
