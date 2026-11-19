<?php

namespace App\Filament\Resources\ResumeCertificationResource\Pages;

use App\Filament\Resources\ResumeCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeCertifications extends ListRecords
{
    protected static string $resource = ResumeCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
