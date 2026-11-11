<?php

namespace App\Filament\Pages;

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

    public function mount(): void
    {
        $this->refreshAnalyticsData();
    }

    public function refreshAnalyticsData(): void
    {
        $service = app(AnalyticsService::class);
        $all = $service->getAllLatest();

        $this->dailyAnalytics = $all[AnalyticsService::PERIOD_DAILY] ?? [];
        $this->monthlyAnalytics = $all[AnalyticsService::PERIOD_MONTHLY] ?? [];
        $this->yearlyAnalytics = $all[AnalyticsService::PERIOD_YEARLY] ?? [];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Trigger Daily Analytics command.
     */
    public function triggerDailyAnalytics(): void
    {
        Artisan::call('analytics:daily');
        $this->lastCommandOutput = Artisan::output();
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
        $this->lastCommandOutput = Artisan::output();
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
        $this->lastCommandOutput = Artisan::output();
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
        $this->lastCommandOutput = Artisan::output();
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
     * Debug: Prune Telescope.
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
}
