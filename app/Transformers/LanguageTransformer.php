<?php

namespace App\Transformers;

use App\Models\Language;

class LanguageTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Language;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'code' => $this->resource->code,
            'icon' => $this->resource->icon,
            'active' => $this->resource->active,
        ];
    }

}
