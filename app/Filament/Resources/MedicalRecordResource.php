<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalRecordResource\Pages;
use App\Filament\Resources\MedicalRecordResource\RelationManagers;
use App\Models\MedicalRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationGroup = 'Medical Records';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* =========================
             * Patient & Visit Details
             * ========================= */
                Forms\Components\Section::make('Patient & Visit Information')
                    ->description('Identify the patient and the visit this medical record belongs to.')
                    ->schema([
                        Forms\Components\Select::make('visit_id')
                            ->label('Visit')
                            ->relationship(
                                name: 'visit',
                                modifyQueryUsing: fn($query) => $query->with('patient'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->patient->first_name . ' ' .
                                    $record->patient->last_name .
                                    ' — ' . $record->created_at->format('d M Y H:i')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive() // make this field reactive
                            ->afterStateUpdated(function ($state, callable $set) {
                                // $state is the selected visit_id
                                if ($state) {
                                    $visit = \App\Models\Visit::with('patient')->find($state);
                                    if ($visit && $visit->patient) {
                                        $set('patient_id', $visit->patient->id);
                                    }
                                }
                            })
                            ->helperText(fn() => $form->getOperation() !== 'view'
                                ? 'Choose the visit or encounter reference'
                                : null),

                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship(
                                name: 'patient',
                                modifyQueryUsing: fn($query) => $query->with('visits'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->first_name . ' ' .
                                    $record->last_name .
                                    ' — ' . ($record->phone ?? 'No phone')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select patient')
                            ->helperText(fn() => $form->getOperation() !== 'view'
                                ? 'Choose the patient this record belongs to'
                                : null),

                    ])
                    ->columns(2),

                /* =========================
             * Record Metadata
             * ========================= */
                Forms\Components\Section::make('Record Details')
                    ->description('Basic classification of this medical record.')
                    ->schema([
                        Forms\Components\Select::make('record_type_id')
                            ->label('Record Type')
                            ->relationship(
                                name: 'recordType',   // MedicalRecord belongsTo RecordType
                                titleAttribute: 'label'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select record type')
                            ->helperText('Type of clinical documentation'),


                        Forms\Components\TextInput::make('title')
                            ->label('Record Title')
                            ->placeholder('e.g. Initial Consultation')
                            ->helperText('Short descriptive title (optional)')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                /* =========================
             * Clinical Content
             * ========================= */
                Forms\Components\Section::make('Clinical Notes')
                    ->description('Detailed clinical documentation for this visit.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Clinical Notes')
                            ->placeholder('Enter patient history, examination findings, assessment, and plan...')
                            ->helperText('Supports rich text formatting (WYSIWYG)')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('data_json')
                            ->label('Structured Clinical Data (JSON)')
                            ->placeholder('{"blood_pressure":"120/80","temperature":"36.8"}')
                            ->helperText('Optional: store structured data for system use')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                /* =========================
             * Coding & Classification
             * ========================= */
                Forms\Components\Section::make('Medical Coding')
                    ->description('Diagnosis and procedure coding for billing and reporting.')
                    ->schema([
                        Forms\Components\Select::make('icd10_code')
                            ->label('ICD-10 Diagnosis Code')
                            ->relationship(
                                name: 'icd10',              // relationship name on MedicalRecord
                                titleAttribute: 'code'      // column on Icd10Code model
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->code . ' — ' . strip_tags($record->description)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select ICD-10 code')
                            ->helperText('Diagnosis code (ICD-10)'),

                        Forms\Components\Select::make('cpt_code')
                            ->label('CPT Procedure Code')
                            ->relationship(
                                name: 'cpt',                // relationship name on MedicalRecord
                                titleAttribute: 'code'      // column on CptCode model
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) => $record->code . ' — ' . strip_tags($record->description)
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select CPT code')
                            ->helperText('Procedure or service code (CPT)'),

                        Forms\Components\Hidden::make('authored_by')
                            ->default(fn() => auth()->id())
                            ->dehydrated(true),

                    ])
                    ->columns(2),


            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('recordType.label')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('authoredBy.name')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('icd10_code')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('cpt_code')
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
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'view' => Pages\ViewMedicalRecord::route('/{record}'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
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
