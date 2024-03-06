<?php


namespace App\Services;

use App\Enums\JobDetailStatusEnum;
use App\Repositories\JobDetailRepository;
use App\Transformers\JobDetailCollectionTransformer;
use App\Transformers\JobDetailTransformer;

class JobDetailService extends BaseService{

    public function __construct(){
        $this->repository = new JobDetailRepository();
        $this->transformer = new JobDetailTransformer();
        $this->collectionTransformer = new JobDetailCollectionTransformer();
        $this->statusMapperEnum = JobDetailStatusEnum::class;
    }

}

