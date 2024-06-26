<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobSearch;
use App\Services\ThirdParty\SlackService;
use Cache;
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
        $jobSearches = JobSearch::whereHas('latestJobs',function($query){
            return $query->where('job_searches_jobs_pivot.is_slack_webhook_sent',0);
        })->take(10)->with(['latestJobs'])->get();

        $webhookSent = [];
        $locks = [];
        foreach($jobSearches as $jobSearch)
        {
            foreach($jobSearch->latestJobs as $job)
            {

                $lock = Cache::lock('slack_job_notification_for_job_' . $job->id.'_job_search_'.$jobSearch->id, 30);
                if (!$lock->get()) continue;
                $job->refresh();
                if($job->pivot->is_slack_webhook_sent) continue;
                if(empty($jobSearch->slack_webhook_url)) continue;
                app(SlackService::class)->setWebhookUrl($jobSearch->slack_webhook_url)->sendNotification($job->slack_notification_message);
                $webhookSent[] = $job->pivot;
                $locks[] = $lock;
            }
        }


        foreach($webhookSent as $pivot)
        {
            $pivot->update([
                'is_slack_webhook_sent' => 1
            ]);
        }


        foreach($locks as $lock)
        {
            $lock->release();
        }


    }
}
