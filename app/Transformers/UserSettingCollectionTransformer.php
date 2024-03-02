<?php

namespace App\Transformers;

class UserSettingCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new UserSettingTransformer();
    }

}
