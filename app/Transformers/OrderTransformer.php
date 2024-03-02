<?php

namespace App\Transformers;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\Order;

class OrderTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Order;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'customer_id' => $this->resource->customer_id,
            'subtotal' => $this->resource->subtotal,
            'tax' => $this->resource->tax,
            'shipping' => $this->resource->shipping,
            'discount' => $this->resource->discount,
            'total' => $this->resource->total,
            'notes' => $this->resource->notes,
            'type' => $this->resource->type,
            'type_pretty' => OrderTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => OrderTypeEnum::getColorType($this->resource->type),
            'status' => $this->resource->status,
            'status_pretty' => OrderStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => OrderStatusEnum::getColorType($this->resource->status),
        ];
    }

}
