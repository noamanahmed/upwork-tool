<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use App\Models\AiJobProposal;
use App\Models\Job;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class ViewJobProposal extends Page
{
    use InteractsWithRecord;

    protected static string $resource = JobResource::class;

    protected static string $view = 'filament.resources.job-resource.pages.view-job-proposal';

    public Job $job;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->job = $this->record;
    }

    public function getTitle(): string
    {
        return 'AI Proposals: ' . $this->record->title;
    }

    public function getViewData(): array
    {
        $enabledProviders = config('services.ai.enabled_providers', []);
        $availableProviders = config('services.ai.available_providers', []);

        $proposals = AiJobProposal::where('job_id', $this->record->id)
            ->whereIn('provider', $availableProviders)
            ->orderByDesc('created_at')
            ->get()
            ->keyBy('provider');

        $visibleTabs = collect($availableProviders)->filter(function ($p) use ($enabledProviders, $proposals) {
            return in_array($p, $enabledProviders) || $proposals->has($p);
        })->values();

        return [
            'job' => $this->record,
            'proposals' => $proposals,
            'enabledProviders' => $enabledProviders,
            'visibleTabs' => $visibleTabs,
        ];
    }
}
