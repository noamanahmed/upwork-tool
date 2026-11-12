<?php

namespace App\Jobs;

use App\Models\JobSearch as ModelsJobSearch;
use App\Services\AiProposalService;
use App\Services\CategoryService;
use App\Services\JobActivityService;
use App\Services\JobService;
use App\Services\UpWorkService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class JobSearch implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public $timeout = 120;

    /**
     * Prevent overlapping runs for the same search (replaces the old Cache::lock).
     */
    public $uniqueFor = 600;

    public function __construct(public int $jobSearchId)
    {
        $this->onQueue('scraping');
    }

    public function uniqueId(): string
    {
        return (string) $this->jobSearchId;
    }

    public function handle(): void
    {
        $jobSearch = ModelsJobSearch::find($this->jobSearchId);

        if (!$jobSearch) {
            return;
        }

        $jobs = app(UpWorkService::class)->jobs($jobSearch->toArray());

        if (empty($jobs)) {
            return;
        }

        [$newJobs, $newPivots] = DB::transaction(function () use ($jobs, $jobSearch) {
            $newJobs = app(JobService::class)->insertJobsFromApiResponse($jobs);
            $newPivots = app(JobService::class)->attachJobsToJobSearchesFromApiResponse($jobs, $jobSearch);

            app(CategoryService::class)->attachCategoriesToJobsFromApiResponse($jobs);
            app(JobActivityService::class)->insertActivitiesFromApiResponse($jobs);

            return [$newJobs, $newPivots];
        });

        $this->dispatchFollowUpWork($newJobs, $newPivots);
    }

    /**
     * AI generation and Slack delivery live on separate queues so they run
     * in parallel and never compete with scraping for workers.
     */
    protected function dispatchFollowUpWork($newJobs, array $newPivots): void
    {
        $aiProposals = app(AiProposalService::class);

        foreach ($newJobs as $job) {
            $aiProposals->dispatchForJob($job);
        }

        foreach ($newPivots as $pivot) {
            SendJobSlackNotification::dispatch($pivot['job_id'], $pivot['job_search_id']);
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('JobSearch job failed', [
            'job_search_id' => $this->jobSearchId,
            'error' => $exception->getMessage(),
        ]);
    }
}
