<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabOrderResource\Pages;
use App\Filament\Resources\LabOrderResource\RelationManagers;
use App\Filament\Resources\LabOrderResource\RelationManagers\LabResultsRelationManager;
use App\Models\LabOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;
use App\Models\Visit;
use App\Models\Patient;

class LabOrderResource extends Resource
{
    protected static ?string $model = LabOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Lab Orders / Requests';
    protected static ?int $navigationSort = 4;

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Lab Order On Patient — {$record->patient->full_name}";
    }


    public static function getGloballySearchableAttributes(): array
    {
        return [
            'panel_code',
            'status',
            'order_date',
            'patient.first_name',
            'patient.last_name',
            'patient.phone',
            'patient.mrn',
            'orderedBy.name',
            'visit.id',
        ];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            collect([
                $record->panel_code,
                $record->order_date?->format('d M Y H:i'),
                ucfirst(str_replace('_', ' ', $record->status)),
            ])->filter()->join(' · ')
        ];
    }


    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with([
                'patient:id,first_name,last_name,phone,mrn',
                'orderedBy:id,name',
                'visit:id',
            ])
            ->where(function ($query) {
                $query
                    // Always include pending / in-progress lab orders
                    ->whereIn('status', ['ordered', 'in_progress'])
                    // Include completed lab orders only from the last 14 days
                    ->orWhere(function ($q) {
                        $q->where('status', 'completed')
                            ->where('order_date', '>=', now()->subDays(14));
                    });
            })
            ->orderByDesc('order_date');
    }




    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* =========================
             * Visit & Patient Context
             * ========================= */
                Forms\Components\Section::make('Visit Information')
                    ->description('Select the visit this lab order belongs to')
                    ->schema([

                        Forms\Components\Select::make('visit_id')
                            ->label('Visit')
                            ->relationship('visit', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn(Visit $record) =>
                                $record->sequence
                                    . ' — ' . $record->patient->first_name . ' ' . $record->patient->last_name
                                    . ' — ' . $record->created_at->format('d M Y H:i')
                            )
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->required()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $visit = Visit::with('patient')->find($state);

                                if ($visit) {
                                    $set('patient_id', $visit->patient_id);
                                }
                            })
                            ->placeholder('Select a visit (auto-fills patient)')
                            ->helperText(
                                fn() =>
                                $form->getOperation() !== 'view'
                                    ? 'Selecting a visit will automatically fill the patient'
                                    : null
                            ),

                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'first_name')
                            ->getOptionLabelFromRecordUsing(
                                fn(Patient $record) =>
                                $record->first_name . ' ' . $record->last_name
                            )
                            ->disabled()
                            ->dehydrated(true)
                            ->placeholder('Auto-filled from visit')
                            ->helperText('Patient is derived from the selected visit'),

                    ])
                    ->columns(2),

                /* =========================
             * Lab Order Details
             * ========================= */
                Forms\Components\Section::make('Lab Order Details')
                    ->schema([

                        Forms\Components\TextInput::make('panel_code')
                            ->label('Panel / Test Code')
                            ->maxLength(255)
                            ->placeholder('e.g. CBC, LFT, U&E')
                            ->helperText('Optional laboratory panel or test identifier'),

                        Forms\Components\Select::make('status')
                            ->label('Order Status')
                            ->options([
                                'ordered'   => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->placeholder('Select status')
                            ->helperText('Current processing status of this lab order'),

                        Forms\Components\DateTimePicker::make('order_date')
                            ->label('Order Date & Time')
                            ->required()
                            ->default(now())
                            ->helperText('When the lab order was requested'),

                    ])
                    ->columns(3),

                /* =========================
             * Notes
             * ========================= */
                Forms\Components\Section::make('Clinical Notes')
                    ->schema([

                        Forms\Components\RichEditor::make('notes')
                            ->label('Additional notes to guide the laboratory tests')
                            ->placeholder('Any clinical notes or special instructions for the lab')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull(),

                    ]),

                /* =========================
             * System Fields
             * ========================= */
                Forms\Components\Hidden::make('ordered_by')
                    ->default(fn() => auth()->id())
                    ->dehydrated(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.tables.lab-orders-status-legend'))
            ->defaultSort('created_at', 'desc')
            ->recordClasses(fn($record) => match ($record->status) {
                'ordered'      => 'laborder-row-ordered laborder-row-hover',
                'in_progress'  => 'laborder-row-in-progress laborder-row-hover',
                'completed'    => 'laborder-row-completed laborder-row-hover',
                'cancelled'    => 'laborder-row-cancelled laborder-row-hover',
                default        => null,
            })

            ->columns([
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('orderedBy.name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('panel_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'primary' => 'ordered',       // blue
                        'warning' => 'in_progress',   // yellow
                        'success' => 'completed',     // green
                        'danger' => 'cancelled',      // red
                    ])
                    ->searchable()
                    ->sortable(),


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
            LabResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLabOrders::route('/'),
            'create' => Pages\CreateLabOrder::route('/create'),
            'view' => Pages\ViewLabOrder::route('/{record}'),
            'edit' => Pages\EditLabOrder::route('/{record}/edit'),
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
