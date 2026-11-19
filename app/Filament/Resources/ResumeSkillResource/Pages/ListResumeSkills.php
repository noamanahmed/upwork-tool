<?php

namespace App\Filament\Resources\ResumeSkillResource\Pages;

use App\Filament\Resources\ResumeSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResumeSkills extends ListRecords
{
    protected static string $resource = ResumeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
