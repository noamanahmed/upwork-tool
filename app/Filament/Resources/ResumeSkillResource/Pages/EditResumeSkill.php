<?php

namespace App\Filament\Resources\ResumeSkillResource\Pages;

use App\Filament\Resources\ResumeSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResumeSkill extends EditRecord
{
    protected static string $resource = ResumeSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
