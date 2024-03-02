<?php

namespace App\Transformers;

use App\Enums\SkillStatusEnum;
use App\Models\Skill;

class SkillTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Skill;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description
        ];
    }

}
