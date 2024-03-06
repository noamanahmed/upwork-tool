<?php


namespace App\Services;

use App\Enums\JobAttachmentStatusEnum;
use App\Repositories\JobAttachmentRepository;
use App\Transformers\JobAttachmentCollectionTransformer;
use App\Transformers\JobAttachmentTransformer;

class JobAttachmentService extends BaseService{

    public function __construct(){
        $this->repository = new JobAttachmentRepository();
        $this->transformer = new JobAttachmentTransformer();
        $this->collectionTransformer = new JobAttachmentCollectionTransformer();
        $this->statusMapperEnum = JobAttachmentStatusEnum::class;
    }

}

