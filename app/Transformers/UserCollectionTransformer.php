<?php

namespace App\Transformers;

class UserCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new UserTransformer();
    }

}
