<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdmissionResource\Pages;
use App\Filament\Resources\AdmissionResource\RelationManagers;
use App\Models\Admission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class AdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?int $navigationSort = 8;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Admission Details')
                    ->description('Record patient admission, discharge, and ward assignment information.')
                    ->schema([

                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship(
                                name: 'patient',
                                modifyQueryUsing: fn($query) => $query->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->mrn} - {$record->full_name} ({$record->phone})")
                            ->searchable(['first_name', 'last_name', 'mrn', 'phone'])
                            ->preload()
                            ->required()
                            ->placeholder('Select the patient being admitted')
                            ->helperText('Search and select the patient for this admission.'),

                        Forms\Components\Select::make('ward_id')
                            ->label('Ward')
                            ->relationship(
                                name: 'ward',
                                modifyQueryUsing: fn($query) => $query->orderBy('name')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name} ({$record->type})")
                            ->searchable(['name', 'type'])
                            ->preload()
                            ->nullable()
                            ->placeholder('Select the ward')
                            ->helperText('Optional: Assign the patient to a ward.'),

                        Forms\Components\Select::make('room_id')
                            ->label('Room')
                            ->relationship(
                                name: 'room',
                                modifyQueryUsing: fn($query) => $query->orderBy('name')
                            )
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}")
                            ->searchable(['name'])
                            ->preload()
                            ->nullable()
                            ->placeholder('Select the room')
                            ->helperText('Optional: Assign the patient to a room.'),


                        Forms\Components\DateTimePicker::make('admitted_at')
                            ->label('Admission Date & Time')
                            ->native(false)
                            ->required()
                            ->placeholder('Select admission date and time')
                            ->helperText('The date and time when the patient was admitted.'),

                        Forms\Components\DateTimePicker::make('discharged_at')
                            ->native(false)
                            ->label('Discharge Date & Time')
                            ->placeholder('Select discharge date and time')
                            ->helperText('The date and time when the patient was discharged, if applicable.'),

                        Forms\Components\Select::make('status')
                            ->label('Admission Status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'discharged' => 'Discharged',
                                'transferred' => 'Transferred',
                            ])
                            ->default('active')
                            ->placeholder('Select current status')
                            ->helperText('Current status of the admission.'),

                        Forms\Components\RichEditor::make('notes')
                            ->label('Notes')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull()
                            ->placeholder('Optional: Any additional information about this admission.')
                            ->helperText('E.g., special care instructions or observations.'),

                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.tables.admission-status-legend'))
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn($record) => match ($record->status) {
                'active'      => 'admission-row-active admission-row-hover',
                'discharged'  => 'admission-row-discharged admission-row-hover',
                'transferred' => 'admission-row-transferred admission-row-hover',
                default       => null,
            })

            ->columns([

                Tables\Columns\TextColumn::make('patient_info')
                    ->label('Patient')
                    ->placeholder('---')
                    ->getStateUsing(
                        fn($record) => $record->patient
                            ? "{$record->patient->full_name} ({$record->patient->mrn})"
                            : '---'
                    ),

                Tables\Columns\TextColumn::make('ward.name')
                    ->placeholder("---")
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.name')
                    ->placeholder("---")
                    ->numeric(),
                Tables\Columns\TextColumn::make('admitted_at')
                    ->placeholder("---")
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discharged_at')
                    ->placeholder("---")
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Admission Status')
                    ->badge() // display as badge
                    ->colors([
                        'success' => 'active',       // Active → green
                        'warning' => 'transferred',  // Transferred → yellow
                        'danger'  => 'discharged',   // Discharged → red
                    ])
                    ->sortable()
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
                CreatedAtDateFilter::make(),
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
            'index' => Pages\ListAdmissions::route('/'),
            'create' => Pages\CreateAdmission::route('/create'),
            'view' => Pages\ViewAdmission::route('/{record}'),
            'edit' => Pages\EditAdmission::route('/{record}/edit'),
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
