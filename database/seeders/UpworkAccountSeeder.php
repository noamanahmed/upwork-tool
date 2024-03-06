<?php

namespace Database\Seeders;

use App\Models\UpworkAccount;
use Illuminate\Database\Seeder;

class UpworkAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UpworkAccount::factory()->count(24)->create();
    }
}
