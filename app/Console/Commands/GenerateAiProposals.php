<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Job;
use App\Models\AiJobProposal;
use App\Jobs\GenerateAiJobProposal;

class GenerateAiProposals extends Command
{
    /**
     * Command signature
     */
    protected $signature = 'upwork:generate-ai-proposals 
                            {hours=1 : Look back window in hours}
                            {job_id? : Optional specific job ID}';

    protected $description = 'Dispatch jobs to generate AI proposals for all enabled providers';

    public function handle(): void
    {
        $providers = $this->resolveEnabledProviders();

        if (empty($providers)) {
            $this->error('No AI providers are enabled. Check AI_<PROVIDER>_ENABLED flags in .env.');
            return;
        }

        $this->info('Enabled providers: ' . implode(', ', array_column($providers, 'provider')));

        $jobId = $this->argument('job_id');

        foreach ($providers as $config) {
            $this->line("--- Processing provider: {$config['provider']} ---");

            if ($jobId) {
                $this->handleSingleJob((int) $jobId, $config['provider'], $config['model'], $config['conversation_id']);
            } else {
                $this->handleBatch($config['provider'], $config['model'], $config['conversation_id']);
            }
        }
    }

    /**
     * Resolve all enabled providers and their config.
     * Returns array of ['provider', 'model', 'conversation_id'] tuples.
     */
    protected function resolveEnabledProviders(): array
    {
        $enabledProviders = config('services.ai.enabled_providers', []);
        $result = [];

        foreach ($enabledProviders as $provider) {
            $model = config("services.ai.{$provider}.model");
            $conversationId = config("services.ai.{$provider}.conversation_id");

            if (!$model || !$conversationId) {
                $this->warn("Skipping provider [{$provider}]: missing model or conversation_id config.");
                continue;
            }

            $result[] = [
                'provider' => $provider,
                'model' => $model,
                'conversation_id' => $conversationId,
            ];
        }

        return $result;
    }

    /**
     * Handle batch mode for a single provider
     */
    protected function handleBatch(string $provider, string $model, string $conversationId): void
    {
        $hours = (int) $this->argument('hours');

        $jobs = Job::where('created_at', '>=', now()->subHours($hours))
            ->with('aiProposals')
            ->get();

        $count = 0;

        foreach ($jobs as $job) {
            if ($this->shouldSkip($job, $provider)) {
                continue;
            }

            if ($this->dispatchProposal($job, $provider, $model, $conversationId)) {
                $count++;
            }
        }

        $this->info("[{$provider}] Done! Dispatched {$count} proposals.");
    }

    /**
     * Handle single job mode for a single provider
     */
    protected function handleSingleJob(int $jobId, string $provider, string $model, string $conversationId): void
    {
        $job = Job::with('aiProposals')->find($jobId);

        if (!$job) {
            $this->error("Job #{$jobId} not found.");
            return;
        }

        $existing = $this->getExistingProposal($job, $provider);

        if ($existing) {
            if ($existing->status === 'generating') {
                $this->warn("[{$provider}] Job #{$jobId} is already being generated.");
                return;
            }

            if ($existing->status === 'completed') {
                $this->error("[{$provider}] Proposal already exists for Job #{$jobId}.");
                return;
            }

            if ($existing->status === 'failed') {
                $existing->delete();
            }
        }

        if ($this->dispatchProposal($job, $provider, $model, $conversationId)) {
            $this->info("[{$provider}] Dispatched proposal for Job #{$jobId}.");
        }
    }

    /**
     * Decide if job should be skipped in batch mode
     */
    protected function shouldSkip(Job $job, string $provider): bool
    {
        $existing = $this->getExistingProposal($job, $provider);

        if (!$existing) {
            return false;
        }

        if ($existing->status === 'generating') {
            $this->info("[{$provider}] Skipping job #{$job->id}: already generating.");
            return true;
        }

        if ($existing->status === 'completed') {
            $this->info("[{$provider}] Skipping job #{$job->id}: already completed.");
            return true;
        }

        if ($existing->status === 'failed') {
            $existing->delete();
            return false;
        }

        return false;
    }

    /**
     * Dispatch proposal generation
     */
    protected function dispatchProposal(Job $job, string $provider, string $model, string $conversationId): bool
    {
        $proposal = $this->createProposal($job, $provider, $model, $conversationId);

        dispatch(new GenerateAiJobProposal($proposal));

        $this->info("[{$provider}] Dispatched job #{$job->id}");

        return true;
    }

    /**
     * Create proposal record
     */
    protected function createProposal(Job $job, string $provider, string $model, string $conversationId): AiJobProposal
    {
        $proposal = AiJobProposal::create([
            'job_id' => $job->id,
            'status' => 'generating',
            'provider' => $provider,
            'model' => $model,
            'conversation_id' => $conversationId,
            'proposal' => 'N/A',
        ]);

        $proposal->prompt = $proposal->getPromptText();
        $proposal->instructions = $proposal->getModelInstructions();
        $proposal->save();

        return $proposal->fresh();
    }

    /**
     * Get existing proposal for provider
     */
    protected function getExistingProposal(Job $job, string $provider): ?AiJobProposal
    {
        return $job->aiProposals
            ->where('provider', $provider)
            ->first();
    }
}