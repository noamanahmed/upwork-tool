<?php

namespace App\Transformers;

class ContractCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new ContractTransformer();
    }

}
