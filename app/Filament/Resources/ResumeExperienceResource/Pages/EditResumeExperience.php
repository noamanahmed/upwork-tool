<?php

namespace App\Filament\Resources\ResumeExperienceResource\Pages;

use App\Filament\Resources\ResumeExperienceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeExperience extends EditRecord
{
    protected static string $resource = ResumeExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
