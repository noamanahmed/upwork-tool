<?php

namespace App\Transformers;

use App\Models\UserSetting;

class UserSettingTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new UserSetting;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'id' => $this->resource->user_id,
            'timezone' => $this->resource->timezone,
            'language' => $this->resource->language
        ];
    }

}
