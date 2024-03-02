<?php

namespace App\Transformers;

use App\Enums\EmployerStatusEnum;
use App\Models\Employer;

class EmployerTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Employer;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'status' =>  $this->resource->status,
            'status_pretty' => EmployerStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => EmployerStatusEnum::getColorType($this->resource->status),
        ];
    }

}
