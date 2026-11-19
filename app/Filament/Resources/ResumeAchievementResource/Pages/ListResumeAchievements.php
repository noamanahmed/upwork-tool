<?php

namespace App\Filament\Resources\ResumeAchievementResource\Pages;

use App\Filament\Resources\ResumeAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeAchievements extends ListRecords
{
    protected static string $resource = ResumeAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
