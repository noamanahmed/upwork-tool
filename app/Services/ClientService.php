<?php


namespace App\Services;

use App\Enums\ClientStatusEnum;
use App\Repositories\ClientRepository;
use App\Transformers\ClientCollectionTransformer;
use App\Transformers\ClientTransformer;

class ClientService extends BaseService{

    public function __construct(){
        $this->repository = new ClientRepository();
        $this->transformer = new ClientTransformer();
        $this->collectionTransformer = new ClientCollectionTransformer();
        $this->statusMapperEnum = ClientStatusEnum::class;
    }

}

