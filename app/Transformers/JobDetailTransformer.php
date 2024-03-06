<?php

namespace App\Transformers;

use App\Models\JobDetail;

class JobDetailTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new JobDetail;
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
