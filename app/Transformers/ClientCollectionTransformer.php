<?php

namespace App\Transformers;

class ClientCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new ClientTransformer();
    }

}
