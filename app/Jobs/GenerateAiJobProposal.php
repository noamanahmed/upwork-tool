<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
