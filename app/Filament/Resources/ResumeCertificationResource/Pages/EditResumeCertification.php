<?php

namespace App\Filament\Resources\ResumeCertificationResource\Pages;

use App\Filament\Resources\ResumeCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeCertification extends EditRecord
{
    protected static string $resource = ResumeCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
