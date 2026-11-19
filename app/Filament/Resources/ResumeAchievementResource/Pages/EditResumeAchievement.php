<?php

namespace App\Filament\Resources\ResumeAchievementResource\Pages;

use App\Filament\Resources\ResumeAchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeAchievement extends EditRecord
{
    protected static string $resource = ResumeAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
