<?php

namespace App\Transformers;

use App\Models\JobAttachment;

class JobAttachmentTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new JobAttachment;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
        ];
    }

}
