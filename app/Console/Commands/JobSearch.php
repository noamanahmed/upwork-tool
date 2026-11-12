<?php

namespace App\Console\Commands;

use App\Enums\JobSearchStatusEnum;
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
        $jobSearches = Cache::remember('job_searches_active', 15 * 60, function () {
            return ModelsJobSearch::query()
                ->where('status', JobSearchStatusEnum::ACTIVE->value)
                ->get();
        });

        foreach ($jobSearches as $search) {
            JobsJobSearch::dispatch($search->id);
            $this->line('Dispatched search ID: ' . $search->id);
        }
    }
}
