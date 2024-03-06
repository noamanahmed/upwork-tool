<?php


namespace App\Services;

use App\Enums\JobSearchStatusEnum;
use App\Repositories\JobSearchRepository;
use App\Transformers\JobSearchCollectionTransformer;
use App\Transformers\JobSearchTransformer;

class JobSearchService extends BaseService{

    public function __construct(){
        $this->repository = new JobSearchRepository();
        $this->transformer = new JobSearchTransformer();
        $this->collectionTransformer = new JobSearchCollectionTransformer();
        $this->statusMapperEnum = JobSearchStatusEnum::class;
    }

}

