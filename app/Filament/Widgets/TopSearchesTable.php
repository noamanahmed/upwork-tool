<?php

namespace App\Filament\Widgets;

use App\Models\JobSearch;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSearchesTable extends BaseWidget
{
    protected static ?string $heading = 'Top Job Searches';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobSearch::query()
                    ->withCount('jobs')
                    ->orderByDesc('jobs_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('q')
                    ->label('Query')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobs_count')
                    ->label('Jobs Found')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('slack_webhook_url')
                    ->label('Slack')
                    ->boolean()
                    ->state(fn ($record) => !empty($record->slack_webhook_url)),
            ]);
    }
}
