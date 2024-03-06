<?php

namespace App\Transformers;

use App\Models\UpworkAccount;

class UpworkAccountTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new UpworkAccount;
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
