<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class PrepareTestDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:prepare-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepares test DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        config()->set('database.default','testing');
        Artisan::call('migrate:fresh',['--database' => 'testing']);
        Artisan::call('test:seed');
    }
}
