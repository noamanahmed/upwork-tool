<?php

namespace App\Filament\Widgets;

use App\Models\AiJobProposal;
use App\Models\Job;
use App\Models\JobSearch;
use App\Models\Proposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalJobs = Job::count();
        $totalSearches = JobSearch::count();
        $totalProposals = Proposal::count();
        $totalAiProposals = AiJobProposal::count();
        $jobsToday = Job::whereDate('created_at', today())->count();
        $jobsPendingSlack = Job::where('is_slack_webhook_sent', false)->count();

        return [
            Stat::make('Total Jobs', number_format($totalJobs))
                ->description("{$jobsToday} new today")
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            Stat::make('Active Searches', number_format($totalSearches))
                ->description('Job search configurations')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('success'),

            Stat::make('AI Proposals', number_format($totalAiProposals))
                ->description('Generated proposals')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),

            Stat::make('Pending Slack', number_format($jobsPendingSlack))
                ->description('Jobs not yet notified')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('danger'),

            Stat::make('Submitted Proposals', number_format($totalProposals))
                ->description('Upwork proposals submitted')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('info'),
        ];
    }
}
