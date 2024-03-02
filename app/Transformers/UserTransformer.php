<?php

namespace App\Transformers;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\User;

class UserTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new User;
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
            'status_pretty' => UserStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => UserStatusEnum::getColorType($this->resource->status),
            'type' => $this->resource->type,
            'type_pretty' => UserTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => UserTypeEnum::getColorType($this->resource->type),
            'role' => $this->resource->getRole(),
            'role_id' => $this->resource->getRoleId(),
            'working_hours' => $this->resource->working_hours,
            'is_email_verified' => $this->resource->hasVerifiedEmail()
        ];
    }

}
