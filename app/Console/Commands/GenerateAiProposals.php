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

    protected $description = 'Dispatch jobs to generate AI proposals';

    public function handle(): void
    {
        [$provider, $model, $conversationId] = $this->resolveConfig();

        if (!$provider || !$model || !$conversationId) {
            $this->error('Invalid AI configuration.');
            return;
        }

        $jobId = $this->argument('job_id');

        if ($jobId) {
            $this->handleSingleJob((int) $jobId, $provider, $model, $conversationId);
            return;
        }

        $this->handleBatch($provider, $model, $conversationId);
    }

    /**
     * Resolve AI config
     */
    protected function resolveConfig(): array
    {
        $provider = config('services.ai.provider');
        $model = config("services.ai.{$provider}.model", 'gpt-4');
        $conversationId = config("services.ai.{$provider}.conversation_id", 'openai');

        return [$provider, $model, $conversationId];
    }

    /**
     * Handle batch mode
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

        $this->info("Done! Dispatched {$count} proposals.");
    }

    /**
     * Handle single job mode
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
                $this->warn("Job #{$jobId} is already being generated.");
                return;
            }

            if ($existing->status === 'completed') {
                $this->error("Proposal already exists for Job #{$jobId}.");
                return;
            }

            if ($existing->status === 'failed') {
                $existing->delete();
            }
        }

        if ($this->dispatchProposal($job, $provider, $model, $conversationId)) {
            $this->info("Dispatched proposal for Job #{$jobId}.");
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
            $this->info("Skipping job #{$job->id}: already generating.");
            return true;
        }

        if ($existing->status === 'completed') {
            $this->info("Skipping job #{$job->id}: already completed.");
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

        $this->info("Dispatched job #{$job->id}");

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