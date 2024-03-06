<?php

namespace App\Transformers;

use App\Models\Job;

class JobTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Job;
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
