<?php

namespace App\Transformers;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;

class ProfileTransformer extends BaseTransformer
{
    public function __construct()
    {
        $this->resource = new User();
        parent::__construct();
    }

    public function __invoke()
    {
        return $this->toArray();
    }

    public function toArray(){
        return [
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'address' => $this->resource->address,
            'city' => $this->resource->city,
            'state' => $this->resource->state,
            'country' => $this->resource->country,
            'status' => $this->resource->status,
            'status_pretty' => UserStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'type' => $this->resource->type,
            'type_pretty' => UserTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'job_type' => is_null($this->resource->job_type) ? null : $this->resource->job_type,
            'working_hours' => $this->resource->working_hours,
            'is_email_verified' => $this->resource->hasVerifiedEmail()
        ];
    }
    public function toJson(){
        return json_encode($this->toArray());
    }

}
