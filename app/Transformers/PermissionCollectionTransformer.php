<?php

namespace App\Transformers;

class PermissionCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function toArray()
    {
        $output = [];
        $output['data'] = $this->resource['data'] ?? [];

        foreach($this->resource as $entity)
        {
            $moduleName = $entity->getModuleName();
            $actionName = $entity->getActionName();
            $output['data'][$moduleName][] = $actionName;
        }

        foreach($output['data'] as $key => $value)
        {
            $value = array_values(array_unique($value));
            $output['data'][$key] = $value;
        }
        return $output;
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new PermissionTransformer();
    }

}
