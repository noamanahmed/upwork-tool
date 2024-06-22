<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Models\Job;
use App\Models\JobSearchPivot;
use App\Models\RssJobs;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class JobService extends BaseService
{

    public function __construct()
    {
        $this->repository = new JobRepository();
        $this->transformer = new JobTransformer();
        $this->collectionTransformer = new JobCollectionTransformer();
        $this->statusMapperEnum = JobStatusEnum::class;
    }

    public function insertJobsFromApiResponse($data)
    {
        $locks = [];
        $jobs = [];
        $jobIds = [];
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $jobIds[] = $node['id'];
        }

        $alreadyExistingJobs = Job::whereIn('upwork_id',$jobIds)->get()->keyBy('upwork_id')->toArray();
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $node = Arr::dot($node);
            $lock = Cache::lock('job_service_insert_job_' . $node['id'], 10);
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$node['id']] ?? null;
                if (!is_null($job)) {
                    $lock->release();
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

                $jobs[] = [
                    'upwork_id' => $node['id'],
                    'title' => $node['job.content.title'],
                    'ciphertext' => $node['ciphertext'],
                    'description' => $node['job.content.description'],
                    'client_total_hires' => $node['client.totalHires'] ?? 0,
                    'client_total_posted_jobs' => $node['client.totalPostedJobs'] ?? 0,
                    'client_total_reviews' => $node['client.totalReviews'] ?? 0,
                    'client_total_feedback' => $node['client.totalFeedback'] ?? 0,
                    'client_total_spent' => (double)$node['client.totalSpent.rawValue'] ?? 0,
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
                $locks[] = $lock;
            }
        }

        if(!empty($jobs))
        {
            Job::insert($jobs);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
    public function attachJobsToJobSearchesFromApiResponse($data,$jobSearch)
    {
        if(empty($data)) return;
        $locks = [];
        $jobPivots = [];
        $jobIds = [];
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $jobIds[] = $node['id'];
        }
        $alreadyExistingJobs = Job::whereIn('upwork_id',$jobIds)->get()->keyBy('upwork_id');
        $alreadyExistingJobSearchPivot = JobSearchPivot::where('job_search_id', $jobSearch->id)->whereIn('job_id',$alreadyExistingJobs->pluck('id')->toArray())->get()->keyBy('job_id');

        foreach ($data as $jobData)
        {
            if(empty($jobData)) continue;
            $node = $jobData['node'];
            $job = $alreadyExistingJobs[$node['id']] ?? null;
            if (empty($job)) continue;
            $lock = Cache::lock('job_service_insert_job_' . $job->id . '_job_searches_'.$jobSearch->id, 30);
            if ($lock->get()) {
                $jobSearchPivot = $alreadyExistingJobSearchPivot[$job->id] ?? null;
                if(!is_null($jobSearchPivot)){
                    $lock->release();
                    continue;
                };
                $jobPivots[] = [
                    'job_search_id' => $jobSearch->id,
                    'job_id' => $job->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if(!empty($jobPivots))
        {
            JobSearchPivot::insert($jobPivots);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
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
    public function insertJobsRelevantToProposals($proposals,$jobContents)
    {
        $locks = [];
        $jobs = [];
        $jobIds = [];
        $cipherTextArray = [];
        foreach ($proposals as $proposalData) {
            if (empty($proposalData)) continue;
            if($proposalData['node']['marketplaceJobPosting']['id'] ?? false)
            {
                $jobIds[] = $proposalData['node']['marketplaceJobPosting']['id'];
            }

        }
        foreach($jobContents  as $jobContent)
        {
            $cipherTextArray[$jobContent['id']] = $jobContent['ciphertext'];
        }

        $alreadyExistingJobs = Job::whereIn('upwork_id',$jobIds)->get()->keyBy('upwork_id')->toArray();
        foreach ($proposals as $proposalData) {
            $jobData = $proposalData['node']['marketplaceJobPosting'] ?? false;
            if (empty($jobData)) continue;
            $cipherText = $cipherTextArray[$jobData['id']] ?? false;
            $node = $jobData;
            $node = Arr::dot($node);
            $lock = Cache::lock('job_service_insert_job_' . $node['id'], 10);
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$node['id']] ?? null;
                if (!is_null($job)) {
                    $lock->release();
                    continue;
                }
                $isHourlyJob = ($node['contractTerms.contractType'] ?? null) === 'HOURLY' ? true : false;
                $minimumBudget = 0;
                $maximumBudget = 0;
                if($isHourlyJob)
                {
                    $minimumBudget = $node['contractTerms.hourlyContractTerms.hourlyBudgetMin'] ?? 0;
                    $maximumBudget = $node['contractTerms.hourlyContractTerms.hourlyBudgetMax'] ?? 0;
                }else{
                    $minimumBudget = $node['amount.displayValue'] ?? 0;
                    $maximumBudget = $node['amount.displayValue'] ?? 0;
                }
                $location =  ($node['client.location.city'] ?? 'N/A') . ' '. ($node['client.location.state'] ?? 'N/A') . ' '. ($node['client.location.country'] ?? 'N/A');
                $jobs[] = [
                    'upwork_id' => $node['id'],
                    'title' => $node['content.title'],
                    'ciphertext' => $cipherText,
                    'description' => $node['content.description'],
                    'client_total_hires' => $node['client.totalHires'] ?? 0,
                    'client_total_posted_jobs' => $node['client.totalPostedJobs'] ?? 0,
                    'client_total_reviews' => $node['client.totalReviews'] ?? 0,
                    'client_total_feedback' => $node['client.totalFeedback'] ?? 0,
                    'client_total_spent' => (double) $node['client.totalSpent.rawValue'] ?? 0,
                    'client_total_spent_currency' => $node['client.totalSpent.currency'] ?? 'USD',
                    'location' => $location,
                    'budget_minimum' => $minimumBudget,
                    'budget_maximum' => $maximumBudget,
                    'is_hourly' =>  $isHourlyJob,
                    'is_payment_verified' => ( $node['client.verificationStatus'] ?? false) === 'VERIFIED',
                    'city' => $node['client.location.city'] ?? 'N/A',
                    'state' => $node['client.location.state'] ?? 'N/A',
                    'country' => $node['client.location.country'] ?? 'N/A',
                    'json' => json_encode($jobData),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $locks[] = $lock;
            }
        }
        dd($jobs);
        if(!empty($jobs))
        {
            Job::insert($jobs);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
}
