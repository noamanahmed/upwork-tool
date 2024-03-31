<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Models\Job;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JobService extends BaseService{

    public function __construct(){
        $this->repository = new JobRepository();
        $this->transformer = new JobTransformer();
        $this->collectionTransformer = new JobCollectionTransformer();
        $this->statusMapperEnum = JobStatusEnum::class;
    }

    public function insertJobsFromApiResponse($data)
    {
        foreach($data as $jobData)
        {

            $node = $jobData['node'];
            $node = Arr::dot($node);
            DB::beginTransaction();
            $job = Job::where('upwork_id',$node['id'])->first();
            if(!is_null($job))
            {
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
        }
    }
}

