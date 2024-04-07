<?php

namespace App\Transformers;

use App\Models\Job;
use Arr;

class JobTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Job;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'skills' => $this->resource->skills,
            'categories' => $this->resource->categories,
            'slack_notification_message' => $this->resource->slack_notification_message,
            'raw_json' => Arr::dot(json_decode($this->resource->json,true)),
        ];
    }

}
