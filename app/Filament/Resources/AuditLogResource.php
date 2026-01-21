<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Filament\Resources\AuditLogResource\RelationManagers;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Forms\Components\TextInput::make('organization_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('actor_user_id')
                    ->numeric(),
                Forms\Components\TextInput::make('entity_table')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('changes_json'),
                Forms\Components\TextInput::make('ip_address'),
                Forms\Components\TextInput::make('entity_id')
                    ->numeric(),
                Forms\Components\TextInput::make('user_agent')
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('http_method')
                    ->maxLength(10),
                Forms\Components\TextInput::make('severity')
                    ->required()
                    ->maxLength(255)
                    ->default('info'),
                Forms\Components\TextInput::make('correlation_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('actor.name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('severity')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entity_table')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entity_id')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('action')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ip_address'),

                Tables\Columns\TextColumn::make('user_agent')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('http_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correlation_id')
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
                CreatedAtDateFilter::make(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordUrl(fn() => null)
            ->actions([])
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
            'index' => Pages\ListAuditLogs::route('/'),
            'create' => Pages\CreateAuditLog::route('/create'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
            'edit' => Pages\EditAuditLog::route('/{record}/edit'),
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
