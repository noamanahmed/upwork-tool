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
                    $jobId = $job['id'];
                }
                $proposals[] = [
                    'proposal_id' => $node['id'],
                    'job_id' => $jobId,
                    'upwork_job_id' => $upworkJobId,
                    'cover_letter' => $proposalData['node']['proposalCoverLetter'] ?? 'N/A',
                    'duration' => $proposalData['node']['terms']['estimatedDuration']['label'] ?? 'N/A',
                    'bid' => $proposalData['node']['terms']['chargeRate']['rawValue'] ?? 0,
                    'currency' => $proposalData['node']['terms']['chargeRate']['currency'] ?? 'N/A',
                    'job_title' => $proposalData['node']['marketplaceJobPosting']['content']['title'] ?? 'N/A',
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
}
