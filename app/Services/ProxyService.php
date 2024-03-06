<?php


namespace App\Services;

use App\Enums\ProxyStatusEnum;
use App\Repositories\ProxyRepository;
use App\Transformers\ProxyCollectionTransformer;
use App\Transformers\ProxyTransformer;

class ProxyService extends BaseService{

    public function __construct(){
        $this->repository = new ProxyRepository();
        $this->transformer = new ProxyTransformer();
        $this->collectionTransformer = new ProxyCollectionTransformer();
        $this->statusMapperEnum = ProxyStatusEnum::class;
    }

}

