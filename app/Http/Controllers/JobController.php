<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\BaseController;

class JobController extends BaseController
{
    /**
     * Display a paginated list of all jobs with sorting.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');

        // Whitelist allowed sort columns
        $allowedSorts = [
            'title',
            'created_at',
            'budget_minimum',
            'budget_maximum',
            'location',
            'is_hourly',
        ];

        // Also allow sorting on computed/related fields via joins or raw expressions
        $allowedSorts[] = 'applicants';
        $allowedSorts[] = 'client_name';
        $allowedSorts[] = 'total_spend';
        $allowedSorts[] = 'proposal_status';

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $query = Job::with('aiProposals');

        // Apply custom sorting
        switch ($sortBy) {
            case 'title':
                $query->orderBy('title', $sortDir);
                break;
            case 'created_at':
                $query->orderBy('created_at', $sortDir);
                break;
            case 'budget_minimum':
                $query->orderBy('budget_minimum', $sortDir);
                break;
            case 'budget_maximum':
                $query->orderBy('budget_maximum', $sortDir);
                break;
            case 'location':
                $query->orderBy('location', $sortDir);
                break;
            case 'is_hourly':
                $query->orderBy('is_hourly', $sortDir);
                break;
            case 'applicants':
                // Sort by totalApplicants from JSON data
                $query->orderByRaw("
                    CASE 
                        WHEN JSON_VALID(jobs.json) THEN 
                            CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.totalApplicants')) AS UNSIGNED)
                        ELSE 0 
                    END {$sortDir}
                ");
                break;
            case 'client_name':
                // Sort by client name from JSON
                $query->orderByRaw("
                    CASE 
                        WHEN JSON_VALID(jobs.json) THEN 
                            JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.job.ownership.team.name'))
                        ELSE ''
                    END {$sortDir}
                ");
                break;
            case 'total_spend':
                // Sort by total spend raw value
                $query->orderByRaw("
                    CASE 
                        WHEN JSON_VALID(jobs.json) THEN 
                            CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalSpent.rawValue')) AS UNSIGNED)
                        ELSE 0 
                    END {$sortDir}
                ");
                break;
            case 'proposal_status':
                // Sort by latest proposal status
                $query->leftJoin('ai_job_proposals as latest_proposal', function ($join) {
                    $join->on('jobs.id', '=', 'latest_proposal.job_id')
                         ->whereRaw('latest_proposal.id = (
                             SELECT id FROM ai_job_proposals ap 
                             WHERE ap.job_id = jobs.id 
                             ORDER BY created_at DESC LIMIT 1
                         )');
                });
                $query->orderBy('latest_proposal.status', $sortDir);
                break;
        }

        $jobs = $query->paginate($perPage)->withQueryString();

        return view('jobs.index', compact('jobs', 'sortBy', 'sortDir'));
    }


}
