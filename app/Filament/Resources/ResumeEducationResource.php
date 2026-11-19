<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeEducationResource\Pages;
use App\Models\ResumeEducation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeEducationResource extends Resource
{
    protected static ?string $model = ResumeEducation::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Education';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Degree & Institution')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('degree')
                                ->label('Degree / Qualification')
                                ->placeholder('e.g. Bachelor of Science')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('field_of_study')
                                ->label('Field of Study')
                                ->placeholder('e.g. Computer Science')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('institution')
                                ->label('School / University')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('Start Date'),
                            Forms\Components\DatePicker::make('end_date')
                                ->label('End Date')
                                ->disabled(fn (Get $get) => (bool) $get('is_current')),
                            Forms\Components\Toggle::make('is_current')
                                ->label('Currently Studying')
                                ->live()
                                ->afterStateUpdated(function (Forms\Set $set, $state) {
                                    if ($state) {
                                        $set('end_date', null);
                                    }
                                }),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('grade')
                                ->placeholder('e.g. 3.8 GPA or Grade A')
                                ->maxLength(255),
                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($r) => "{$r->first_name} {$r->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Activities / Description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active in Resume')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('degree')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('field_of_study')
                    ->searchable(),
                Tables\Columns\TextColumn::make('institution')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->date('Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->formatStateUsing(fn ($record) => $record->is_current ? 'Present' : ($record->end_date ? $record->end_date->format('Y') : 'N/A')),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
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
            'index' => Pages\ListResumeEducations::route('/'),
            'create' => Pages\CreateResumeEducation::route('/create'),
            'edit' => Pages\EditResumeEducation::route('/{record}/edit'),
        ];
    }
}
