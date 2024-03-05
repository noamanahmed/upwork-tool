<?php


namespace App\Services;

use App\Enums\SettingStatusEnum;
use App\Repositories\SettingRepository;
use App\Transformers\SettingCollectionTransformer;
use App\Transformers\SettingTransformer;

class SettingService extends BaseService{

    public function __construct(){
        $this->repository = new SettingRepository();
        $this->transformer = new SettingTransformer();
        $this->collectionTransformer = new SettingCollectionTransformer();
        $this->statusMapperEnum = SettingStatusEnum::class;
    }

}

