<?php

namespace App\Filament\Resources\ResumeLanguageResource\Pages;

use App\Filament\Resources\ResumeLanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeLanguage extends EditRecord
{
    protected static string $resource = ResumeLanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
