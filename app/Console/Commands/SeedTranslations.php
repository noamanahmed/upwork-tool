<?php

namespace App\Console\Commands;

use Artisan;
use Illuminate\Console\Command;

class SeedTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed translations from PHP array';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Artisan::call('db:seed --class=TranslationSeeder');
    }
}
