<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Models\Job;
use App\Models\JobSearchPivot;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Support\Arr;
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
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $node = Arr::dot($node);
            $lock = Cache::lock('job_service_insert_job_' . $node['id'], 10);
            if ($lock->get()) {
                DB::beginTransaction();
                $job = Job::where('upwork_id', $node['id'])->first();
                if (!is_null($job)) {
                    continue;
                }
                $job = new Job();
                $job->upwork_id = $node['id'];
                // $job->client_id = $node['job.ownership.team.id'];
                $job->title = $node['job.content.title'];
                $job->ciphertext = $node['ciphertext'];
                $job->description = $node['job.content.description'];
                $job->json = json_encode($jobData);
                $job->save();
                DB::commit();
                $lock->release();
            }
        }
    }
    public function attachJobsToJobSearchesFromApiResponse($data,$jobSearch)
    {
        foreach ($data as $jobData) {

            $node = $jobData['node'];
            $job = Job::where('upwork_id', $node['id'])->first();
            if (empty($job)) continue;
            $jobSearchPivot = JobSearchPivot::where('job_id', $job->id)->where('job_search_id', $jobSearch->id)->first();
            if(!is_null($jobSearchPivot)) continue;
            $lock = Cache::lock('job_service_insert_job_' . $node['id'].'_job_searches_'.$jobSearch->id, 30);
            if ($lock->get()) {
                DB::beginTransaction();
                $jobSearch->jobs()->attach($job);
                DB::commit();
                $lock->release();
            }
        }
    }
}
