<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\AiJobProposal;
use App\Models\Job;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Upwork';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                TextColumn::make('upwork_id')
                    ->label('Upwork ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('budget_minimum')
                    ->label('Budget')
                    ->formatStateUsing(function ($state, Job $record) {
                        $max = $record->budget_maximum;
                        if ($state === $max) {
                            return '$' . number_format($state);
                        }
                        return '$' . number_format($state) . ' - $' . number_format($max);
                    })
                    ->sortable(),
                TextColumn::make('is_hourly')
                    ->label('Type')
                    ->formatStateUsing(fn (bool $state) => $state ? 'Hourly' : 'Fixed')
                    ->badge()
                    ->color(fn (bool $state) => $state ? 'info' : 'success')
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('client_total_hires')
                    ->label('Client Hires')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('client_total_spent')
                    ->label('Client Spend')
                    ->formatStateUsing(function ($state, Job $record) {
                        $currency = $record->client_total_spent_currency ?? 'USD';
                        return number_format($state) . ' ' . $currency;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_payment_verified')
                    ->label('Payment Verified')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_slack_webhook_sent')
                    ->label('Slack Sent')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('ai_proposals_count')
                    ->label('AI Proposals')
                    ->counts('aiProposals')
                    ->sortable(),
                TextColumn::make('searches_count')
                    ->label('Matched Searches')
                    ->counts('searches')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_hourly')
                    ->options([
                        true => 'Hourly',
                        false => 'Fixed Rate',
                    ]),
                SelectFilter::make('is_payment_verified')
                    ->options([
                        true => 'Verified',
                        false => 'Not Verified',
                    ]),
                SelectFilter::make('is_slack_webhook_sent')
                    ->label('Slack Notified')
                    ->options([
                        true => 'Sent',
                        false => 'Not Sent',
                    ]),
                Tables\Filters\Filter::make('has_ai_proposal')
                    ->label('Has AI Proposal')
                    ->query(fn ($query) => $query->whereHas('aiProposals')),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from'),
                        \Filament\Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Job Details')
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                        TextEntry::make('upwork_id')
                            ->label('Upwork ID'),
                        TextEntry::make('ciphertext')
                            ->label('Ciphertext')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->dateTime(),
                    ])->columns(2),

                Infolists\Components\Section::make('Budget & Type')
                    ->schema([
                        TextEntry::make('budget_range')
                            ->label('Budget')
                            ->state(function (Job $record) {
                                $min = $record->budget_minimum;
                                $max = $record->budget_maximum;
                                if ($min === $max) {
                                    return '$' . number_format($min);
                                }
                                return '$' . number_format($min) . ' - $' . number_format($max);
                            }),
                        TextEntry::make('is_hourly')
                            ->label('Job Type')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Hourly' : 'Fixed Rate')
                            ->badge()
                            ->color(fn (bool $state) => $state ? 'info' : 'success'),
                        TextEntry::make('location'),
                        TextEntry::make('is_payment_verified')
                            ->label('Payment Verified')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                        TextEntry::make('is_slack_webhook_sent')
                            ->label('Slack Notification Sent')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Sent' : 'Pending')
                            ->badge()
                            ->color(fn (bool $state) => $state ? 'success' : 'warning'),
                    ])->columns(2),

                Infolists\Components\Section::make('Client Information')
                    ->schema([
                        TextEntry::make('client_total_hires')
                            ->label('Total Hires'),
                        TextEntry::make('client_total_posted_jobs')
                            ->label('Total Posted Jobs'),
                        TextEntry::make('client_total_reviews')
                            ->label('Total Reviews'),
                        TextEntry::make('client_total_feedback')
                            ->label('Feedback Score'),
                        TextEntry::make('client_total_spent')
                            ->label('Total Spent')
                            ->formatStateUsing(function ($state, Job $record) {
                                $currency = $record->client_total_spent_currency ?? 'USD';
                                return number_format((float) $state) . ' ' . $currency;
                            }),
                    ])->columns(2),

                Infolists\Components\Section::make('AI Proposals')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('aiProposals')
                            ->schema([
                                TextEntry::make('provider')
                                    ->badge(),
                                TextEntry::make('model')
                                    ->label('Model'),
                                TextEntry::make('status'),
                                TextEntry::make('generated_at')
                                    ->label('Generated At')
                                    ->dateTime(),
                                TextEntry::make('proposal')
                                    ->label('Proposal')
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),

                Infolists\Components\Section::make('Matched Searches')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('searches')
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('q')
                                    ->label('Query'),
                                TextEntry::make('slack_webhook_url')
                                    ->label('Slack Webhook'),
                                TextEntry::make('pivot.is_slack_webhook_sent')
                                    ->label('Slack Sent')
                                    ->formatStateUsing(fn (bool $state) => $state ? 'Sent' : 'Pending')
                                    ->badge()
                                    ->color(fn (bool $state) => $state ? 'success' : 'warning'),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'view' => Pages\ViewJob::route('/{record}'),
        ];
    }
}
