<?php

namespace App\Transformers;

class TranslationCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }


    public function toArray()
    {
        $output = [];
        foreach($this->resource as $entity)
        {
            $entityTransformer = $this->getEntityTransformer();
            $transformer = (new $entityTransformer)->setResource($entity);
            $output[] = $transformer->toArray();
        }
        return $output;
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new TranslationTransformer();
    }

}
