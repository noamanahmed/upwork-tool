<?php

namespace App\Transformers;

class EmployeeCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new EmployeeTransformer();
    }

}
