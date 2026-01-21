<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecordTypeResource\Pages;
use App\Filament\Resources\RecordTypeResource\RelationManagers;
use App\Models\RecordType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RecordTypeResource extends Resource
{
    protected static ?string $model = RecordType::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Record Type')
                    ->description('Defines a category of medical record used throughout the system.')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Record Type Code')
                            ->required()
                            ->placeholder('e.g. consultation, progress_note')
                            ->helperText('System identifier (lowercase, no spaces)')
                            ->maxLength(50)
                            ->regex('/^[a-z0-9_]+$/'),

                        Forms\Components\TextInput::make('label')
                            ->label('Display Label')
                            ->required()
                            ->placeholder('e.g. Consultation Note')
                            ->helperText('Human-readable name shown to users')
                            ->maxLength(255),
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
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
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
            'index' => Pages\ListRecordTypes::route('/'),
            'create' => Pages\CreateRecordType::route('/create'),
            'view' => Pages\ViewRecordType::route('/{record}'),
            'edit' => Pages\EditRecordType::route('/{record}/edit'),
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
