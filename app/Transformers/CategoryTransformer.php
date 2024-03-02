<?php

namespace App\Transformers;

use App\Enums\CategoryStatusEnum;
use App\Models\Category;

class CategoryTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Category;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'products_count' => $this->resource->products_count,
            'status' => $this->resource->status,
            'status_pretty' => CategoryStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => CategoryStatusEnum::getColorType($this->resource->status),
        ];
    }

}
