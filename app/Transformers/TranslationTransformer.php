<?php

namespace App\Transformers;

use App\Enums\TranslationStatus;
use App\Models\Translation;

class TranslationTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Translation;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'language_id' => $this->resource->language->id,
            'language_code' => $this->resource->language->code,
            'key' => $this->resource->key,
            'value' => $this->resource->value,
        ];
    }

}
