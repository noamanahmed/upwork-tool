<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class MigrateTestDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs migration on test DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        config()->set('database.default','testing');
        $this->info('Running Migrations');
        Artisan::call('migrate',['--database' => 'testing']);
        $this->info('Running Migrations Completed');
    }
}
