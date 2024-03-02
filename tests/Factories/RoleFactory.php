<?php

namespace Tests\Factories;

use App\Models\Role;

trait RoleFactory {
    static $role = null;

    function createRole()
    {
        static::$role = Role::factory()->create([

        ]);
        return static::$role;
    }

    function makeRole()
    {
        static::$role = Role::factory()->make([

        ]);
        return static::$role;
    }
}
