<?php

namespace App\Transformers;

use App\Models\Region;

class RegionTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Region;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
        ];
    }

}
