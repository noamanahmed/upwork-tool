<?php

namespace App\Filament\Resources\ResumeProjectResource\Pages;

use App\Filament\Resources\ResumeProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeProject extends EditRecord
{
    protected static string $resource = ResumeProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
