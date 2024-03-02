<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PermissionSeeder extends Seeder
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
            $modelName = $file->getFilenameWithoutExtension();
            $modelName = explode('_',str($modelName)->snake())[0];
            $models[$modelName] = $modelName;
        }
        $defaultPermission = Permission::$defaultPermissions;

        $defaultModelPermission = [
            'index',
            'get',
            'create',
            'update',
            'delete',
            'delete_multi',
        ];
        $permissions = [];
        foreach($models as $model)
        {
            $modelName = str($model)->snake()->plural();
            foreach($defaultModelPermission as $permission)
            {
                $permissions[] = [
                    'guard_name' => 'web',
                    'name' => $permission . '_' . $modelName
                ];
                $permissions[] = [
                    'guard_name' => 'api',
                    'name' => $permission . '_' . $modelName
                ];
            }
        }
        foreach($defaultPermission as $permission)
        {
            $permissions[] = [
                'guard_name' => 'web',
                'name' => $permission
            ];
            $permissions[] = [
                'guard_name' => 'api',
                'name' => $permission
            ];
        }

        foreach($permissions as $permission)
        {
            Permission::updateOrCreate($permission,$permission);
        }

    }
}
