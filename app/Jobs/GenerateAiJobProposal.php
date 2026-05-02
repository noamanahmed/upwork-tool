<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class GenerateAiJobProposal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public \App\Models\AiJobProposal $aiJobProposal
    ) {
    }

    public function handle(): void
    {
        $provider = $this->aiJobProposal->provider;
        $apiKey = config("services.ai.{$provider}.key", 'unknown');
        // generate key based on provider and api_key
        $key = 'generate_ai_proposal_' . $provider . '_' . md5($apiKey);
        $rateLimit = (int) config('services.ai.rate_limit', 5);

        // Throttle using Redis to limit max requests per minute (60 seconds)
        Redis::throttle($key)
            ->block(0)->allow($rateLimit)->every(60)
            ->then(function () {
                $this->processProposal();
            }, function () {
                // Could not obtain lock; push the job back onto the queue
                $this->release(15);
            });
    }

    protected function processProposal(): void
    {
        try {
            $jobId = $this->aiJobProposal->job_id;
            $job = \App\Models\Job::find($jobId);

            if (!$job) {
                throw new \Exception("Job not found.");
            }

            // Extract relevant job details to pass to the agent
            $jobData = [
                'title' => $job->title,
                'description' => $job->slack_notification_message ?? null,
            ];

            $promptText = $this->aiJobProposal->getPromptText() . json_encode($jobData, JSON_PRETTY_PRINT);

            $agent = app(\App\Ai\Agents\AiJobProposalAgent::class);
            $agent->setConversationId($this->aiJobProposal->conversation_id);

            // dd($this->aiJobProposal->toArray(), $promptText);
            // Execute using the stored provider and model
            $response = $agent->prompt(
                prompt: $promptText,
                provider: $this->aiJobProposal->provider,
                model: $this->aiJobProposal->model
            );

            $this->aiJobProposal->update([
                'proposal' => (string) $response,
                'status' => 'completed'
            ]);

        } catch (\Exception $e) {
            $this->aiJobProposal->update([
                'status' => 'failed',
                'proposal' => 'Error generating proposal: ' . $e->getMessage()
            ]);

            throw $e;
        }
    }
}
