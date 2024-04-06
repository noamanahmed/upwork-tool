<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Services\ThirdParty\SlackService;
use Illuminate\Console\Command;

class SendJobSlackNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:send-slack-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jobs = Job::where('is_slack_webhook_sent',0)->take(10)->get();
        foreach($jobs as $job)
        {
            app(SlackService::class)->sendNotification($job->slack_notification_message);
        }
        $jobIds = $jobs->pluck('id')->toArray();
        if(!empty($jobIds))
        {
            Job::whereIn('id',$jobIds)->update([
                'is_slack_webhook_sent' => 1
            ]);
        }

    }
}
