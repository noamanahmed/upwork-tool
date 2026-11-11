<?php

namespace App\Console\Commands;

use App\Enums\AiProposalStatusEnum;
use App\Jobs\GenerateAiJobProposal;
use App\Models\AiJobProposal;
use App\Models\Job;
use App\Services\AiProposalService;
use Illuminate\Console\Command;

class GenerateAiProposals extends Command
{
    protected $signature = 'upwork:generate-ai-proposals 
                            {hours=24 : Look back window in hours}
                            {job_id? : Optional specific job ID}';

    protected $description = 'Safety net: dispatches AI proposal generation for jobs that were missed';

    public const STUCK_AFTER_MINUTES = 30;

    public const MAX_JOBS = 500;

    public function handle(): void
    {
        $aiProposals = app(AiProposalService::class);
        $providers = $aiProposals->enabledProviders();

        if (empty($providers)) {
            $this->error('No AI providers are enabled. Check AI_<PROVIDER>_ENABLED flags in .env.');

            return;
        }

        $jobId = $this->argument('job_id');

        if ($jobId) {
            $job = Job::find($jobId);

            if (!$job) {
                $this->error("Job #{$jobId} not found.");

                return;
            }

            $dispatched = $aiProposals->dispatchForJob($job);
            $this->info("Dispatched {$dispatched} proposal generation job(s) for job #{$jobId}.");

            return;
        }

        $hours = (int) $this->argument('hours');

        $jobs = Job::query()
            ->where('created_at', '>=', now()->subHours($hours))
            ->where(function ($query) use ($providers) {
                foreach ($providers as $provider) {
                    $query->orWhereDoesntHave('aiProposals', function ($proposalQuery) use ($provider) {
                        $proposalQuery->where('provider', $provider['provider'])
                            ->whereIn('status', AiProposalStatusEnum::dispatched());
                    });
                }
            })
            ->orderBy('id')
            ->limit(self::MAX_JOBS)
            ->get();

        $dispatched = 0;

        foreach ($jobs as $job) {
            $dispatched += $aiProposals->dispatchForJob($job);
        }

        $this->info("Dispatched {$dispatched} proposal generation job(s) across {$jobs->count()} job(s).");

        $this->requeueStuckProposals();
    }

    /**
     * Recover rows whose worker died mid-flight (the old pipeline left these
     * stuck in "generating" forever).
     */
    protected function requeueStuckProposals(): void
    {
        $stuck = AiJobProposal::query()
            ->whereIn('status', AiProposalStatusEnum::inFlight())
            ->where('updated_at', '<', now()->subMinutes(self::STUCK_AFTER_MINUTES))
            ->orderBy('id')
            ->limit(self::MAX_JOBS)
            ->pluck('id');

        foreach ($stuck as $proposalId) {
            GenerateAiJobProposal::dispatch($proposalId)->onQueue('ai');
        }

        $this->info("Re-dispatched {$stuck->count()} stuck proposal(s).");
    }
}
