<?php

namespace Database\Seeders;

use App\Models\JobSearch;
use Illuminate\Database\Seeder;

class JobSearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JobSearch::factory()->count(24)->create();
    }
}
