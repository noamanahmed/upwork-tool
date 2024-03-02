<?php

namespace App\Services;

use App\Repositories\TranslationRepository;
use App\Transformers\TranslationCollectionTransformer;
use App\Transformers\TranslationTransformer;

class TranslationService extends BaseService{

    public function __construct(){
        $this->repository = new TranslationRepository();
        $this->transformer = new TranslationTransformer();
        $this->collectionTransformer = new TranslationCollectionTransformer();
    }
}
