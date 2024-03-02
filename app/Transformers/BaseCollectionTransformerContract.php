<?php

namespace App\Transformers;

interface BaseCollectionTransformerContract{
    public function getEntityTransformer() : BaseTransformerContract;
}
