<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Filament\Resources\PrescriptionResource\RelationManagers;
use App\Filament\Resources\PrescriptionResource\RelationManagers\PrescriptionItemsRelationManager;
use App\Models\Prescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Prescription Details')
                    ->schema([

                        Forms\Components\Select::make('visit_id')
                            ->label('Visit')
                            ->relationship(
                                name: 'visit',
                                modifyQueryUsing: fn($query) =>
                                $query->with('patient'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->patient->first_name . ' ' .
                                    $record->patient->last_name .
                                    ' — ' . $record->created_at->format('d M Y H:i')
                            )
                            ->default(fn() => request()->get('visit_id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the patient visit' : null),

                        Forms\Components\Select::make('prescribed_by')
                            ->label('Prescribed By')
                            ->relationship(
                                name: 'prescribedBy',
                                modifyQueryUsing: fn($query) => $query->with('user'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn($record) =>
                                $record->user->name
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Doctor who issued this prescription' : null),


                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft'     => 'Draft',
                                'issued'    => 'Issued',
                                'dispensed' => 'Dispensed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Current prescription status' : null),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\RichEditor::make('notes')
                            ->placeholder('Additional prescription notes...')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->header(view('filament.tables.prescription-status-legend'))
            ->recordClasses(fn($record) => match ($record->status) {
                'draft'      => 'prescription-row-draft prescription-row-hover',
                'issued'     => 'prescription-row-issued prescription-row-hover',
                'dispensed'  => 'prescription-row-dispensed prescription-row-hover',
                'cancelled'  => 'prescription-row-cancelled prescription-row-hover',
                default      => null,
            })
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('visit')
                    ->label('Patient Visit')
                    ->getStateUsing(
                        fn($record) =>
                        $record->visit
                            ? $record->visit->patient->first_name . ' ' .
                            $record->visit->patient->last_name .
                            ' — ' . $record->visit->created_at->format('d M Y H:i')
                            : '-'
                    )

                    ->searchable(),
                Tables\Columns\TextColumn::make('prescribedBy.user.name')
                    ->label('Prescribed By')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
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

                // TODAY
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn($query) => $query->whereDate('created_at', Carbon::today()))
                    ->default(), // ✅ default tab

                // LAST 3 DAYS
                Tables\Filters\Filter::make('last_3_days')
                    ->label('Last 3 Days')
                    ->query(
                        fn($query) =>
                        $query->where('created_at', '>=', Carbon::today()->subDays(2))
                    ),

                // THIS WEEK
                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(
                        fn($query) =>
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ])
                    ),

                // THIS MONTH
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(
                        fn($query) =>
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfMonth(),
                            Carbon::now()->endOfMonth()
                        ])
                    ),

                // ALL
                Tables\Filters\Filter::make('all')
                    ->label('All')
                    ->query(fn($query) => $query), // no filter  
                Tables\Filters\TrashedFilter::make(),

            ])
            ->defaultSort('created_at', 'desc')
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
            PrescriptionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'view' => Pages\ViewPrescription::route('/{record}'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
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
