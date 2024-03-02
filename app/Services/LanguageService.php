<?php


namespace App\Services;

use App\Enums\LanguageStatusEnum;
use App\Repositories\LanguageRepository;
use App\Transformers\LanguageCollectionTransformer;
use App\Transformers\LanguageTransformer;

class LanguageService extends BaseService{

    public function __construct(){
        $this->repository = new LanguageRepository();
        $this->transformer = new LanguageTransformer();
        $this->collectionTransformer = new LanguageCollectionTransformer();
        $this->statusMapperEnum = LanguageStatusEnum::class;
    }

}

