<?php


namespace App\Services;

use App\Enums\JobStatusEnum;
use App\Enums\ProposalStatusEnum;
use App\Jobs\JobActivity as JobsJobActivity;
use App\Models\Job;
use App\Models\JobActivity;
use App\Models\JobSearchPivot;
use App\Models\Proposal;
use App\Models\RssJobs;
use App\Repositories\JobRepository;
use App\Transformers\JobCollectionTransformer;
use App\Transformers\JobTransformer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ProposalService
{

    public function insertProposalsFromApiResponse($data)
    {
        $locks = [];
        $proposals = [];
        $proposalIds = [];
        $jobIds = [];
        foreach ($data as $proposal) {
            if (empty($proposal)) continue;
            $node = $proposal['node'];
            $proposalIds[] = $node['id'];
            if($node['marketplaceJobPosting']['id'] ?? false)
            {
                $jobIds[] = $node['marketplaceJobPosting']['id'];
            }
        }
        $alreadyExistingJobsModels = Job::whereIn('upwork_id',$jobIds)->get()->keyBy('upwork_id');
        $alreadyExistingJobs = $alreadyExistingJobsModels->toArray();

        $alreadyExistingProposalsModels = Proposal::whereIn('proposal_id',$proposalIds)->get()->keyBy('proposal_id');
        $alreadyExistingProposals = $alreadyExistingProposalsModels->toArray();

        foreach ($data as $proposalData) {
            $upworkJobId = $proposalData['node']['marketplaceJobPosting']['id'] ?? false;
            if(!$upworkJobId) continue;
            if (empty($proposalData)) continue;
            $node = $proposalData['node'];
            $node = Arr::dot($node);
            if($alreadyExistingProposals[$node['id']] ?? false) continue;
            $lock = Cache::lock('proposal_service_insert_proposal_' . $node['id'], 10);
            $jobId = null;
            if ($lock->get()) {
                $job = $alreadyExistingJobs[$upworkJobId] ?? null;
                if (!is_null($job)) {
                    $jobId = $job->id;
                }
                $proposals[] = [
                    'proposal_id' => $node['id'],
                    'job_id' => $jobId,
                    'upwork_job_id' => $upworkJobId,
                    'cover_letter' => $node['proposalCoverLetter'] ?? 'N/A',
                    'duration' => $node['terms']['estimatedDuration'] ?? 'N/A',
                    'bid' => $node['terms']['chargeRate']['rawValue'] ?? 0,
                    'currency' => $node['terms']['chargeRate']['currency'] ?? 'N/A',
                    'status' => ProposalStatusEnum::fromName(strtoupper($proposalData['type'])),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $locks[] = $lock;
            }
        }

        if(!empty($proposals))
        {
            Proposal::insert($proposals);
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
