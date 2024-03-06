<?php


namespace App\Services;

use App\Enums\CategoryStatusEnum;
use App\Repositories\CategoryRepository;
use App\Transformers\CategoryCollectionTransformer;
use App\Transformers\CategoryTransformer;

class CategoryService extends BaseService{

    public function __construct(){
        $this->repository = new CategoryRepository();
        $this->transformer = new CategoryTransformer();
        $this->collectionTransformer = new CategoryCollectionTransformer();
        $this->statusMapperEnum = CategoryStatusEnum::class;
    }

}

