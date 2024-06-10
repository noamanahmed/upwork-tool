<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Jobs\JobActivity as JobsJobActivity;
use App\Models\Job;
use App\Models\JobActivity;
use App\Models\JobSearchPivot;
use App\Models\RssJobs;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class JobActivityService
{

    public function insertActivitiesFromApiResponse($data)
    {
        $locks = [];
        $jobActivities = [];
        $jobIds = [];
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $jobIds[] = $node['id'];
        }
        $jobsWithNewlyCreatedActivity = [];
        $alreadyExistingJobsModels = Job::whereIn('upwork_id',$jobIds)->with(['latestActivity'])->get()->keyBy('upwork_id');
        $alreadyExistingJobs = $alreadyExistingJobsModels->toArray();
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $node = Arr::dot($node);
            $lock = Cache::lock('job_activity_service_insert_job_activity_' . $node['id'], 10);
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$node['id']] ?? null;
                if (is_null($job) || array_key_exists('latestActivity',$job)) {
                    $lock->release();
                    continue;
                }

                $jobActivities[] = [
                    'job_id' => $job['id'],
                    'schedule' => 'FIRST',
                    'total_applicants' => $node['totalApplicants'] ?? 0,
                    'average_rate_bid' => $node['job.activityStat.applicationsBidStats.avgRateBid.rawValue'] ?? 0,
                    'minimum_rate_bid' => $node['job.activityStat.applicationsBidStats.minRateBid.rawValue'] ?? 0,
                    'maximum_rate_bid' => $node['job.activityStat.applicationsBidStats.maxRateBid.rawValue'] ?? 0,
                    'interview_rate_bid' => $node['job.activityStat.applicationsBidStats.avgInterviewedRateBid.rawValue'] ?? 0,
                    'invites_sent' => $node['job.activityStat.jobActivity.invitesSent'] ?? 0,
                    'total_invited_to_interview' => $node['job.activityStat.jobActivity.totalInvitedToInterview'] ?? 0,
                    'total_hired' => $node['job.activityStat.jobActivity.totalHired'] ?? 0,
                    'total_unanswered_invites' => $node['job.activityStat.jobActivity.totalUnansweredInvites'] ?? 0,
                    'total_offered' => $node['job.activityStat.jobActivity.totalOffered'] ?? 0,
                    'total_recommended' => $node['job.activityStat.jobActivity.totalRecommended'] ?? 0,
                    'last_client_activity' => $node['job.activityStat.jobActivity.lastClientActivity'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $locks[] = $lock;
                $jobsWithNewlyCreatedActivity[] = $node['id'];
            }
        }
        $activityFetchingSchedule = (new JobActivity())->dispatchSchedule();
        foreach($jobsWithNewlyCreatedActivity as $newJob)
        {
            $job = $alreadyExistingJobsModels[$newJob];
            foreach($activityFetchingSchedule as $delayInSeconds)
            {
                JobsJobActivity::dispatch($job,$delayInSeconds)->delay($delayInSeconds);
            }
        }

        if(!empty($jobActivities))
        {
            JobActivity::insert($jobActivities);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
    public function updatejobActivities($job,$data,$schedule = 'DEFAULT')
    {
        $locks = [];
        $jobActivities = [];
        $jobIds = [];
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $jobIds[] = $node['id'];
        }

        $alreadyExistingJobs = Job::whereIn('upwork_id',$jobIds)->with(['latestActivity'])->get()->keyBy('upwork_id')->toArray();
        foreach ($data as $jobData) {
            if (empty($jobData)) continue;
            $node = $jobData['node'];
            $node = Arr::dot($node);
            $lock = Cache::lock('job_activity_service_insert_job_activity_' . $node['id'], 10);
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$node['id']] ?? null;
                if (is_null($job)) {
                    $lock->release();
                    continue;
                }
                $jobActivities[] = [
                    'job_id' => $job['id'],
                    'schedule' => $schedule,
                    'total_applicants' => $node['totalApplicants'] ?? 0,
                    'average_rate_bid' => $node['job.activityStat.applicationsBidStats.avgRateBid.rawValue'] ?? 0,
                    'minimum_rate_bid' => $node['job.activityStat.applicationsBidStats.minRateBid.rawValue'] ?? 0,
                    'maximum_rate_bid' => $node['job.activityStat.applicationsBidStats.maxRateBid.rawValue'] ?? 0,
                    'interview_rate_bid' => $node['job.activityStat.applicationsBidStats.avgInterviewedRateBid.rawValue'] ?? 0,
                    'invites_sent' => $node['job.activityStat.jobActivity.invitesSent'] ?? 0,
                    'total_invited_to_interview' => $node['job.activityStat.jobActivity.totalInvitedToInterview'] ?? 0,
                    'total_hired' => $node['job.activityStat.jobActivity.totalHired'] ?? 0,
                    'total_unanswered_invites' => $node['job.activityStat.jobActivity.totalUnansweredInvites'] ?? 0,
                    'total_offered' => $node['job.activityStat.jobActivity.totalOffered'] ?? 0,
                    'total_recommended' => $node['job.activityStat.jobActivity.totalRecommended'] ?? 0,
                    'last_client_activity' => $node['job.activityStat.jobActivity.lastClientActivity'] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $locks[] = $lock;
            }
        }

        if(!empty($jobActivities))
        {
            JobActivity::insert($jobActivities);
        }
        foreach($locks as $lock)
        {
            $lock->release();
        }
    }
}
