<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class SeedTestDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:seed';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will seed dummy data to ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        config()->set('database.default','testing');
        $this->info('Seeding Essentials');
        Artisan::call('db:seed');
        $this->info('Seeding Essentials Completed!');

    }
}
