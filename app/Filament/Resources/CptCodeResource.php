<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CptCodeResource\Pages;
use App\Filament\Resources\CptCodeResource\RelationManagers;
use App\Models\CptCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CptCodeResource extends Resource
{
    protected static ?string $model = CptCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* =========================
             * CPT Code Identity
             * ========================= */
                Forms\Components\Section::make('CPT Code')
                    ->description('Procedure or service identification used for billing.')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('CPT Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g. 99213, 93000')
                            ->helperText('Official CPT procedure code')
                            ->maxLength(10),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive codes are hidden from billing and clinical use')
                            ->default(true),
                    ])
                    ->columns(2),

                /* =========================
             * Procedure Description
             * ========================= */
                Forms\Components\Section::make('Procedure Description')
                    ->description('Human-readable explanation of the medical service.')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Procedure Description')
                            ->required()
                            ->rows(3)
                            ->placeholder('e.g. Office or outpatient visit for the evaluation and management of an established patient')
                            ->helperText('Official CPT description used for billing')
                            ->columnSpanFull(),
                    ]),

                /* =========================
             * CPT Classification
             * ========================= */
                Forms\Components\Section::make('CPT Classification')
                    ->description('High-level category for reporting and analytics.')
                    ->schema([
                        Forms\Components\TextInput::make('category')
                            ->label('Category')
                            ->placeholder('e.g. Evaluation & Management')
                            ->helperText('CPT category or service group')
                            ->maxLength(255),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
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
            'index' => Pages\ListCptCodes::route('/'),
            'create' => Pages\CreateCptCode::route('/create'),
            'view' => Pages\ViewCptCode::route('/{record}'),
            'edit' => Pages\EditCptCode::route('/{record}/edit'),
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
