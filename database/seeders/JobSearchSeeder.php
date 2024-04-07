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
        $jobSearch = new JobSearch();
        $jobSearch->user_id = 1;
        $jobSearch->name = 'laravel';
        $jobSearch->q = 'laravel';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->save();

        $jobSearch = new JobSearch();
        $jobSearch->user_id = 1;
        $jobSearch->name = 'wordpress';
        $jobSearch->q = 'wordpress';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->save();

        $jobSearch = new JobSearch();
        $jobSearch->user_id = 1;
        $jobSearch->name = 'devops';
        $jobSearch->q = 'devops';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->save();
    }
}
