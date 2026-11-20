<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeProjectResource\Pages;
use App\Models\ResumeProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeProjectResource extends Resource
{
    protected static ?string $model = ResumeProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Projects & Case Studies';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Overview')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title')
                                ->label('Project / Case Study Title')
                                ->placeholder('e.g. HIPAA-Compliant Healthcare Platform')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('client_name')
                                ->label('Client / Organization Name')
                                ->placeholder('e.g. Shuttle Health')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('project_url')
                                ->label('Project Live URL / Repo')
                                ->url()
                                ->maxLength(255),
                            Forms\Components\Select::make('profile_id')
                                ->relationship('profile', 'first_name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                                ->default(fn () => \App\Models\ResumeProfile::first()?->id),
                            Forms\Components\TextInput::make('sort_order')
                                ->numeric()
                                ->default(0),
                        ]),
                    ]),

                Forms\Components\Section::make('Case Study Narrative (Used for AI Proposals)')
                    ->description('Provide the Problem, Solution, and Result. AI proposals cite these when matching job requirements.')
                    ->schema([
                        Forms\Components\Textarea::make('problem')
                            ->label('Client Challenge / Problem')
                            ->placeholder('What problem did the client face?')
                            ->rows(3),

                        Forms\Components\Textarea::make('solution')
                            ->label('Technical Implementation / Solution')
                            ->placeholder('What architecture or code solution did you build?')
                            ->rows(3),

                        Forms\Components\Textarea::make('result')
                            ->label('Outcome / Measurable Result')
                            ->placeholder('e.g. Delivered 90% TTFB reduction, scaled to 100k users...')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Technologies & AI Matching Tags')
                    ->schema([
                        Forms\Components\TagsInput::make('tech_stack')
                            ->label('Technologies & Stack')
                            ->placeholder('e.g. Laravel 11, Vue 3, PostgreSQL, AWS')
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('tags')
                            ->label('Matching Keyword Tags')
                            ->placeholder('e.g. healthcare, api, aws, microservices')
                            ->helperText('AI Proposal scanner matches these tags against Upwork job descriptions.')
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
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('client_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->limitList(3),
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
            'index' => Pages\ListResumeProjects::route('/'),
            'create' => Pages\CreateResumeProject::route('/create'),
            'edit' => Pages\EditResumeProject::route('/{record}/edit'),
        ];
    }
}
