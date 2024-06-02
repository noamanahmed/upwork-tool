<?php

namespace App\Console\Commands;

use App\Models\RssJobSearches;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Services\ThirdParty\SlackService;


class SendRssJobSlackNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:send-rss-slack-notifications';

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
        $jobSearches = RssJobSearches::whereHas('latestJobs',function($query){
            return $query->where('is_slack_webhook_sent',0);
        })->take(10)->get();
        $webhookSent = [];
        $locks = [];
        foreach($jobSearches as $jobSearch)
        {

            foreach($jobSearch->latestJobs as $rssJob)
            {

                $lock = Cache::lock('slack_job_notification_for_rss_job_' . $rssJob->id.'_rss_job_search_'.$jobSearch->id, 30);
                if (!$lock->get()) continue;
                $rssJob->refresh();

                $job = $rssJob->job;
                if(empty($job)) continue;

                // app(SlackService::class)->setWebhookUrl($jobSearch->slack_webhook_url)->sendNotification($job->slack_notification_message);
                $webhookSent[] = $rssJob;
                $locks[] = $lock;
            }
        }

    foreach($webhookSent as $key => $job)
        {
            $webhookSent[$key]->update([
                'is_slack1_webhook_sent' => 1
            ]);
        }



        foreach($locks as $lock)
        {
            $lock->release();
        }

        dd($webhookSent);
    }
}
