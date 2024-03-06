<?php

namespace Database\Seeders;

use App\Models\JobAttachment;
use Illuminate\Database\Seeder;

class JobAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JobAttachment::factory()->count(24)->create();
    }
}
