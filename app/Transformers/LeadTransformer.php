<?php

namespace App\Transformers;

use App\Enums\LeadStatusEnum;
use App\Models\Lead;

class LeadTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Lead;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'full_name' => $this->resource->full_name,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'website' => $this->resource->website,
            'company_id' => $this->resource->company_id,
            'company' => $this->resource->company,
            'job_designation' => $this->resource->job_designation,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'country' => $this->resource->country,
            'address' => $this->resource->address,
            'notes' => $this->resource->notes,
            'status' => $this->resource->status,
            'status_pretty' => LeadStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => LeadStatusEnum::getColorType($this->resource->status)
        ];
    }

}
