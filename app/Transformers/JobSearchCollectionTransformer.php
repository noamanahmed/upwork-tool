<?php

namespace App\Transformers;

class JobSearchCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new JobSearchTransformer();
    }

}
