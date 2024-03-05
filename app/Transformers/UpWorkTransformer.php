<?php

namespace App\Transformers;

use App\Models\UpWork;

class UpWorkTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new UpWork;
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
