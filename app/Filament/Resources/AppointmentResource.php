<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Appointment Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Schedule or update an appointment.' : null)
                    ->schema([

                        // Patient selection
                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship(
                                name: 'patient',
                                modifyQueryUsing: fn($query) => $query->orderBy('last_name')
                            )
                            ->getOptionLabelFromRecordUsing(fn(Patient $record) => "{$record->full_name} ({$record->phone})")
                            ->searchable(['first_name', 'last_name', 'phone']) // allow searching by phone too
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Select a patient')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Choose the patient for this appointment' : null),


                        // Doctor selection
                        Forms\Components\Select::make('doctor_id')
                            ->label('Clinic Team Member')
                            ->relationship('doctor', 'id')
                            ->getOptionLabelFromRecordUsing(fn(Doctor $record) => $record->user->name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Select a doctor')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Choose the attending staff member for this appointment' : null),

                        // Room selection (optional)
                        Forms\Components\Select::make('room_id')
                            ->label('Room')
                            ->relationship('room', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->native(false)
                            ->placeholder('Optional: Select room for the appointment')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Assign a room if required' : null),

                        // Schedule times
                        Forms\Components\DateTimePicker::make('scheduled_start')
                            ->label('Start Time')
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'The scheduled start date and time of the appointment' : null),

                        Forms\Components\DateTimePicker::make('scheduled_end')
                            ->label('End Time')
                            ->required()
                            ->afterOrEqual('scheduled_start')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'The scheduled end date and time of the appointment' : null),

                        // Status selection
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'checked_in' => 'Checked In',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'no_show' => 'No Show',
                            ])
                            ->default('pending')
                            ->required()
                            ->native(false)
                            ->placeholder('Select appointment status')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Current status of the appointment' : null),

                        // Reason / notes
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason / Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Optional notes about this appointment'),

                        // Auto-set created_by to current user
                        Forms\Components\Hidden::make('created_by')
                            ->default(fn() => auth()->id()),
                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('sequence')
                    ->label('Appointment #')
                    ->numeric()
                    ->placeholder("---")
                    ->searchable(),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->numeric()
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label("Staff Member")
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('room.name')
                    ->placeholder("---"),
                Tables\Columns\TextColumn::make('scheduled_start')
                    ->placeholder("---")
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('scheduled_end')
                    ->placeholder("---")
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->placeholder("---")
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
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
