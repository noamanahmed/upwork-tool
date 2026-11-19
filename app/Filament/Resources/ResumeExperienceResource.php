<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeExperienceResource\Pages;
use App\Models\ResumeExperience;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeExperienceResource extends Resource
{
    protected static ?string $model = ResumeExperience::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Work Experience';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role & Company')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('role')
                                ->label('Job Title / Role')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('company')
                                ->label('Company Name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('location')
                                ->placeholder('e.g. Remote or City, Country')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('employment_type')
                                ->options([
                                    'Full-time' => 'Full-time',
                                    'Contract' => 'Contract',
                                    'Freelance' => 'Freelance',
                                    'Part-time' => 'Part-time',
                                    'Self-employed' => 'Self-employed',
                                ]),
                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($r) => "{$r->first_name} {$r->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Forms\Components\Section::make('Duration')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Start Date'),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('End Date')
                                ->disabled(fn (Get $get) => (bool) $get('is_current')),
                            Forms\Components\Toggle::make('is_current')
                                ->label('Currently Working Here')
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($state) {
                                        $set('end_date', null);
                                    }
                                }),
                        ]),
                    ]),

                Forms\Components\Section::make('Responsibilities & Highlights')
                    ->schema([
                        Forms\Components\TagsInput::make('highlights')
                            ->label('Key Achievements & Highlights (Bullet Points)')
                            ->placeholder('Add achievement / responsibility and press Enter')
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('technologies')
                            ->label('Technologies Used')
                            ->placeholder('e.g. Laravel, Vue.js, AWS')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active in Resume')
                            ->helperText('Active records are included when populating proposals and CV.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('employment_type')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->formatStateUsing(fn ($record) => $record->is_current ? 'Present' : ($record->end_date ? $record->end_date->format('M Y') : 'N/A')),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('is_current')
                    ->label('Currently Working'),
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
            'index' => Pages\ListResumeExperiences::route('/'),
            'create' => Pages\CreateResumeExperience::route('/create'),
            'edit' => Pages\EditResumeExperience::route('/{record}/edit'),
        ];
    }
}
