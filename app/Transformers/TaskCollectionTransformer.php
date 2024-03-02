<?php

namespace App\Transformers;

class TaskCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new TaskTransformer();
    }

}
