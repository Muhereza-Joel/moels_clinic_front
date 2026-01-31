<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Medical Records';
    protected static ?string $navigationLabel = 'Measurement Units';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Unit Details')
                    ->description('Basic information about the clinic or hospital unit.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Unit Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Radiology Unit, Pharmacy Unit, Pediatrics Unit')
                            ->helperText('Enter the full name of the clinic or hospital unit.'),

                        Forms\Components\TextInput::make('singular')
                            ->label('Singular Label')
                            ->maxLength(255)
                            ->placeholder('e.g., tablet, capsule')
                            ->helperText('Optional: A singular term used to describe an entity in this unit.'),

                        Forms\Components\TextInput::make('plural')
                            ->label('Plural Label')
                            ->maxLength(255)
                            ->placeholder('e.g., tablets, capsules')
                            ->helperText('Optional: A plural term used to describe multiple entities in this unit.'),

                        Forms\Components\TextInput::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief description of the unit, e.g., "Handles radiology scans and imaging services."')
                            ->helperText('Provide a short description of what this clinic or hospital unit does.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Unit')
                            ->default(true)
                            ->required()
                            ->helperText('Indicate if this unit is currently active or not.'),
                    ])
                    ->columns(2), // two-column layout for compact view
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('singular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plural')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'view' => Pages\ViewUnit::route('/{record}'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
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
