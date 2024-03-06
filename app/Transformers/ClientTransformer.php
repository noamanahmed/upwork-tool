<?php

namespace App\Transformers;

use App\Models\Client;

class ClientTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Client;
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
