<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Room Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Define the room name and type.' : null)
                    ->schema([

                        Forms\Components\TextInput::make('name')
                            ->label('Room Name')
                            ->placeholder('Enter room name')
                            ->required()
                            ->maxLength(255)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'The display name of the room, e.g., “Consultation Room 1”' : null),

                        Forms\Components\Select::make('type')
                            ->label('Room Type')
                            ->options([
                                'consultation' => 'Consultation',
                                'procedure' => 'Procedure',
                                'ward' => 'Ward',
                                'lab' => 'Lab',
                                'pharmacy' => 'Pharmacy',
                            ])
                            ->placeholder('Select room type')
                            ->required()
                            ->searchable()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the functional type of the room' : null),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Inactive rooms will not be assignable to staff or patients' : null),

                    ])
                    ->columns(1), // single column is fine for clarity
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->placeholder("---")
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'view' => Pages\ViewRoom::route('/{record}'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
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
