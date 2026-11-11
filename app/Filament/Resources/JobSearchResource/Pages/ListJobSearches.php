<?php

namespace App\Filament\Resources\JobSearchResource\Pages;

use App\Enums\JobSearchStatusEnum;
use App\Filament\Resources\JobSearchResource;
use App\Models\JobSearch;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListJobSearches extends ListRecords
{
    protected static string $resource = JobSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('searchAll')
                ->label('Run All Searches')
                ->icon('heroicon-o-magnifying-glass')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Run All Upwork Job Searches')
                ->modalDescription('This will immediately dispatch background queue jobs for all active job searches, bypassing scheduler time window and active days restrictions.')
                ->modalSubmitActionLabel('Dispatch All Now')
                ->action(function () {
                    $searches = JobSearch::query()
                        ->where('status', JobSearchStatusEnum::ACTIVE)
                        ->get();

                    if ($searches->isEmpty()) {
                        $searches = JobSearch::all();
                    }

                    if ($searches->isEmpty()) {
                        Notification::make()
                            ->title('No Job Searches Found')
                            ->body('There are no job searches configured to run.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $count = 0;
                    foreach ($searches as $search) {
                        \App\Jobs\JobSearch::dispatch($search->id)->onQueue('scraping');
                        $count++;
                    }

                    Notification::make()
                        ->title('All Searches Dispatched')
                        ->body("Successfully queued {$count} background job search(es).")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
