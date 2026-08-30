<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Models\Job;
use Filament\Forms;
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
                        return number_format((float) $state) . ' ' . $currency;
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
                SelectFilter::make('searches')
                    ->relationship('searches', 'name')
                    ->label('Search Category'),
                SelectFilter::make('is_hourly')
                    ->label('Job Type')
                    ->options([
                        true => 'Hourly',
                        false => 'Fixed Rate',
                    ]),
                SelectFilter::make('is_payment_verified')
                    ->label('Payment Verified')
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
                SelectFilter::make('proposal_status')
                    ->label('AI Proposal Status')
                    ->options([
                        'none' => 'No Proposal Generated',
                        'completed' => 'Completed',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }
                        if ($data['value'] === 'none') {
                            return $query->whereDoesntHave('aiProposals');
                        }
                        return $query->whereHas('aiProposals', function ($q) use ($data) {
                            $q->where('status', $data['value'])
                              ->whereRaw('ai_job_proposals.id = (SELECT MAX(id) FROM ai_job_proposals WHERE job_id = jobs.id)');
                        });
                    }),
                Tables\Filters\Filter::make('budget')
                    ->form([
                        Forms\Components\TextInput::make('budget_min')->numeric()->label('Min Budget'),
                        Forms\Components\TextInput::make('budget_max')->numeric()->label('Max Budget'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['budget_min'], fn ($q) => $q->where('budget_minimum', '>=', (float) $data['budget_min']))
                            ->when($data['budget_max'], fn ($q) => $q->where('budget_maximum', '<=', (float) $data['budget_max']));
                    }),
                Tables\Filters\Filter::make('applicants')
                    ->form([
                        Forms\Components\TextInput::make('applicants_min')->numeric()->label('Min Applicants'),
                        Forms\Components\TextInput::make('applicants_max')->numeric()->label('Max Applicants'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['applicants_min'], fn ($q) => $q->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(json, '$.node.totalApplicants')) AS UNSIGNED) >= ?", [(int) $data['applicants_min']]))
                            ->when($data['applicants_max'], fn ($q) => $q->whereRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(json, '$.node.totalApplicants')) AS UNSIGNED) <= ?", [(int) $data['applicants_max']]));
                    }),
                Tables\Filters\Filter::make('client_spend')
                    ->form([
                        Forms\Components\TextInput::make('spend_min')->numeric()->label('Min Spend'),
                        Forms\Components\TextInput::make('spend_max')->numeric()->label('Max Spend'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['spend_min'], fn ($q) => $q->where('client_total_spent', '>=', (float) $data['spend_min']))
                            ->when($data['spend_max'], fn ($q) => $q->where('client_total_spent', '<=', (float) $data['spend_max']));
                    }),
                Tables\Filters\Filter::make('client_hires')
                    ->form([
                        Forms\Components\TextInput::make('hires_min')->numeric()->label('Min Hires'),
                        Forms\Components\TextInput::make('hires_max')->numeric()->label('Max Hires'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['hires_min'], fn ($q) => $q->where('client_total_hires', '>=', (int) $data['hires_min']))
                            ->when($data['hires_max'], fn ($q) => $q->where('client_total_hires', '<=', (int) $data['hires_max']));
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Posted From'),
                        Forms\Components\DatePicker::make('created_until')->label('Posted To'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q) => $q->whereDate('created_at', '>=', $data['created_from']))
                            ->when($data['created_until'], fn ($q) => $q->whereDate('created_at', '<=', $data['created_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('proposal')
                    ->label('AI Proposal')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->url(fn (Job $record) => Pages\ViewJobProposal::getUrl(['record' => $record])),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('upwork')
                    ->label('Upwork')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Job $record) => "https://www.upwork.com/jobs/~" . ltrim($record->ciphertext, '~'))
                    ->openUrlInNewTab(),
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
            'proposal' => Pages\ViewJobProposal::route('/{record}/proposal'),
        ];
    }
}
