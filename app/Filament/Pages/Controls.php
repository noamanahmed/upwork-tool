<?php

namespace App\Filament\Pages;

use App\Models\Job;
use App\Models\JobSearch;
use App\Models\Setting;
use App\Services\AnalyticsService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class Controls extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationGroup = 'Controls';

    protected static ?string $navigationLabel = 'Debug Controls';

    protected static ?string $title = 'Controls & Debug Actions';

    protected static ?string $slug = 'controls';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.controls';

    public string $activeTab = 'daily';

    public ?string $lastCommandOutput = null;

    public ?string $lastExecutedAction = null;

    public array $dailyAnalytics = [];

    public array $monthlyAnalytics = [];

    public array $yearlyAnalytics = [];

    public int $totalJobsCount = 0;

    public int $activeSearchesCount = 0;

    public string $currentAiProvider = 'gemini';

    public bool $isCronEnabled = true;

    public string $cronWindow = '21:00 - 03:00';

    public function mount(): void
    {
        $this->refreshAnalyticsData();
        $this->refreshSystemStatus();
    }

    public function refreshSystemStatus(): void
    {
        try {
            $this->totalJobsCount = Job::count();
            $this->activeSearchesCount = JobSearch::where('status', \App\Enums\JobSearchStatusEnum::ACTIVE->value)->count();
            $this->currentAiProvider = Setting::get('AI_PROVIDER') ?? config('services.ai.default', 'gemini');
            $this->isCronEnabled = Setting::getBool('UPWORK_CRON_ENABLED', true);
            $this->cronWindow = (Setting::get('UPWORK_CRON_START') ?? '21:00') . ' - ' . (Setting::get('UPWORK_CRON_END') ?? '03:00');
        } catch (\Throwable $e) {
            // Graceful fallback if database tables unready
        }
    }

    public function refreshAnalyticsData(): void
    {
        $service = app(AnalyticsService::class);
        $all = $service->getAllLatest();

        $this->dailyAnalytics = $all[AnalyticsService::PERIOD_DAILY] ?? [];
        $this->monthlyAnalytics = $all[AnalyticsService::PERIOD_MONTHLY] ?? [];
        $this->yearlyAnalytics = $all[AnalyticsService::PERIOD_YEARLY] ?? [];
    }

    public function refreshAll(): void
    {
        $this->refreshAnalyticsData();
        $this->refreshSystemStatus();

        Notification::make()
            ->title('Status Refreshed')
            ->body('System status and analytics data reloaded.')
            ->success()
            ->send();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Clear terminal / console output.
     */
    public function clearTerminalOutput(): void
    {
        $this->lastCommandOutput = null;
        $this->lastExecutedAction = null;
    }

    /**
     * Trigger Daily Analytics command.
     */
    public function triggerDailyAnalytics(): void
    {
        Artisan::call('analytics:daily');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'analytics:daily' successfully.";
        $this->lastExecutedAction = 'analytics:daily';
        $this->activeTab = 'daily';

        $this->dailyAnalytics = app(AnalyticsService::class)->getLatest(AnalyticsService::PERIOD_DAILY) ?? [];

        Notification::make()
            ->title('Daily Analytics Generated')
            ->body('Successfully computed daily analytics snapshot.')
            ->success()
            ->send();
    }

    /**
     * Trigger Monthly Analytics command.
     */
    public function triggerMonthlyAnalytics(): void
    {
        Artisan::call('analytics:monthly');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'analytics:monthly' successfully.";
        $this->lastExecutedAction = 'analytics:monthly';
        $this->activeTab = 'monthly';

        $this->monthlyAnalytics = app(AnalyticsService::class)->getLatest(AnalyticsService::PERIOD_MONTHLY) ?? [];

        Notification::make()
            ->title('Monthly Analytics Generated')
            ->body('Successfully computed monthly analytics snapshot.')
            ->success()
            ->send();
    }

    /**
     * Trigger Yearly Analytics command.
     */
    public function triggerYearlyAnalytics(): void
    {
        Artisan::call('analytics:yearly');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'analytics:yearly' successfully.";
        $this->lastExecutedAction = 'analytics:yearly';
        $this->activeTab = 'yearly';

        $this->yearlyAnalytics = app(AnalyticsService::class)->getLatest(AnalyticsService::PERIOD_YEARLY) ?? [];

        Notification::make()
            ->title('Yearly Analytics Generated')
            ->body('Successfully computed yearly analytics snapshot.')
            ->success()
            ->send();
    }

    /**
     * Trigger All Analytics command.
     */
    public function triggerAllAnalytics(): void
    {
        Artisan::call('analytics:generate', ['period' => 'all']);
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'analytics:generate all' successfully.";
        $this->lastExecutedAction = 'analytics:generate all';

        $this->refreshAnalyticsData();

        Notification::make()
            ->title('All Analytics Generated')
            ->body('Successfully generated daily, monthly, and yearly analytics snapshots.')
            ->success()
            ->send();
    }

    /**
     * Debug: Run Upwork Job Search.
     */
    public function triggerJobSearch(): void
    {
        Artisan::call('upwork:search');
        $this->lastCommandOutput = Artisan::output() ?: "Dispatched 'upwork:search' successfully.";
        $this->lastExecutedAction = 'upwork:search';

        Notification::make()
            ->title('Job Search Dispatched')
            ->body("Dispatched background queued jobs for all active job searches.")
            ->info()
            ->send();
    }

    /**
     * Debug: Send Slack Notifications.
     */
    public function triggerSlackNotifications(): void
    {
        Artisan::call('upwork:send-slack-notifications');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'upwork:send-slack-notifications'.";
        $this->lastExecutedAction = 'upwork:send-slack-notifications';

        Notification::make()
            ->title('Slack Notifications Sent')
            ->body('Pending Slack notifications have been processed.')
            ->info()
            ->send();
    }

    /**
     * Debug: Generate AI Proposals.
     */
    public function triggerAiProposals(): void
    {
        Artisan::call('upwork:generate-ai-proposals');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'upwork:generate-ai-proposals'.";
        $this->lastExecutedAction = 'upwork:generate-ai-proposals';

        Notification::make()
            ->title('AI Proposal Generation Dispatched')
            ->body('Dispatched AI proposal jobs for enabled providers.')
            ->info()
            ->send();
    }

    /**
     * Debug: Sync proposals statuses from Upwork.
     */
    public function triggerFetchProposals(): void
    {
        Artisan::call('upwork:proposals');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'upwork:proposals' successfully.";
        $this->lastExecutedAction = 'upwork:proposals';

        Notification::make()
            ->title('Proposals Sync Dispatched')
            ->body('Fetching proposal statuses from Upwork API.')
            ->info()
            ->send();
    }

    /**
     * Debug: Backfill proposal jobs.
     */
    public function triggerFetchProposalJobs(): void
    {
        Artisan::call('upwork:proposal-jobs');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'upwork:proposal-jobs' successfully.";
        $this->lastExecutedAction = 'upwork:proposal-jobs';

        Notification::make()
            ->title('Proposal Jobs Sync Dispatched')
            ->body('Backfilling missing job data for tracked proposals.')
            ->info()
            ->send();
    }

    /**
     * Debug: Prune Telescope entries.
     */
    public function triggerPruneTelescope(): void
    {
        Artisan::call('upwork:prune-telescope');
        $this->lastCommandOutput = Artisan::output() ?: "Executed 'upwork:prune-telescope'.";
        $this->lastExecutedAction = 'upwork:prune-telescope';

        Notification::make()
            ->title('Telescope Pruned')
            ->body('Old Telescope entries pruned.')
            ->info()
            ->send();
    }

    /**
     * Debug: Clear System Cache.
     */
    public function triggerClearCache(): void
    {
        Setting::clearCache();
        Artisan::call('cache:clear');
        $this->lastCommandOutput = Artisan::output() ?: "Application cache and settings cache cleared successfully.";
        $this->lastExecutedAction = 'cache:clear';

        $this->refreshSystemStatus();

        Notification::make()
            ->title('System Cache Cleared')
            ->body('Application cache and setting runtime store cleared.')
            ->success()
            ->send();
    }
}
