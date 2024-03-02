<?php

namespace App\Transformers;

use App\Enums\TaskStatusEnum;
use App\Enums\TaskWorkTypeEnum;
use App\Models\Task;

class TaskTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Task;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'start_date' => $this->resource->start_date,
            'end_date' => $this->resource->end_date,
            'employer_id' => $this->resource->employer_id,
            'employee_id' => $this->resource->employee_id,
            'company_id' => $this->resource->company_id,
            'location_id' => $this->resource->location_id,
            'status' => $this->resource->status,
            'status_pretty' => TaskStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => TaskStatusEnum::getColorType($this->resource->status),
            'work_type' => $this->resource->work_type,
            'work_type_pretty' => TaskWorkTypeEnum::getPrettyKeyfromValue($this->resource->work_type),
            'work_type_color' => TaskWorkTypeEnum::getColorType($this->resource->work_type),
        ];
    }

}
