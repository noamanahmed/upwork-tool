<?php

namespace App\Services;

use App\Models\AiJobProposal;
use App\Models\Job;
use App\Models\JobSearch;
use App\Models\Proposal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyticsService
{
    /**
     * Supported analytics periods.
     */
    public const PERIOD_DAILY = 'daily';
    public const PERIOD_MONTHLY = 'monthly';
    public const PERIOD_YEARLY = 'yearly';

    /**
     * Generate analytics for a given period ('daily', 'monthly', 'yearly').
     */
    public function generate(string $period): array
    {
        $period = strtolower(trim($period));

        $boundaries = $this->getPeriodBoundaries($period);

        $startDate = $boundaries['start_date'];
        $endDate = $boundaries['end_date'];
        $prevStartDate = $boundaries['prev_start_date'];
        $prevEndDate = $boundaries['prev_end_date'];

        // 1. Current Period Job Statistics
        $jobsQuery = Job::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
        $totalJobs = (clone $jobsQuery)->count();
        $hourlyJobs = (clone $jobsQuery)->where('is_hourly', true)->count();
        $fixedJobs = (clone $jobsQuery)->where('is_hourly', false)->count();
        $verifiedPaymentJobs = (clone $jobsQuery)->where('is_payment_verified', true)->count();
        $slackSentJobs = (clone $jobsQuery)->where('is_slack_webhook_sent', true)->count();

        $avgHourlyRate = (clone $jobsQuery)
            ->where('is_hourly', true)
            ->where('budget_minimum', '>', 0)
            ->avg('budget_minimum') ?? 0;

        $avgFixedBudget = (clone $jobsQuery)
            ->where('is_hourly', false)
            ->where('budget_minimum', '>', 0)
            ->avg('budget_minimum') ?? 0;

        $totalClientSpent = (clone $jobsQuery)->sum('client_total_spent') ?? 0;
        $totalClientHires = (clone $jobsQuery)->sum('client_total_hires') ?? 0;

        $avgClientFeedback = (clone $jobsQuery)
            ->where('client_total_feedback', '>', 0)
            ->avg('client_total_feedback') ?? 0;

        // 2. Previous Period Job Statistics (for trend comparison)
        $prevTotalJobs = Job::where('created_at', '>=', $prevStartDate)
            ->where('created_at', '<=', $prevEndDate)
            ->count();

        $jobGrowthPercent = $prevTotalJobs > 0
            ? round((($totalJobs - $prevTotalJobs) / $prevTotalJobs) * 100, 1)
            : ($totalJobs > 0 ? 100.0 : 0.0);

        // 3. Jobs per Search configuration
        $searchesBreakdown = Job::join('job_searches_jobs_pivot', 'jobs.id', '=', 'job_searches_jobs_pivot.job_id')
            ->join('job_searches', 'job_searches_jobs_pivot.job_search_id', '=', 'job_searches.id')
            ->where('jobs.created_at', '>=', $startDate)
            ->where('jobs.created_at', '<=', $endDate)
            ->groupBy('job_searches.id', 'job_searches.name')
            ->select(
                'job_searches.id',
                'job_searches.name as search_name',
                DB::raw('COUNT(jobs.id) as jobs_count')
            )
            ->orderByDesc('jobs_count')
            ->get()
            ->toArray();

        // 4. AI Proposals Statistics
        $aiProposalsQuery = AiJobProposal::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);

        $totalAiProposals = (clone $aiProposalsQuery)->count();
        $aiCompleted = (clone $aiProposalsQuery)->where('status', 'completed')->count();
        $aiPending = (clone $aiProposalsQuery)->where('status', 'pending')->count();
        $aiFailed = (clone $aiProposalsQuery)->where('status', 'failed')->count();

        $aiByProvider = (clone $aiProposalsQuery)
            ->select('provider', DB::raw('COUNT(*) as count'))
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        // 5. Upwork Submitted Proposals
        $proposalsQuery = Proposal::where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate);
        $totalSubmittedProposals = (clone $proposalsQuery)->count();
        $totalBidAmount = (clone $proposalsQuery)->sum('bid') ?? 0;

        // 6. Top In-Demand Skills
        $topSkills = DB::table('job_skills')
            ->join('jobs', 'job_skills.job_id', '=', 'jobs.id')
            ->join('skills', 'job_skills.skill_id', '=', 'skills.id')
            ->where('jobs.created_at', '>=', $startDate)
            ->where('jobs.created_at', '<=', $endDate)
            ->groupBy('skills.id', 'skills.name')
            ->select('skills.name', DB::raw('COUNT(jobs.id) as count'))
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'name')
            ->toArray();

        $analytics = [
            'period' => $period,
            'period_label' => $boundaries['label'],
            'generated_at' => now()->toIso8601String(),
            'start_date' => $startDate->toDateTimeString(),
            'end_date' => $endDate->toDateTimeString(),
            'metrics' => [
                'total_jobs' => $totalJobs,
                'previous_period_jobs' => $prevTotalJobs,
                'job_growth_percent' => $jobGrowthPercent,
                'hourly_jobs' => $hourlyJobs,
                'fixed_jobs' => $fixedJobs,
                'avg_hourly_rate' => round($avgHourlyRate, 2),
                'avg_fixed_budget' => round($avgFixedBudget, 2),
                'total_client_spent' => round($totalClientSpent, 2),
                'total_client_hires' => (int) $totalClientHires,
                'avg_client_feedback' => round($avgClientFeedback, 2),
                'verified_payment_jobs' => $verifiedPaymentJobs,
                'verified_payment_percentage' => $totalJobs > 0 ? round(($verifiedPaymentJobs / $totalJobs) * 100, 1) : 0,
                'slack_sent_jobs' => $slackSentJobs,
                'total_ai_proposals' => $totalAiProposals,
                'ai_completed' => $aiCompleted,
                'ai_pending' => $aiPending,
                'ai_failed' => $aiFailed,
                'ai_by_provider' => $aiByProvider,
                'total_submitted_proposals' => $totalSubmittedProposals,
                'total_bid_amount' => round($totalBidAmount, 2),
            ],
            'searches_breakdown' => $searchesBreakdown,
            'top_skills' => $topSkills,
        ];

        // Store in Cache and Disk
        $this->saveAnalytics($period, $analytics);

        Log::info("Generated {$period} analytics successfully.", [
            'period' => $period,
            'total_jobs' => $totalJobs,
            'ai_proposals' => $totalAiProposals,
        ]);

        return $analytics;
    }

    /**
     * Generate daily analytics.
     */
    public function generateDaily(): array
    {
        return $this->generate(self::PERIOD_DAILY);
    }

    /**
     * Generate monthly analytics.
     */
    public function generateMonthly(): array
    {
        return $this->generate(self::PERIOD_MONTHLY);
    }

    /**
     * Generate yearly analytics.
     */
    public function generateYearly(): array
    {
        return $this->generate(self::PERIOD_YEARLY);
    }

    /**
     * Get the latest cached analytics for a given period.
     */
    public function getLatest(string $period): ?array
    {
        $cached = Cache::get("upwork_analytics_{$period}");
        if ($cached) {
            return $cached;
        }

        // Try reading from disk
        $path = "analytics/{$period}_latest.json";
        if (Storage::disk('local')->exists($path)) {
            $json = Storage::disk('local')->get($path);
            $data = json_decode($json, true);
            if (is_array($data)) {
                Cache::forever("upwork_analytics_{$period}", $data);
                return $data;
            }
        }

        return null;
    }

    /**
     * Get all latest cached analytics (daily, monthly, yearly).
     */
    public function getAllLatest(): array
    {
        return [
            self::PERIOD_DAILY => $this->getLatest(self::PERIOD_DAILY),
            self::PERIOD_MONTHLY => $this->getLatest(self::PERIOD_MONTHLY),
            self::PERIOD_YEARLY => $this->getLatest(self::PERIOD_YEARLY),
        ];
    }

    /**
     * Persist analytics to cache and storage.
     */
    protected function saveAnalytics(string $period, array $data): void
    {
        // Cache indefinitely
        Cache::forever("upwork_analytics_{$period}", $data);

        // Save latest snapshot to storage
        try {
            Storage::disk('local')->put("analytics/{$period}_latest.json", json_encode($data, JSON_PRETTY_PRINT));
            // Also store timestamped historical snapshot
            $timestamp = now()->format('Y-m-d_His');
            Storage::disk('local')->put("analytics/history/{$period}_{$timestamp}.json", json_encode($data, JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            Log::warning("Could not persist analytics to disk: " . $e->getMessage());
        }
    }

    /**
     * Calculate start and end Carbon dates based on period.
     */
    protected function getPeriodBoundaries(string $period): array
    {
        $now = now();

        switch ($period) {
            case self::PERIOD_MONTHLY:
                return [
                    'label' => 'Monthly (' . $now->format('F Y') . ')',
                    'start_date' => $now->copy()->startOfMonth(),
                    'end_date' => $now->copy(),
                    'prev_start_date' => $now->copy()->subMonth()->startOfMonth(),
                    'prev_end_date' => $now->copy()->subMonth()->endOfMonth(),
                ];

            case self::PERIOD_YEARLY:
                return [
                    'label' => 'Yearly (' . $now->format('Y') . ')',
                    'start_date' => $now->copy()->startOfYear(),
                    'end_date' => $now->copy(),
                    'prev_start_date' => $now->copy()->subYear()->startOfYear(),
                    'prev_end_date' => $now->copy()->subYear()->endOfYear(),
                ];

            case self::PERIOD_DAILY:
            default:
                return [
                    'label' => 'Daily (Last 24 Hours / ' . $now->format('M d, Y') . ')',
                    'start_date' => $now->copy()->subDay(),
                    'end_date' => $now->copy(),
                    'prev_start_date' => $now->copy()->subDays(2),
                    'prev_end_date' => $now->copy()->subDay(),
                ];
        }
    }
}
