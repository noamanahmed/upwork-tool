<?php

namespace App\Transformers;

class CompanyCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new CompanyTransformer();
    }

}
