<?php

namespace App\Transformers;

use App\Enums\EmployeeStatusEnum;
use App\Models\User;

class EmployeeTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new User;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'employer_id' => $this->resource->employer_id,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'description' => $this->resource->description,
            'status' =>  $this->resource->status,
            'status_pretty' => EmployeeStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => EmployeeStatusEnum::getColorType($this->resource->status),
            'skills' => $this->getSkills(),
        ];
    }

    public function getSkills(){
        $skills = [];
        foreach($this->resource->skills as $location)
        {
            $skills[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }
        return $skills;
    }

}
