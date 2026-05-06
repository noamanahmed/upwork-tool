<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JobController extends Controller
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
            'applicants',
            'client_name',
            'total_spend',
            'proposal_status',
            'client_total_posted_jobs',
            'client_total_hires',
            'client_total_reviews',
            'client_total_feedback',
        ];

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
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.totalApplicants')) AS UNSIGNED),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'client_name':
                $query->orderByRaw("
                    COALESCE(
                        JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.job.ownership.team.name')),
                        ''
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'total_spend':
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalSpent.rawValue')) AS DECIMAL(10,2)),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'client_total_posted_jobs':
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalPostedJobs')) AS UNSIGNED),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'client_total_hires':
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalHires')) AS UNSIGNED),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'client_total_reviews':
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalReviews')) AS UNSIGNED),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'client_total_feedback':
                $query->orderByRaw("
                    COALESCE(
                        CAST(JSON_UNQUOTE(JSON_EXTRACT(jobs.json, '$.node.client.totalFeedback')) AS UNSIGNED),
                        0
                    ) {$sortDir}, jobs.id {$sortDir}
                ");
                break;
            case 'proposal_status':
                // Sort by latest proposal status using subquery
                $query->orderBySub(function($q) use ($sortDir) {
                    $q->select('status')
                      ->from('ai_job_proposals')
                      ->whereColumn('job_id', 'jobs.id')
                      ->orderByDesc('created_at')
                      ->limit(1);
                }, $sortDir);
                $query->orderBy('jobs.created_at', 'desc'); // Secondary sort
                break;
        }

        $jobs = $query->paginate($perPage)->withQueryString();

        return view('jobs.index', compact('jobs', 'sortBy', 'sortDir'));
    }


}
