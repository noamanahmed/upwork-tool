<?php

namespace App\Transformers;

use App\Models\JobSearch;

class JobSearchTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new JobSearch;
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
