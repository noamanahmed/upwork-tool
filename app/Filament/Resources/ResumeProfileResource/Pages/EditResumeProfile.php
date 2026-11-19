<?php

namespace App\Filament\Resources\ResumeProfileResource\Pages;

use App\Filament\Resources\ResumeProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeProfile extends EditRecord
{
    protected static string $resource = ResumeProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
