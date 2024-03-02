<?php

namespace App\Transformers;

abstract class BaseCollectionTransformer extends BaseTransformer implements BaseCollectionTransformerContract
{
    abstract public function getEntityTransformer() : BaseTransformerContract;

    public function toArray()
    {
        $output = [];
        $output['data'] = $this->resource['data'] ?? [];

        foreach($this->resource->items() as $entity)
        {
            $entityTransformer = $this->getEntityTransformer();
            $transformer = (new $entityTransformer)->setResource($entity);
            $output['data'][] = $transformer->toArray();
        }
        $output = $this->buildPaginationMetaData($output,$this->resource);
        return $output;
    }
}
