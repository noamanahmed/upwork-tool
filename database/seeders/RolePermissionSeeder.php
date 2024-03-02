<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modelsPath = app_path('Models');
        $modelFiles = File::files($modelsPath);
        $models = [];

        foreach ($modelFiles as $file) {
            if(strrpos($file->getFilenameWithoutExtension(),'Base') === 0) continue;
            $models[] = $file->getFilenameWithoutExtension();
        }
        $defaultPermission = [
            'index',
            'get',
            'create',
            'update',
            'delete',
            'delete_multi',
        ];
        $defaultReadOnlyPermissions = [
            'index',
            'get'
        ];

        $permissions = [];
        $readOnlyPermissions = [];
        foreach($models as $model)
        {
            $modelName = str($model)->snake()->plural();
            foreach($defaultPermission as $permission)
            {
                $permissions[] = $permission . '_' . $modelName;
            }
            if($modelName->value() === 'roles') continue;
            if($modelName->value() === 'users') continue;
            foreach($defaultReadOnlyPermissions as $permission)
            {
                $readOnlyPermissions[] = $permission . '_' . $modelName;
            }
        }
        $defaultPermission = Permission::$defaultPermissions;

        $defaultPermissionsList = Permission::whereIn('name',$defaultPermission)->whereGuardName('web')->get();
        $permissionsList = Permission::whereIn('name',$permissions)->whereGuardName('web')->get();
        $readOnlyPermissionsList = Permission::whereIn('name',$readOnlyPermissions)->whereGuardName('web')->get();
        $permissionsList = [...$permissionsList,...$defaultPermissionsList];
        $readOnlyPermissionsList = [...$readOnlyPermissionsList,...$defaultPermissionsList];

        $adminRole = Role::whereName('admin')->firstOrFail();
        $superAdminRole = Role::whereName('super_admin')->firstOrFail();
        $readOnlyRole = Role::whereName('read_only')->firstOrFail();
        $employeeRole = Role::whereName('employee')->firstOrFail();

        $adminRole->syncPermissions($permissionsList);
        $superAdminRole->syncPermissions($permissionsList);
        $readOnlyRole->syncPermissions($readOnlyPermissionsList);
        $employeeRole->syncPermissions($readOnlyPermissionsList);

    }
}
