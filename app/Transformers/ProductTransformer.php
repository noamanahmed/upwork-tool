<?php

namespace App\Transformers;

use App\Enums\ProductStatusEnum;
use App\Enums\ProductTypeEnum;
use App\Models\Product;

class ProductTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Product;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'product_number' => $this->resource->product_number,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'regular_price' => $this->resource->regular_price,
            'sale_price' => $this->resource->sale_price,
            'price' => $this->resource->getPrice(),
            'discount' => $this->resource->getDiscount(),
            'discount_percentage' => $this->resource->getDiscountPercentage(),
            'stock' => $this->resource->stock,
            'sku' => $this->resource->sku,
            'status' => $this->resource->status,
            'image' => $this->resource->image ? $this->resource->image->signedUrl : null,
            'status_pretty' => ProductStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => ProductStatusEnum::getColorType($this->resource->status),
            'type' => $this->resource->type,
            'type_pretty' => ProductTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => ProductTypeEnum::getColorType($this->resource->type),
            'categories' => $this->getCategories(),
        ];
    }

    public function getCategories(){
        $categories = [];
        foreach($this->resource->categories as $location)
        {
            $categories[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }
        return $categories;
    }

}
