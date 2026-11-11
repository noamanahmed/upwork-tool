<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AiProposalStatusEnum;
use App\Http\Requests\StoreUpWorkRequest;
use App\Http\Requests\UpdateUpWorkRequest;
use App\Models\AiJobProposal;
use App\Models\Job;
use App\Models\JobSearch;
use App\Models\RssJobSearches;
use App\Models\UpWork;
use App\Services\AiProposalService;
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

        $enabledProviders = config('services.ai.enabled_providers', []);
        $provider = $request->get('provider', $enabledProviders[0] ?? null);

        if (!$provider || !in_array($provider, $enabledProviders, true)) {
            return $this->upworkService->apiResponseWithAuthorizationFailedError([
                'message' => "Provider [{$provider}] is not enabled. Enabled: " . implode(', ', $enabledProviders),
            ]);
        }

        $existingProposal = AiJobProposal::where('job_id', $jobId)
            ->where('provider', $provider)
            ->first();

        if ($existingProposal) {
            if ($existingProposal->status === AiProposalStatusEnum::COMPLETED->value) {
                return $this->upworkService->successfullApiResponse([
                    'message' => 'The AI proposal is already generated.',
                    'proposal' => $existingProposal,
                ]);
            }

            if (in_array($existingProposal->status, AiProposalStatusEnum::inFlight(), true)) {
                return $this->upworkService->successfullApiResponse([
                    'message' => 'The AI proposal is currently being generated.',
                    'proposal' => $existingProposal,
                ]);
            }
        }

        $dispatched = app(AiProposalService::class)->dispatchForJob($job, $provider);

        if ($dispatched === 0) {
            return $this->upworkService->errorfullApiResponse([
                'message' => "Missing model or conversation_id config for provider [{$provider}].",
            ]);
        }

        return $this->upworkService->successfullApiResponse([
            'message' => 'The AI proposal is currently being generated.',
            'proposal' => AiJobProposal::where('job_id', $jobId)->where('provider', $provider)->first(),
        ]);
    }

    public function getAiJobProposal($jobId, $aiJobProposalId)
    {
        $proposal = \App\Models\AiJobProposal::where('job_id', $jobId)
            ->findOrFail($aiJobProposalId);

        return $this->upworkService->successfullApiResponse([
            'proposal' => $proposal
        ]);
    }

    public function regenerateProposal($jobId, Request $request)
    {
        $job = Job::findOrFail($jobId);

        $enabledProviders = config('services.ai.enabled_providers', []);
        $provider = $request->get('provider', $enabledProviders[0] ?? null);

        if (!$provider || !in_array($provider, $enabledProviders, true)) {
            return $this->upworkService->apiResponseWithAuthorizationFailedError([
                'message' => "Provider [{$provider}] is not enabled. Enabled: " . implode(', ', $enabledProviders),
            ]);
        }

        AiJobProposal::where('job_id', $jobId)
            ->where('provider', $provider)
            ->delete();

        $dispatched = app(AiProposalService::class)->dispatchForJob($job, $provider);

        if ($dispatched === 0) {
            return $this->upworkService->errorfullApiResponse([
                'message' => "Missing model or conversation_id config for provider [{$provider}].",
            ]);
        }

        return $this->upworkService->successfullApiResponse([
            'message' => 'Proposal regeneration initiated.',
            'proposal' => AiJobProposal::where('job_id', $jobId)->where('provider', $provider)->first(),
        ]);
    }
}
