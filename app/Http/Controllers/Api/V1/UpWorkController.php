<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StoreUpWorkRequest;
use App\Http\Requests\UpdateUpWorkRequest;
use App\Models\Job;
use App\Models\JobSearch;
use App\Models\RssJobSearches;
use App\Models\UpWork;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Http\Request;

class UpWorkController extends BaseController
{
    public function __construct(
        private UpWorkService $upworkService,
        private JobService $jobService
    ) {
    }

    public function init(Request $request)
    {
        return $this->upworkService->init();
    }
    public function code(Request $request)
    {
        return $this->upworkService->code();
    }
    public function jobs(JobSearch $jobSearch, Request $request)
    {
        return $this->upworkService->jobs($jobSearch->toArray());
    }
    public function rssJobs(RssJobSearches $jobSearch, Request $request)
    {
        return $this->upworkService->rssJobs($jobSearch);
    }

    public function job($jobId, Request $request)
    {
        return $this->jobService->get($jobId);
    }
    public function jobSlackMessage($jobId, Request $request)
    {
        $job = Job::findOrfail($jobId);
        return $job->slack_notification_message;
    }

    public function categories(Request $request)
    {
        return $this->upworkService->categories();
    }

    public function skills(Request $request)
    {
        return $this->upworkService->skills();
    }
    public function timezones(Request $request)
    {
        return $this->upworkService->timezones();
    }
    public function languages(Request $request)
    {
        return $this->upworkService->languages();
    }
    public function countries(Request $request)
    {
        return $this->upworkService->countries();
    }
    public function regions(Request $request)
    {
        return $this->upworkService->regions();
    }
    public function analytics(Request $request)
    {
        return $this->upworkService->analytics();
    }
    public function proposals(Request $request)
    {
        return $this->upworkService->proposals();
    }

    public function generateProposal($jobId, Request $request)
    {
        $job = Job::findOrFail($jobId);

        $provider = $request->get('provider', config('services.ai.provider'));
        $modelName = config("services.ai.{$provider}.model", 'gpt-4');
        $conversationId = config("services.ai.{$provider}.conversation_id", 'openai');

        if (empty($provider) || empty($modelName) || empty($conversationId)) {
            return $this->upworkService->errorfullApiResponse([
                'message' => 'Please provide a valid provider, model, and conversation id.',
            ]);
        }

        // Check if an AI proposal already exists for this job
        $existingProposal = \App\Models\AiJobProposal::where('job_id', $jobId)
            ->where('provider', $provider)
            ->first();

        if ($existingProposal) {
            if ($existingProposal->status === 'completed') {
                return $this->upworkService->successfullApiResponse([
                    'message' => 'The AI proposal is already generated.',
                    'proposal' => $existingProposal
                ]);
            }

            return $this->upworkService->successfullApiResponse([
                'message' => 'The AI proposal is currently being generated.',
                'proposal' => $existingProposal
            ]);
        }

        // Not generated yet, create and dispatch
        $aiJobProposal = \App\Models\AiJobProposal::create([
            'job_id' => $jobId,
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

        return $this->upworkService->successfullApiResponse([
            'message' => 'The AI proposal is currently being generated.',
            'proposal' => $aiJobProposal
        ]);
    }

    public function getAiJobProposal($jobId, $aiJobProposalId)
    {
        $proposal = \App\Models\AiJobProposal::where('job_id', $jobId)
            ->findOrFail($aiJobProposalId);

        return $this->successfullApiResponse([
            'proposal' => $proposal
        ]);
    }
}
