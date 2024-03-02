<?php

namespace App\Transformers;

class OrderCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new OrderTransformer();
    }

}
