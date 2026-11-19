<?php

namespace App\Filament\Resources\ResumeHobbyResource\Pages;

use App\Filament\Resources\ResumeHobbyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeHobbies extends ListRecords
{
    protected static string $resource = ResumeHobbyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
