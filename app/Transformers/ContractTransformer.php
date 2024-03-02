<?php

namespace App\Transformers;

use App\Enums\ContractStatusEnum;
use App\Enums\ContractTypeEnum;
use App\Models\Contract;

class ContractTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Contract;
        parent::__construct();
    }

    public function toArray(){
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' =>  $this->resource->description,
            'reference_number' =>  $this->resource->reference_number,
            'start_contract_date' =>  $this->resource->start_contract_date,
            'end_contract_date' =>  $this->resource->end_contract_date,
            'contract_pdf' => $this->resource->contract_pdf ? $this->resource->contract_pdf->signedUrl : null,
            'status' =>  $this->resource->status,
            'status_pretty' => ContractStatusEnum::getPrettyKeyfromValue($this->resource->status),
            'status_color' => ContractStatusEnum::getColorType($this->resource->status),
            'status' =>  $this->resource->status,
            'type' =>  $this->resource->type,
            'type_pretty' => ContractTypeEnum::getPrettyKeyfromValue($this->resource->type),
            'type_color' => ContractTypeEnum::getColorType($this->resource->type),
            'locations' => $this->getLocations(),
        ];
    }

    public function getLocations(){
        $locations = [];
        foreach($this->resource->locations as $location)
        {
            $locations[] = [
                'id' => $location->id,
                'name' => $location->name,
            ];
        }
        return $locations;
    }

}
