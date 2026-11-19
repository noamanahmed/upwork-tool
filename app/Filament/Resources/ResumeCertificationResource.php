<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResumeCertificationResource\Pages;
use App\Models\ResumeCertification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResumeCertificationResource extends Resource
{
    protected static ?string $model = ResumeCertification::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Resume & CV';

    protected static ?string $navigationLabel = 'Certifications';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Certification Details')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Certification Name')
                                ->placeholder('e.g. AWS Certified Solutions Architect')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('issuer')
                                ->label('Issuing Organization')
                                ->placeholder('e.g. Amazon Web Services')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\DatePicker::make('issue_date')
                                ->label('Issue Date'),
                            Forms\Components\DatePicker::make('expiry_date')
                                ->label('Expiration Date'),
                            Forms\Components\TextInput::make('credential_id')
                                ->label('Credential ID / License Number')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('credential_url')
                                ->label('Verification URL')
                                ->url()
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
                            ->label('Description / Scope')
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issuer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('issue_date')
                    ->date('M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('credential_url')
                    ->label('Link')
                    ->url(fn ($record) => $record->credential_url, shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->limit(20),
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
            'index' => Pages\ListResumeCertifications::route('/'),
            'create' => Pages\CreateResumeCertification::route('/create'),
            'edit' => Pages\EditResumeCertification::route('/{record}/edit'),
        ];
    }
}
