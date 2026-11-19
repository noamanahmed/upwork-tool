<?php

namespace App\Filament\Resources\ResumeProjectResource\Pages;

use App\Filament\Resources\ResumeProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeProjects extends ListRecords
{
    protected static string $resource = ResumeProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
