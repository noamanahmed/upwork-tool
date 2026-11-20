<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeAchievementResource\Pages;
use App\Models\ResumeAchievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeAchievementResource extends Resource
{
    protected static ?string $model = ResumeAchievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Achievements';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Achievement Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Achievement Statement')
                            ->placeholder('e.g. Reduced API response time (TTFB) by 90%')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('category')
                                ->options([
                                    'backend' => 'Backend',
                                    'infrastructure' => 'Infrastructure',
                                    'cms' => 'CMS / WordPress',
                                    'frontend' => 'Frontend',
                                    'general' => 'General',
                                ])
                                ->default('general')
                                ->required(),

                            Forms\Components\TextInput::make('metric')
                                ->label('Measurable Proof / Metric')
                                ->placeholder('e.g. 90% TTFB, 2000+ sites')
                                ->maxLength(255),

                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),

                            Forms\Components\Toggle::make('is_active')
                                ->label('Active in Resume')
                                ->default(true),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'backend' => 'primary',
                        'infrastructure' => 'warning',
                        'cms' => 'success',
                        'frontend' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('metric')
                    ->placeholder('N/A'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'backend' => 'Backend',
                        'infrastructure' => 'Infrastructure',
                        'cms' => 'CMS',
                        'frontend' => 'Frontend',
                        'general' => 'General',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResumeAchievements::route('/'),
            'create' => Pages\CreateResumeAchievement::route('/create'),
            'edit' => Pages\EditResumeAchievement::route('/{record}/edit'),
        ];
    }
}
