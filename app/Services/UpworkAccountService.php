<?php


namespace App\Services;

use App\Enums\UpworkAccountStatusEnum;
use App\Repositories\UpworkAccountRepository;
use App\Transformers\UpworkAccountCollectionTransformer;
use App\Transformers\UpworkAccountTransformer;

class UpworkAccountService extends BaseService{

    public function __construct(){
        $this->repository = new UpworkAccountRepository();
        $this->transformer = new UpworkAccountTransformer();
        $this->collectionTransformer = new UpworkAccountCollectionTransformer();
        $this->statusMapperEnum = UpworkAccountStatusEnum::class;
    }

}

