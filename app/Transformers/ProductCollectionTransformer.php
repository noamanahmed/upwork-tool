<?php

namespace App\Transformers;

class ProductCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new ProductTransformer();
    }

}
