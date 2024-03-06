<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;

class JobService extends BaseService{

    public function __construct(){
        $this->repository = new JobRepository();
        $this->transformer = new JobTransformer();
        $this->collectionTransformer = new JobCollectionTransformer();
        $this->statusMapperEnum = JobStatusEnum::class;
    }

}

