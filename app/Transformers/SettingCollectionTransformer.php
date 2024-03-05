<?php

namespace App\Transformers;

class SettingCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new SettingTransformer();
    }

}
