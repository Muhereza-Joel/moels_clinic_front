<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceItems'; // Fixed case
    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('item_type')
                    ->required()
                    ->options([
                        'drug' => 'Drug / Medicine',
                        'general' => 'General Item / Supply',
                    ])
                    ->reactive()
                    ->helperText('Select the type of item'),

                Forms\Components\Select::make('drug_id')
                    ->label('Drug')
                    ->visible(fn($get) => $get('item_type') === 'drug')
                    ->options(\App\Models\Drug::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $drug = \App\Models\Drug::find($state);
                        if ($drug) {
                            $set('description', $drug->name);
                            $set('unit_price', $drug->selling_price);
                            $quantity = $get('quantity') ?: 1;
                            $set('total_amount', $drug->selling_price * $quantity);
                        } else {
                            $set('description', null);
                            $set('unit_price', null);
                            $set('total_amount', null);
                        }
                    })
                    ->helperText('Select a drug to auto-fill description and unit price'),

                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255)
                    ->readOnly(fn($get) => $get('item_type') === 'drug'),

                Forms\Components\TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        $set('total_amount', ($get('quantity') ?: 0) * ($state ?: 0))
                    ),

                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->reactive()
                    ->afterStateUpdated(
                        fn($state, callable $set, callable $get) =>
                        $set('total_amount', ($get('unit_price') ?: 0) * ($state ?: 0))
                    ),

                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
                    ->reactive(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->searchable(),
                Tables\Columns\TextColumn::make('item_type')->sortable(),
                Tables\Columns\TextColumn::make('unit_price')->money('UGX', true),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('total_amount')->money('UGX', true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item')
                    ->after(function () {
                        // $this->recalculateInvoiceTotals();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function () {
                        // $this->recalculateInvoiceTotals();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {
                        // $this->recalculateInvoiceTotals();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->after(function () {
                        // $this->recalculateInvoiceTotals();
                    }),
            ]);
    }

    // protected function recalculateInvoiceTotals(): void
    // {
    //     $invoice = $this->getOwnerRecord();

    //     // Calculate subtotal from all invoice items
    //     $subtotal = $invoice->invoiceItems()->sum('total_amount');

    //     // Calculate tax (adjust tax rate as needed)
    //     $taxRate = 0; // 18% tax rate
    //     $tax = $subtotal * $taxRate;

    //     // Get existing discount or default to 0
    //     $discount = $invoice->discount_amount ?: 0;

    //     // Calculate total
    //     $total = $subtotal + $tax - $discount;

    //     // Update the invoice
    //     $invoice->updateQuietly([
    //         'subtotal_amount' => $subtotal,
    //         'tax_amount' => $tax,
    //         'total_amount' => $total,
    //     ]);

    //     // Refresh the form data
    //     $this->dispatch('refresh');
    // }
}
