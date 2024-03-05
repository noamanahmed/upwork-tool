<?php

namespace App\Transformers;

use App\Models\Setting;

class SettingTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Setting;
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
