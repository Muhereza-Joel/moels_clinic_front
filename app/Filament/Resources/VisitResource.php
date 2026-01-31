<?php

namespace App\Filament\Resources;

use Filament\Notifications\Actions\Action as NotificationAction;
use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;
use App\Filament\Actions\ForwardAction;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = 'Patients Visits';
    protected static ?int $navigationSort = 3;

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Visit #{$record->id} — {$record->patient->full_name}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            collect([
                $record->visit_date?->format('d M Y H:i'),
                $record->doctor?->user?->name,
                ucfirst($record->status),
            ])->filter()->join(' · ')
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with([
                'patient:id,first_name,last_name,phone,mrn',
                'doctor.user:id,name',
                'appointment:id,sequence',
            ])
            ->where('visit_date', '>=', now()->subMonths(3)) // Only last 3 months
            ->orderByDesc('visit_date');
    }





    public static function getGloballySearchableAttributes(): array
    {
        return [
            'status',
            'visit_date',
            'patient.first_name',
            'patient.last_name',
            'patient.phone',
            'patient.mrn',
            'doctor.user.name',
            'appointment.sequence',
        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visit Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Record patient visit information.' : null)
                    ->schema([

                        // Appointment selection
                        Forms\Components\Select::make('appointment_id')
                            ->label('Appointment')
                            ->relationship('appointment', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn(Appointment $record) =>
                                $record->sequence . ' — ' . $record->patient->first_name . ' ' . $record->patient->last_name
                            )
                            ->searchable()
                            ->preload()
                            ->reactive() // allows auto-fill when selected
                            ->afterStateUpdated(function ($state, callable $set) {
                                $appointment = Appointment::with('patient', 'doctor')->find($state);

                                if ($appointment) {
                                    $set('patient_id', $appointment->patient_id);
                                    $set('doctor_id', $appointment->doctor_id);
                                }
                            })
                            ->placeholder('Select an appointment')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Selecting an appointment will auto-fill patient and doctor.' : null),

                        // Patient selection (auto-filled)
                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                \App\Models\Patient::query()
                                    ->where('mrn', 'ilike', "%{$search}%")
                                    ->orWhere('first_name', 'ilike', "%{$search}%")
                                    ->orWhere('last_name', 'ilike', "%{$search}%")
                                    ->orWhere('phone', 'ilike', "%{$search}%")
                                    ->orWhere('emergency_contact', 'ilike', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($patient) => [
                                        $patient->id => "{$patient->mrn} - {$patient->full_name} ({$patient->phone})"
                                    ])
                                    ->toArray()
                            )

                            ->getOptionLabelUsing(
                                fn($value): ?string =>
                                \App\Models\Patient::find($value)?->full_name
                            )
                            ->default(fn() => request()->get('patient_id'))
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Select patient if not using appointment')
                            ->helperText('Automatically filled when appointment is selected.'),


                        // Doctor selection (auto-filled)
                        Forms\Components\Select::make('doctor_id')
                            ->label('Staff Member / Doctor')
                            ->relationship('doctor', 'id')
                            ->getOptionLabelFromRecordUsing(fn(Doctor $record) => $record->user->name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false)
                            ->placeholder('Select doctor if not using appointment')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Automatically filled when appointment is selected.' : null),

                        // Visit date
                        Forms\Components\DateTimePicker::make('visit_date')
                            ->label('Visit Date & Time')
                            ->required()
                            ->default(now())
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Date and time of the patient visit' : null),

                        // Status with enum options
                        Forms\Components\Select::make('status')
                            ->label('Visit Status')
                            ->options([
                                'open' => 'Open',
                                'finalized' => 'Finalized',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('open')
                            ->required()
                            ->placeholder('Select visit status')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Only allowed statuses: Open, Finalized, Cancelled' : null),

                        // Chief complaint
                        Forms\Components\RichEditor::make('chief_complaint')
                            ->label('Chief Complaint')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull()
                            ->placeholder(fn() => $form->getOperation() !== 'view' ? 'Enter patient\'s main complaint or reason for visit' : null),

                        // Triage data
                        Forms\Components\KeyValue::make('triage_json')
                            ->label('Triage / Observations')
                            ->keyLabel('Observation')
                            ->valueLabel('Value')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Record triage measurements or observations (e.g., BP, temp, pulse)' : null),

                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.tables.visit-status-legend'))
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn($record) => match ($record->status) {
                'open' => 'visit-row-open visit-row-hover',
                'finalized' => 'visit-row-finalized visit-row-hover',
                'cancelled' => 'visit-row-cancelled visit-row-hover',
                default => null,
            })

            ->columns([

                Tables\Columns\TextColumn::make('appointment.sequence')
                    ->numeric()
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->numeric()
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('visit_date')
                    ->dateTime()
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->placeholder("---")
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('createPrescription')
                        ->label('Create Prescription')
                        ->icon('heroicon-o-beaker')
                        ->url(
                            fn(Visit $record) =>
                            PrescriptionResource::getUrl('create', [
                                'visit_id' => $record->id,
                            ])
                        ),
                    ForwardAction::forward(static::class),

                ])->label('Select Action')
                    ->button(),
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
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'view' => Pages\ViewVisit::route('/{record}'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
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
