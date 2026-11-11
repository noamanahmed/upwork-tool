<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Models\Job;
use App\Models\JobSearchPivot;
use App\Models\RssJobs;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JobService extends BaseService
{

    public function __construct()
    {
        $this->repository = new JobRepository();
        $this->transformer = new JobTransformer();
        $this->collectionTransformer = new JobCollectionTransformer();
        $this->statusMapperEnum = JobStatusEnum::class;
    }

    /**
     * Insert jobs that are not in the database yet.
     *
     * @return Collection<int, Job> the newly inserted jobs, keyed by upwork_id
     */
    public function insertJobsFromApiResponse($data): Collection
    {
        $upworkIds = collect($data)->filter()->pluck('node.id')->unique()->values();

        if ($upworkIds->isEmpty()) {
            return new Collection();
        }

        $existingUpworkIds = Job::whereIn('upwork_id', $upworkIds)
            ->pluck('id', 'upwork_id');

        $rows = [];
        $newUpworkIds = [];

        foreach ($data as $jobData) {
            if (empty($jobData)) continue;

            $node = Arr::dot($jobData['node']);
            $upworkId = $node['id'];

            if ($existingUpworkIds->has($upworkId) || in_array($upworkId, $newUpworkIds)) {
                continue;
            }

            $isHourlyJob = ($node['job.contractTerms.contractType'] ?? null) === 'HOURLY' ? true : false;
            $minimumBudget = 0;
            $maximumBudget = 0;
            if($isHourlyJob)
            {
                $minimumBudget = $node['job.contractTerms.hourlyContractTerms.hourlyBudgetMin'] ?? 0;
                $maximumBudget = $node['job.contractTerms.hourlyContractTerms.hourlyBudgetMax'] ?? 0;
            }else{
                $minimumBudget = $node['amount.displayValue'] ?? 0;
                $maximumBudget = $node['amount.displayValue'] ?? 0;
            }
            $location =  ($node['client.location.city'] ?? 'N/A') . ' '. ($node['client.location.state'] ?? 'N/A') . ' '. ($node['client.location.country'] ?? 'N/A');

            $rows[] = [
                'upwork_id' => $upworkId,
                'title' => $node['job.content.title'],
                'ciphertext' => $node['ciphertext'],
                'description' => $node['job.content.description'],
                'client_total_hires' => $node['client.totalHires'] ?? 0,
                'client_total_posted_jobs' => $node['client.totalPostedJobs'] ?? 0,
                'client_total_reviews' => $node['client.totalReviews'] ?? 0,
                'client_total_feedback' => $node['client.totalFeedback'] ?? 0,
                'client_total_spent' => (double)($node['client.totalSpent.rawValue'] ?? 0),
                'client_total_spent_currency' => $node['client.totalSpent.currency'] ?? 'USD',
                'location' => $location,
                'budget_minimum' => $minimumBudget,
                'budget_maximum' => $maximumBudget,
                'is_hourly' =>  $isHourlyJob,
                'is_payment_verified' => ( $node['client.verificationStatus'] ?? false) === 'VERIFIED',
                'city' => $node['client.location.city'] ?? 'N/A',
                'state' => $node['client.location.state'] ?? 'N/A',
                'country' => $node['client.location.country'] ?? 'N/A',
                'posted_at' => Carbon::parse($node['publishedDateTime'] ?? '1970-01-01 00:00:01'),
                'is_activity_job_dispatched' => 0,
                'json' => json_encode($jobData),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $newUpworkIds[] = $upworkId;
        }

        if (empty($rows)) {
            return new Collection();
        }

        $maxId = (int) (Job::max('id') ?? 0);

        $inserted = Job::insertOrIgnore($rows);

        if ($inserted !== count($rows)) {
            Log::warning('Job bulk insert skipped rows', [
                'attempted' => count($rows),
                'inserted' => $inserted,
                'upwork_ids' => $newUpworkIds,
            ]);
        }

        return Job::where('id', '>', $maxId)
            ->whereIn('upwork_id', $newUpworkIds)
            ->get()
            ->keyBy('upwork_id');
    }

    /**
     * Attach jobs to the search and return only the pivot rows that are new.
     *
     * @return array<int, array{job_id: int, job_search_id: int}>
     */
    public function attachJobsToJobSearchesFromApiResponse($data, $jobSearch): array
    {
        if (empty($data)) {
            return [];
        }

        $upworkIds = collect($data)->filter()->pluck('node.id')->unique()->values();

        if ($upworkIds->isEmpty()) {
            return [];
        }

        $jobsByUpworkId = Job::whereIn('upwork_id', $upworkIds)->pluck('id', 'upwork_id');

        if ($jobsByUpworkId->isEmpty()) {
            return [];
        }

        $existingJobIds = JobSearchPivot::where('job_search_id', $jobSearch->id)
            ->whereIn('job_id', $jobsByUpworkId->values())
            ->pluck('job_id')
            ->all();

        $rows = [];

        foreach ($jobsByUpworkId as $jobId) {
            if (in_array($jobId, $existingJobIds)) {
                continue;
            }

            $rows[] = [
                'job_search_id' => $jobSearch->id,
                'job_id' => $jobId,
                'is_slack_webhook_sent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (empty($rows)) {
            return [];
        }

        JobSearchPivot::insertOrIgnore($rows);

        return JobSearchPivot::where('job_search_id', $jobSearch->id)
            ->whereIn('job_id', array_column($rows, 'job_id'))
            ->whereNotIn('job_id', $existingJobIds)
            ->get(['job_id', 'job_search_id'])
            ->map(fn ($pivot) => [
                'job_id' => (int) $pivot->job_id,
                'job_search_id' => (int) $pivot->job_search_id,
            ])
            ->all();
    }

    public function insertRssJobs($data,$rssJobSearch)
    {
        if(empty($data)) return;
        $alreadyExistingJobs = RssJobs::whereIn('ciphertext',collect($data)->pluck('ciphertext')->toArray())->get()->keyBy('ciphertext')->toArray();
        $locks = [];
        $rssJobs = [];
        foreach ($data as $job)
        {
            if(empty($job)) continue;
            $cipherText = $job['ciphertext'];
            $lock = Cache::lock('job_service_insert_rss_job_' . $cipherText.'_rss_job_searches_'.$rssJobSearch->id, 10);
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$cipherText] ?? null ;
                if (!is_null($job)) {
                    $lock->release();
                    continue;
                }
                $rssJobs[] = [
                    'ciphertext' => $cipherText,
                    'rss_job_search_id' => $rssJobSearch->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $locks[] = $lock;
            }
        }
        if(!empty($rssJobs))
        {
            RssJobs::insert($rssJobs);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
}
