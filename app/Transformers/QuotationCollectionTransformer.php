<?php

namespace App\Transformers;

use App\Enums\QuotationStatusEnum;
use App\Models\Quotation;

class QuotationCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new QuotationTransformer();
    }

}
