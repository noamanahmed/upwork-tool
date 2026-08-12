<?php

namespace App\Filament\Resources;

use App\Enums\JobSearchStatusEnum;
use App\Filament\Resources\JobSearchResource\Pages;
use App\Models\JobSearch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JobSearchResource extends Resource
{
    protected static ?string $model = JobSearch::class;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?string $navigationGroup = 'Upwork';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('q')
                            ->label('Search Query')
                            ->maxLength(255)
                            ->helperText('The search keywords for Upwork'),
                        Forms\Components\TextInput::make('sort')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('category')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options(JobSearchStatusEnum::class)
                            ->default(JobSearchStatusEnum::ACTIVE)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Job Type Filters')
                    ->schema([
                        Forms\Components\Toggle::make('is_job_type_hourly')
                            ->label('Hourly Jobs')
                            ->live(),
                        Forms\Components\TextInput::make('hourly_rate_minimum')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Forms\Get $get) => $get('is_job_type_hourly')),
                        Forms\Components\TextInput::make('hourly_rate_maximum')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Forms\Get $get) => $get('is_job_type_hourly')),
                        Forms\Components\Toggle::make('is_job_type_fixed')
                            ->label('Fixed Rate Jobs')
                            ->live(),
                        Forms\Components\TextInput::make('fixed_rate_minimum')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Forms\Get $get) => $get('is_job_type_fixed')),
                        Forms\Components\TextInput::make('fixed_rate_maximum')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (Forms\Get $get) => $get('is_job_type_fixed')),
                    ])->columns(2),

                Forms\Components\Section::make('Client Filters')
                    ->schema([
                        Forms\Components\Toggle::make('is_payment_verified')
                            ->label('Payment Verified'),
                        Forms\Components\Toggle::make('is_previous_client')
                            ->label('Previous Client'),
                        Forms\Components\TextInput::make('client_previous_hired_minimum')
                            ->numeric()
                            ->label('Min Previous Hires'),
                        Forms\Components\TextInput::make('client_previous_hired_maximum')
                            ->numeric()
                            ->label('Max Previous Hires'),
                    ])->columns(2),

                Forms\Components\Section::make('Proposal & Connect Filters')
                    ->schema([
                        Forms\Components\TextInput::make('proposals_minimum')
                            ->numeric()
                            ->label('Min Proposals'),
                        Forms\Components\TextInput::make('proposals_maximum')
                            ->numeric()
                            ->label('Max Proposals'),
                        Forms\Components\TextInput::make('connect_required_minimum')
                            ->numeric()
                            ->label('Min Connects'),
                        Forms\Components\TextInput::make('connect_required_maximum')
                            ->numeric()
                            ->label('Max Connects'),
                    ])->columns(2),

                Forms\Components\Section::make('Location & Schedule')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('timezone')
                            ->maxLength(255),
                        Forms\Components\Select::make('duration_type')
                            ->options([
                                'short' => 'Short Term',
                                'medium' => 'Medium Term',
                                'long' => 'Long Term',
                            ]),
                        Forms\Components\Select::make('workload_type')
                            ->options([
                                'as_needed' => 'As Needed',
                                'part_time' => 'Part Time',
                                'full_time' => 'Full Time',
                            ]),
                        Forms\Components\Select::make('experience_level')
                            ->options([
                                'entry' => 'Entry Level',
                                'intermediate' => 'Intermediate',
                                'expert' => 'Expert',
                            ]),
                        Forms\Components\Toggle::make('is_contract_to_hire')
                            ->label('Contract to Hire'),
                    ])->columns(2),

                Forms\Components\Section::make('Notifications')
                    ->schema([
                        Forms\Components\TextInput::make('slack_webhook_url')
                            ->url()
                            ->maxLength(255)
                            ->label('Slack Webhook URL'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('q')
                    ->label('Query')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_job_type_hourly')
                    ->label('Hourly')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_job_type_fixed')
                    ->label('Fixed Rate')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jobs_count')
                    ->label('Jobs Found')
                    ->counts('jobs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(JobSearchStatusEnum::class),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'email'),
                Tables\Filters\Filter::make('has_slack_webhook')
                    ->label('Has Slack Webhook')
                    ->query(fn ($query) => $query->whereNotNull('slack_webhook_url')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobSearches::route('/'),
            'create' => Pages\CreateJobSearch::route('/create'),
            'edit' => Pages\EditJobSearch::route('/{record}/edit'),
        ];
    }
}
