<?php


namespace App\Services;

use App\Enums\SkillStatusEnum;
use App\Repositories\SkillRepository;
use App\Transformers\SkillCollectionTransformer;
use App\Transformers\SkillTransformer;

class SkillService extends BaseService{

    public function __construct(){
        $this->repository = new SkillRepository();
        $this->transformer = new SkillTransformer();
        $this->collectionTransformer = new SkillCollectionTransformer();
        $this->statusMapperEnum = SkillStatusEnum::class;
    }

}

