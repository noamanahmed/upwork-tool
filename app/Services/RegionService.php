<?php


namespace App\Services;

use App\Enums\RegionStatusEnum;
use App\Repositories\RegionRepository;
use App\Transformers\RegionCollectionTransformer;
use App\Transformers\RegionTransformer;

class RegionService extends BaseService{

    public function __construct(){
        $this->repository = new RegionRepository();
        $this->transformer = new RegionTransformer();
        $this->collectionTransformer = new RegionCollectionTransformer();
        $this->statusMapperEnum = RegionStatusEnum::class;
    }

}

