<?php

namespace App\Transformers;

use App\Enums\QuotationStatusEnum;
use App\Models\Quotation;

class QuotationTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Quotation;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'lead_id' => $this->resource->lead_id,
            'quotation_number' => $this->resource->quotation_number,
            'date_created' => $this->resource->date_created,
            'date_valid_until' => $this->resource->date_valid_until,
            'total_amount' => (float) $this->resource->total_amount,
            'lead_name' => $this->resource->lead->full_name,
            'status' => $this->resource->status,
            'status_pretty' => QuotationStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => QuotationStatusEnum::getColorType($this->resource->status)
        ];
    }

}
