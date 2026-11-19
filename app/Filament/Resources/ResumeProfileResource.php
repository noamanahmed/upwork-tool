<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeProfileResource\Pages;
use App\Models\ResumeProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeProfileResource extends Resource
{
    protected static ?string $model = ResumeProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Profile';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('first_name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('last_name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('title')
                                ->label('Professional Title')
                                ->placeholder('e.g. Senior Full Stack Developer')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('experience_years')
                                ->label('Years of Experience')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(50)
                                ->default(0),
                        ]),

                        Forms\Components\Textarea::make('summary')
                            ->label('Professional Summary / Bio')
                            ->placeholder('Brief overview of skills and experience...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Location & Timezone')
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\TextInput::make('city')->maxLength(255),
                            Forms\Components\TextInput::make('state')->maxLength(255),
                            Forms\Components\TextInput::make('country')->maxLength(255),
                            Forms\Components\TextInput::make('timezone')->placeholder('Asia/Karachi')->maxLength(255),
                        ]),
                    ]),

                Forms\Components\Section::make('Online Presence & Status')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('github_url')->label('GitHub URL')->url()->maxLength(255),
                            Forms\Components\TextInput::make('linkedin_url')->label('LinkedIn URL')->url()->maxLength(255),
                            Forms\Components\TextInput::make('website_url')->label('Personal Website')->url()->maxLength(255),
                        ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Profile')
                            ->helperText('Active profile used by default for AI proposal context.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name']),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location'),
                Tables\Columns\TextColumn::make('experience_years')
                    ->label('Experience')
                    ->suffix(' yrs')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
            'index' => Pages\ListResumeProfiles::route('/'),
            'create' => Pages\CreateResumeProfile::route('/create'),
            'edit' => Pages\EditResumeProfile::route('/{record}/edit'),
        ];
    }
}
