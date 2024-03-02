<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RebuildPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will create new permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('db:seed --class=RoleSeeder');
        Artisan::call('db:seed --class=PermissionSeeder');
        Artisan::call('db:seed --class=RolePermissionSeeder');

    }
}
