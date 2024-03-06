<?php

namespace App\Transformers;

class UpworkAccountCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new UpworkAccountTransformer();
    }

}
