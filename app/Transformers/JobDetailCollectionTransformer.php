<?php

namespace App\Transformers;

class JobDetailCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new JobDetailTransformer();
    }

}
