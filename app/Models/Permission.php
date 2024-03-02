<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as ModelsPermission;

class Permission extends ModelsPermission
{
    public static $defaultPermissions = [
        'get_dashboard',
        'get_profile',
        'update_profile',
        'get_settings',
        'update_settings',
    ];

    use HasFactory;

    public function getModuleName()
    {
        $parts = explode('_', $this->name);
        return end($parts);
    }

    public function getActionName()
    {
        $parts = explode('_', $this->name);
        array_pop($parts); // Remove the last element (module name)
        return implode('_', $parts);
    }

    public function scopeAvailable($query){
        return $query->whereNotIn('name',[
            'create_roles',
            'delete_multi_roles',
            'delete_roles',
            'get_roles',
            'index_roles',
            'update_roles',
        ]);
    }
}
