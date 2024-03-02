<?php

namespace App\Transformers;

use App\Enums\CustomerStatusEnum;
use App\Models\Customer;

class CustomerTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Customer;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'address' => $this->resource->address,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'country' => $this->resource->country,
            'status' => $this->resource->status,
            'status_pretty' => CustomerStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => CustomerStatusEnum::getColorType($this->resource->status),
            'notes' => $this->resource->notes,
        ];
    }

}
