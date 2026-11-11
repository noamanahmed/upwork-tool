<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class GenerateMonthlyAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly Upwork analytics (jobs, searches, client spend, AI proposals)';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsService $service): int
    {
        $this->info('Generating Monthly Upwork Analytics...');

        $data = $service->generateMonthly();

        $metrics = $data['metrics'];

        $this->table(
            ['Metric', 'Value'],
            [
                ['Period', $data['period_label']],
                ['Total Jobs', number_format($metrics['total_jobs'])],
                ['Job Growth vs Prev Month', $metrics['job_growth_percent'] . '%'],
                ['Hourly Jobs', number_format($metrics['hourly_jobs']) . ' (Avg: $' . number_format($metrics['avg_hourly_rate'], 2) . '/hr)'],
                ['Fixed Rate Jobs', number_format($metrics['fixed_jobs']) . ' (Avg: $' . number_format($metrics['avg_fixed_budget'], 2) . ')'],
                ['Total Client Spend', '$' . number_format($metrics['total_client_spent'], 2)],
                ['Total Client Hires', number_format($metrics['total_client_hires'])],
                ['Average Client Rating', '★ ' . number_format($metrics['avg_client_feedback'], 2)],
                ['Payment Verified Jobs', number_format($metrics['verified_payment_jobs']) . " ({$metrics['verified_payment_percentage']}%)"],
                ['AI Proposals Generated', number_format($metrics['total_ai_proposals'])],
                ['Submitted Upwork Proposals', number_format($metrics['total_submitted_proposals'])],
            ]
        );

        if (!empty($data['searches_breakdown'])) {
            $this->line('');
            $this->info('Monthly Jobs Breakdown by Search:');
            $searchRows = array_map(fn ($s) => [$s['search_name'], $s['jobs_count']], $data['searches_breakdown']);
            $this->table(['Search Name', 'Jobs Count'], $searchRows);
        }

        $this->info('Monthly analytics generated and cached successfully.');

        return Command::SUCCESS;
    }
}
