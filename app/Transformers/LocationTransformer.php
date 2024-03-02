<?php

namespace App\Transformers;

use App\Enums\LocationStatusEnum;
use App\Enums\LocationTypeEnum;
use App\Models\Location;

class LocationTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Location;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'address' => $this->resource->address,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'zip' => $this->resource->zip,
            'country' => $this->resource->country,
            'type' => $this->resource->type,
            'type_pretty' => LocationTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => LocationTypeEnum::getColorType($this->resource->type),
            'status' => $this->resource->status,
            'status_pretty' => LocationStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => LocationStatusEnum::getColorType($this->resource->status),
        ];
    }

}
