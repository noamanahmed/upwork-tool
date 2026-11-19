<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeLanguageResource\Pages;
use App\Models\ResumeLanguage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeLanguageResource extends Resource
{
    protected static ?string $model = ResumeLanguage::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Languages';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Language Details')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Language')
                                ->placeholder('e.g. English, Urdu')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\Select::make('proficiency')
                                ->options([
                                    'Native' => 'Native',
                                    'Fluent' => 'Fluent',
                                    'Professional' => 'Professional Working',
                                    'Intermediate' => 'Intermediate',
                                    'Basic' => 'Basic',
                                ])
                                ->default('Fluent')
                                ->required(),

                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($r) => "{$r->first_name} {$r->last_name}")
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('proficiency')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Native', 'Fluent' => 'success',
                        'Professional' => 'primary',
                        'Intermediate' => 'warning',
                        default => 'gray',
                    }),
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
            'index' => Pages\ListResumeLanguages::route('/'),
            'create' => Pages\CreateResumeLanguage::route('/create'),
            'edit' => Pages\EditResumeLanguage::route('/{record}/edit'),
        ];
    }
}
