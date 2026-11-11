<?php

namespace App\Services;

use App\Enums\AiProposalStatusEnum;
use App\Jobs\GenerateAiJobProposal;
use App\Models\AiJobProposal;
use App\Models\Job;
use Illuminate\Support\Facades\Log;

class AiProposalService
{
    /**
     * Enabled providers with the model/conversation config they need.
     *
     * @return array<int, array{provider: string, model: string, conversation_id: string}>
     */
    public function enabledProviders(): array
    {
        $providers = [];

        foreach (config('services.ai.enabled_providers', []) as $provider) {
            $model = config("services.ai.{$provider}.model");
            $conversationId = config("services.ai.{$provider}.conversation_id");
            $key = config("services.ai.{$provider}.key");

            if (!$model || !$conversationId) {
                continue;
            }

            // A null key means the provider credentials were never configured
            // (an empty string stays valid, e.g. local Ollama).
            if ($key === null) {
                Log::warning("Skipping AI provider [{$provider}]: no API key configured.");

                continue;
            }

            $providers[] = [
                'provider' => $provider,
                'model' => $model,
                'conversation_id' => $conversationId,
            ];
        }

        return $providers;
    }

    /**
     * Ensure every enabled provider has a proposal row for the job and queue
     * the generation jobs. Returns the number of jobs dispatched.
     */
    public function dispatchForJob(Job $job, ?string $provider = null): int
    {
        $dispatched = 0;

        foreach ($this->enabledProviders() as $config) {
            if ($provider !== null && $config['provider'] !== $provider) {
                continue;
            }

            $proposal = AiJobProposal::firstOrCreate(
                [
                    'job_id' => $job->id,
                    'provider' => $config['provider'],
                ],
                [
                    'status' => AiProposalStatusEnum::QUEUED->value,
                    'model' => $config['model'],
                    'conversation_id' => $config['conversation_id'],
                    'proposal' => 'N/A',
                    'queued_at' => now(),
                ]
            );

            if (!$proposal->wasRecentlyCreated) {
                if (in_array($proposal->status, AiProposalStatusEnum::dispatched(), true)) {
                    continue;
                }

                $proposal->update([
                    'status' => AiProposalStatusEnum::QUEUED->value,
                    'proposal' => 'N/A',
                    'attempts' => 0,
                    'last_error' => null,
                    'queued_at' => now(),
                ]);
            }

            if (empty($proposal->prompt)) {
                $proposal->prompt = $proposal->getPromptText();
                $proposal->instructions = $proposal->getModelInstructions();
                $proposal->save();
            }

            GenerateAiJobProposal::dispatch($proposal->id)->onQueue('ai');
            $dispatched++;
        }

        return $dispatched;
    }
}
