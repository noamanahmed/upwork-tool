<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAiProposals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:generate-ai-proposals {hours=1 : The number of hours to look back for jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch jobs to generate AI proposals for recently posted jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->argument('hours');
        $provider = config('services.ai.provider');
        $modelName = config("services.ai.{$provider}.model", 'gpt-4');
        $conversationId = config("services.ai.{$provider}.conversation_id", 'openai');

        if (empty($provider) || empty($modelName) || empty($conversationId)) {
            $this->error('Please provide a valid provider, model, and conversation id in your config.');
            return;
        }

        // Get jobs from the last X hours where we do NOT already have a proposal for this specific provider.
        // Assuming AiJobProposal has `job_id` correctly bound. The command `Job::whereDoesntHave` would need the relationship defined on Job.
        // Since we aren't sure if the Job model has the relationship, we'll do an array diff.

        $jobs = \App\Models\Job::where('created_at', '>=', now()->subHours($hours))->with('aiProposals')->get();
        $dispatchedCount = 0;
        foreach ($jobs as $job) {
            $existing = $job->aiProposals->where('provider', $provider)->first();
            
            if ($existing)
                continue;

            $aiJobProposal = \App\Models\AiJobProposal::create([
                'job_id' => $job->id,
                'status' => 'generating',
                'provider' => $provider,
                'model' => $modelName,
                'conversation_id' => $conversationId,
                'proposal' => 'N/A',
            ]);

            $aiJobProposal = $aiJobProposal->fresh(); // Refresh to get any default values or changes from the model
            $aiJobProposal->prompt = $aiJobProposal->getPromptText();
            $aiJobProposal->instructions = $aiJobProposal->getModelInstructions();
            $aiJobProposal->save();

            dispatch(new \App\Jobs\GenerateAiJobProposal($aiJobProposal));
            $this->info("Dispatched proposal generation for Upwork job #{$job->id}.");
            $dispatchedCount++;
        }

        $this->info("Done! Dispatched {$dispatchedCount} proposals.");
    }
}
