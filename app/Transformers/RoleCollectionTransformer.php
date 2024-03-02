<?php

namespace App\Transformers;

class RoleCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new RoleTransformer();
    }

}
