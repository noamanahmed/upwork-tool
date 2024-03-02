<?php

namespace App\Transformers;

use App\Models\Role;

class RoleTransformer extends BaseTransformer
{

    public function __construct()
    {
        $this->resource = new Role;
        parent::__construct();
    }

    public function toArray(){
        $permissionsData = $this->resource->permissions;
        $permissions = [];
        $response =  [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'pretty_name' => $this->resource->pretty_name,
        ];
        foreach($permissionsData as $entity)
        {

            $moduleName = $entity->getModuleName();
            $actionName = $entity->getActionName();

            $permissions[$moduleName][] = $actionName;
        }
        foreach($permissions as $key => $value)
        {
            $value = array_values(array_unique($value));
            $permissions[$key] = $value;
        }
        $response['permissions'] = $permissions;
        return $response;
    }

}
