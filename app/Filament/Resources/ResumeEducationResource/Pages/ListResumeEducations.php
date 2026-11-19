<?php

namespace App\Filament\Resources\ResumeEducationResource\Pages;

use App\Filament\Resources\ResumeEducationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeEducations extends ListRecords
{
    protected static string $resource = ResumeEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
