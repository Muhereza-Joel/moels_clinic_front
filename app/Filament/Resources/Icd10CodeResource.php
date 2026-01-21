<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Icd10CodeResource\Pages;
use App\Filament\Resources\Icd10CodeResource\RelationManagers;
use App\Models\Icd10Code;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Icd10CodeResource extends Resource
{
    protected static ?string $model = Icd10Code::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* =========================
             * ICD-10 Code Identity
             * ========================= */
                Forms\Components\Section::make('ICD-10 Code')
                    ->description('Core ICD-10 diagnosis identification.')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('ICD-10 Code')
                            ->required()
                            ->placeholder('e.g. I10, E11.9, J18.9')
                            ->helperText('Official ICD-10 diagnosis code')
                            ->maxLength(10),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive codes are hidden from clinical selection')
                            ->default(true),
                    ])
                    ->columns(2),

                /* =========================
             * Clinical Description
             * ========================= */
                Forms\Components\Section::make('Diagnosis Description')
                    ->description('Human-readable diagnosis name.')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label('Diagnosis Description')
                            ->required()
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->placeholder('e.g. Essential (primary) hypertension')
                            ->helperText('Official ICD-10 diagnosis wording')
                            ->columnSpanFull(),
                    ]),

                /* =========================
             * ICD-10 Classification
             * ========================= */
                Forms\Components\Section::make('ICD-10 Classification')
                    ->description('WHO classification hierarchy for reporting and analytics.')
                    ->schema([
                        Forms\Components\TextInput::make('chapter')
                            ->label('Chapter')
                            ->placeholder('e.g. IX')
                            ->helperText('ICD-10 chapter (Roman numeral)')
                            ->maxLength(10),

                        Forms\Components\TextInput::make('block')
                            ->label('Block')
                            ->placeholder('e.g. I10–I15')
                            ->helperText('ICD-10 block range')
                            ->maxLength(20),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chapter')
                    ->searchable(),
                Tables\Columns\TextColumn::make('block')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIcd10Codes::route('/'),
            'create' => Pages\CreateIcd10Code::route('/create'),
            'view' => Pages\ViewIcd10Code::route('/{record}'),
            'edit' => Pages\EditIcd10Code::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
