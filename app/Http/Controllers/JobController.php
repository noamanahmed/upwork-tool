<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobSearch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class JobController extends Controller
{
    /**
     * Display a paginated list of all jobs with sorting and search filtering.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $selectedSearchId = $request->get('search_id');

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

        $query = Job::with(['aiProposals', 'searches']);

        // Filter by search/category
        if ($selectedSearchId) {
            $query->whereHas('searches', function ($q) use ($selectedSearchId) {
                $q->where('job_searches.id', $selectedSearchId);
            });
        }

        // Free-text search on title and description
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter (latest ai_job_proposals.status)
        if ($status = $request->get('status')) {
            if ($status === 'none') {
                $query->whereDoesntHave('aiProposals');
            } else {
                $query->whereHas('aiProposals', function ($q) use ($status) {
                    $q->where('status', $status)
                      ->whereRaw('ai_job_proposals.id = (SELECT MAX(id) FROM ai_job_proposals WHERE job_id = jobs.id)');
                });
            }
        }

        // Type filter: hourly or fixed
        if ($type = $request->get('type')) {
            if ($type === 'hourly') {
                $query->where('is_hourly', 1);
            } elseif ($type === 'fixed') {
                $query->where('is_hourly', 0);
            }
        }

        // Budget range (direct columns)
        if ($budgetMin = $request->get('budget_min')) {
            $query->where('budget_minimum', '>=', (float) $budgetMin);
        }
        if ($budgetMax = $request->get('budget_max')) {
            $query->where('budget_maximum', '<=', (float) $budgetMax);
        }

        // Applicants range (JSON column)
        if ($val = $request->get('applicants_min')) {
            $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(json, '$.node.totalApplicants')) AS UNSIGNED) >= ?", [(int) $val]);
        }
        if ($val = $request->get('applicants_max')) {
            $query->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(json, '$.node.totalApplicants')) AS UNSIGNED) <= ?", [(int) $val]);
        }

        // Spend range (DB column)
        if ($val = $request->get('spend_min')) {
            $query->where('client_total_spent', '>=', (float) $val);
        }
        if ($val = $request->get('spend_max')) {
            $query->where('client_total_spent', '<=', (float) $val);
        }

        // Spend currency
        if ($currency = $request->get('spend_currency')) {
            $query->where('client_total_spent_currency', $currency);
        }

        // Posted jobs range
        if ($val = $request->get('posted_jobs_min')) {
            $query->where('client_total_posted_jobs', '>=', (int) $val);
        }
        if ($val = $request->get('posted_jobs_max')) {
            $query->where('client_total_posted_jobs', '<=', (int) $val);
        }

        // Hires range
        if ($val = $request->get('hires_min')) {
            $query->where('client_total_hires', '>=', (int) $val);
        }
        if ($val = $request->get('hires_max')) {
            $query->where('client_total_hires', '<=', (int) $val);
        }

        // Reviews range
        if ($val = $request->get('reviews_min')) {
            $query->where('client_total_reviews', '>=', (int) $val);
        }
        if ($val = $request->get('reviews_max')) {
            $query->where('client_total_reviews', '<=', (int) $val);
        }

        // Feedback range
        if ($val = $request->get('feedback_min')) {
            $query->where('client_total_feedback', '>=', (float) $val);
        }
        if ($val = $request->get('feedback_max')) {
            $query->where('client_total_feedback', '<=', (float) $val);
        }

        // Posted date range
        if ($val = $request->get('posted_from')) {
            $query->whereDate('created_at', '>=', $val);
        }
        if ($val = $request->get('posted_to')) {
            $query->whereDate('created_at', '<=', $val);
        }

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
        $allSearches = JobSearch::orderBy('name')->withCount('jobs')->get();
        $currencies = Job::select('client_total_spent_currency')
            ->distinct()
            ->whereNotNull('client_total_spent_currency')
            ->orderBy('client_total_spent_currency')
            ->pluck('client_total_spent_currency');

        $filters = $request->only([
            'search', 'status', 'type',
            'budget_min', 'budget_max',
            'applicants_min', 'applicants_max',
            'spend_min', 'spend_max', 'spend_currency',
            'posted_jobs_min', 'posted_jobs_max',
            'hires_min', 'hires_max',
            'reviews_min', 'reviews_max',
            'feedback_min', 'feedback_max',
            'posted_from', 'posted_to',
        ]);

        return view('jobs.index', compact(
            'jobs', 'sortBy', 'sortDir', 'allSearches', 'selectedSearchId',
            'currencies', 'filters'
        ));
    }


}
