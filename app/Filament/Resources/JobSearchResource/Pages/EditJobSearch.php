<?php

namespace App\Filament\Resources\JobSearchResource\Pages;

use App\Filament\Resources\JobSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJobSearch extends EditRecord
{
    protected static string $resource = JobSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
