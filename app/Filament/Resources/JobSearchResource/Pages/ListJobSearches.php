<?php

namespace App\Filament\Resources\JobSearchResource\Pages;

use App\Filament\Resources\JobSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobSearches extends ListRecords
{
    protected static string $resource = JobSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
