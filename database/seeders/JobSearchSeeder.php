<?php

namespace Database\Seeders;

use App\Models\JobSearch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobSearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        JobSearch::whereIn('id',[1,2,3])->delete();
        $defaultLocations = '';
        $minimumFeedback = 4;
        $maximumProposals = 10;

        $jobSearch = new JobSearch();
        $jobSearch->id = 1;
        $jobSearch->user_id = 1;
        $jobSearch->name = 'laravel';
        $jobSearch->q = 'laravel';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->is_payment_verified = true;
        $jobSearch->location = $defaultLocations;
        $jobSearch->feedback_minimum = $minimumFeedback;
        $jobSearch->proposals_maximum = $maximumProposals;
        $jobSearch->save();

        $jobSearch = new JobSearch();
        $jobSearch->id = 2;
        $jobSearch->user_id = 1;
        $jobSearch->name = 'wordpress';
        $jobSearch->q = 'wordpress';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->is_payment_verified = true;
        $jobSearch->location = $defaultLocations;
        $jobSearch->feedback_minimum = $minimumFeedback;
        $jobSearch->proposals_maximum = $maximumProposals;
        $jobSearch->save();

        $jobSearch = new JobSearch();
        $jobSearch->id = 3;
        $jobSearch->user_id = 1;
        $jobSearch->name = 'devops';
        $jobSearch->q = 'devops';
        $jobSearch->slack_webhook_url = config('services.slack.webhook_url');
        $jobSearch->is_payment_verified = true;
        $jobSearch->location = $defaultLocations;
        $jobSearch->feedback_minimum = $minimumFeedback;
        $jobSearch->proposals_maximum = $maximumProposals;
        $jobSearch->save();

        DB::commit();
    }
}
