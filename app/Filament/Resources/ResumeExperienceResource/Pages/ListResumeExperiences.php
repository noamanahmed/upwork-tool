<?php

namespace App\Filament\Resources\ResumeExperienceResource\Pages;

use App\Filament\Resources\ResumeExperienceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeExperiences extends ListRecords
{
    protected static string $resource = ResumeExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
