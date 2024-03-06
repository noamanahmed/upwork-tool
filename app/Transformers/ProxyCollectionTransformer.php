<?php

namespace App\Transformers;

class ProxyCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new ProxyTransformer();
    }

}
