<?php

namespace App\Transformers;

class EmployerCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new EmployerTransformer();
    }

}
