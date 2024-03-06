<?php

namespace App\Transformers;

use App\Models\Proxy;

class ProxyTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Proxy;
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
