<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeHobbyResource\Pages;
use App\Models\ResumeHobby;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeHobbyResource extends Resource
{
    protected static ?string $model = ResumeHobby::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Hobbies & Interests';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Hobby Details')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Hobby / Interest')
                                ->placeholder('e.g. Football, Chess, Traveling')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('icon')
                                ->placeholder('e.g. heroicon-o-trophy')
                                ->maxLength(255),

                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                        ]),

                        Forms\Components\Textarea::make('description')
                            ->placeholder('Brief description or personal touch...')
                            ->rows(2)
                            ->columnSpanFull(),

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
                Tables\Columns\TextColumn::make('description')
                    ->limit(40)
                    ->placeholder('N/A'),
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
            'index' => Pages\ListResumeHobbies::route('/'),
            'create' => Pages\CreateResumeHobby::route('/create'),
            'edit' => Pages\EditResumeHobby::route('/{record}/edit'),
        ];
    }
}
