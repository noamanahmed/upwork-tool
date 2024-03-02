<?php

namespace App\Transformers;

use App\Enums\CompanyStatusEnum;
use App\Enums\CompanyTypeEnum;
use App\Models\Company;

class CompanyTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Company;
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
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'type' => $this->resource->type,
            'image' => $this->resource->image ? $this->resource->image->signedUrl : null,
            'type_pretty' => CompanyTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => CompanyTypeEnum::getColorType($this->resource->type),
            'status' => $this->resource->status,
            'status_pretty' => CompanyStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => CompanyStatusEnum::getColorType($this->resource->status),
            'locations' => $this->getLocations(),
        ];
    }

    public function getLocations(){
        $locations = [];
        foreach($this->resource->locations as $location)
        {
            $locations[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }
        return $locations;
    }
}
