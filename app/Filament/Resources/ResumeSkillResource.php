<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeSkillResource\Pages;
use App\Models\ResumeSkill;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeSkillResource extends Resource
{
    protected static ?string $model = ResumeSkill::class;

    protected static ?string $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Skills';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Skill Details')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Skill Name')
                                ->required()
                                ->placeholder('e.g. Laravel, Vue.js, Docker')
                                ->maxLength(255),
                            Forms\Components\Select::make('category')
                                ->options([
                                    'backend' => 'Backend',
                                    'frontend' => 'Frontend',
                                    'databases' => 'Databases',
                                    'devops' => 'DevOps & Cloud',
                                    'cms' => 'CMS & WordPress',
                                    'infrastructure' => 'Infrastructure & Servers',
                                    'advanced' => 'Advanced & Architecture',
                                    'general' => 'General',
                                ])
                                ->default('backend')
                                ->required(),
                            Forms\Components\Select::make('proficiency')
                                ->options([
                                    'beginner' => 'Beginner',
                                    'intermediate' => 'Intermediate',
                                    'advanced' => 'Advanced',
                                    'expert' => 'Expert',
                                ])
                                ->default('intermediate')
                                ->required(),
                        ]),

                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\TextInput::make('years_of_experience')
                                ->label('Years of Experience')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(50),
                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                            Forms\Components\Toggle::make('is_featured')
                                ->label('Featured Skill')
                                ->default(false),
                        ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active in Resume')
                            ->helperText('Active skills are matched dynamically for proposal generation.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Skill')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'backend' => 'primary',
                        'frontend' => 'info',
                        'devops', 'infrastructure' => 'warning',
                        'databases' => 'danger',
                        'cms' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('proficiency')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'expert' => 'success',
                        'advanced' => 'primary',
                        'intermediate' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'backend' => 'Backend',
                        'frontend' => 'Frontend',
                        'databases' => 'Databases',
                        'devops' => 'DevOps',
                        'cms' => 'CMS',
                        'infrastructure' => 'Infrastructure',
                        'advanced' => 'Advanced',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
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
            'index' => Pages\ListResumeSkills::route('/'),
            'create' => Pages\CreateResumeSkill::route('/create'),
            'edit' => Pages\EditResumeSkill::route('/{record}/edit'),
        ];
    }
}
