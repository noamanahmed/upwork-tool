<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::updateOrCreate(['guard_name' => 'web','priority' => 100, 'name' => 'admin'],['guard_name' => 'web','priority' => 100, 'name' => 'admin']);
        Role::updateOrCreate(['guard_name' => 'web','priority' => 1, 'name' => 'super_admin'],['guard_name' => 'web','priority' => 1, 'name' => 'super_admin']);
        Role::updateOrCreate(['guard_name' => 'web','priority' => 1000, 'name' => 'read_only'],['guard_name' => 'web','priority' => 1000, 'name' => 'read_only']);
        Role::updateOrCreate(['guard_name' => 'web','priority' => 500, 'name' => 'employee'],['guard_name' => 'web','priority' => 500, 'name' => 'employee']);
    }
}
