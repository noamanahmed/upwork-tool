<?php

namespace App\Transformers;

use App\Models\Permission;

class PermissionTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Permission;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
        ];
    }

}
