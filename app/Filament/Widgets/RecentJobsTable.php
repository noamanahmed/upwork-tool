<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentJobsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Jobs';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Job::query()
                    ->withCount('aiProposals')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('budget_minimum')
                    ->label('Budget')
                    ->formatStateUsing(function ($state, Job $record) {
                        $max = $record->budget_maximum;
                        if ($state === $max) {
                            return '$' . number_format($state);
                        }
                        return '$' . number_format($state) . ' - $' . number_format($max);
                    }),
                Tables\Columns\TextColumn::make('is_hourly')
                    ->label('Type')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Hourly' : 'Fixed')
                    ->badge()
                    ->color(fn (bool $state) => $state ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('ai_proposals_count')
                    ->label('AI Proposals')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fetched')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Recent Jobs');
    }
}
