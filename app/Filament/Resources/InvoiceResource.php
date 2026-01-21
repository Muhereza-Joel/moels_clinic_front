<?php

namespace App\Filament\Resources;

use App\Models\Patient;
use App\Models\Visit;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceItemsRelationManager;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* -------------------------------------------------
             | Patient & Visit
             -------------------------------------------------*/
                Forms\Components\Section::make('Patient & Visit')
                    ->schema([

                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'id') // relate by ID
                            ->getOptionLabelFromRecordUsing(
                                fn(Patient $record) =>
                                $record->first_name . ' ' . $record->last_name .
                                    ($record->phone ? ' — ' . $record->phone : '')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the patient for this invoice (name and phone)' : null),

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
                            ->reactive() // allows auto-fill when changing
                            ->afterStateUpdated(function ($state, callable $set) {
                                $visit = Visit::with('patient')->find($state);
                                if ($visit) {
                                    $set('patient_id', $visit->patient_id);
                                }
                            })
                            ->placeholder('Select a visit (auto-fills patient)')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Selecting a visit will auto-fill the patient ID' : null),

                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Invoice Details
             -------------------------------------------------*/
                Forms\Components\Section::make('Invoice Details')
                    ->schema([

                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->disabled() // auto-generated
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Automatically generated in the system' : null),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'issued' => 'Issued',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid',
                                'void' => 'Void',
                            ])
                            ->required()
                            ->default('draft')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Invoice status' : null),

                        Forms\Components\TextInput::make('subtotal_amount')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->default(0),

                        Forms\Components\TextInput::make('tax_amount')
                            ->label('Tax')
                            ->numeric()
                            ->readOnly()
                            ->required()
                            ->default(0),

                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Discount')
                            ->numeric()
                            ->readOnly()
                            ->required()
                            ->default(0),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Balance')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->default(0)
                            ->disabled() // optionally calculated automatically
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Calculated from subtotal + tax - discount' : null),

                        Forms\Components\TextInput::make('currency')
                            ->required()
                            ->hidden()
                            ->default('UGX')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Currency of this invoice' : null),

                        Forms\Components\DateTimePicker::make('issued_at')
                            ->label('Issued At')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'When the invoice was issued' : null),

                        Forms\Components\DateTimePicker::make('due_at')
                            ->label('Due At')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Payment due date' : null),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->placeholder('Additional information about the invoice'),

                    ])
                    ->columns(2),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'warning',
                        'partially_paid' => 'info',
                        'paid' => 'success',
                        'void' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtotal_amount')
                    ->numeric()
                    ->money('UGX', true),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->money('UGX', true),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->money('UGX', true),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Balance')
                    ->numeric()
                    ->money('UGX', true),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label("Issued At")
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('due_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => ! in_array($record->status, ['paid', 'void'], true)),
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
            InvoiceItemsRelationManager::class

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
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
