<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobSearch;
use App\Models\Proposal;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobService;
use App\Services\ProposalService;
use App\Services\UpWorkService;
use Cache;
use Illuminate\Console\Command;

class GetProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:proposals';

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
        $proposals = app(UpWorkService::class)->proposals();
        app(ProposalService::class)->insertProposalsFromApiResponse($proposals);
    }

}
