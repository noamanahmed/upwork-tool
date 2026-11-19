<?php

namespace App\Filament\Resources\ResumeHobbyResource\Pages;

use App\Filament\Resources\ResumeHobbyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeHobby extends EditRecord
{
    protected static string $resource = ResumeHobbyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
