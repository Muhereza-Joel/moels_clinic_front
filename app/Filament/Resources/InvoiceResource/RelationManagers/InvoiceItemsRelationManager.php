<?php

namespace App\Filament\Resources\InvoiceResource\RelationManagers;

use App\Models\Drug;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'invoiceItems';
    protected static ?string $recordTitleAttribute = 'description';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('item_type')
                ->label('Item Type')
                ->required()
                ->options([
                    'drug' => 'Drug / Medicine',
                    'misc' => 'Miscellaneous Item / Service',
                ])
                ->reactive()
                ->helperText('Select the type of invoice item'),

            Forms\Components\Select::make('drug_id')
                ->label('Drug')
                ->visible(fn($get) => $get('item_type') === 'drug')
                ->options(
                    Drug::query()->pluck('name', 'id')
                )
                ->searchable()
                ->preload()
                ->reactive()
                ->dehydrated(false)
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $drug = Drug::find($state);

                    if ($drug) {
                        $quantity = $get('quantity') ?: 1;

                        $set('description', $drug->name);
                        $set('unit_price', $drug->selling_price);
                        $set('total_amount', $drug->selling_price * $quantity);
                    } else {
                        $set('description', null);
                        $set('unit_price', null);
                        $set('total_amount', null);
                    }
                })
                ->helperText('Selecting a drug auto-fills price and description'),

            Forms\Components\TextInput::make('description')
                ->label('Description')
                ->required()
                ->maxLength(255)
                ->readOnly(fn($get) => $get('item_type') === 'drug'),

            Forms\Components\TextInput::make('unit_price')
                ->label('Unit Price')
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
                ->label('Total')
                ->numeric()
                ->disabled()
                ->dehydrated(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),

                Tables\Columns\TextColumn::make('item_type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit_price')
                    ->money('UGX', true),

                Tables\Columns\TextColumn::make('quantity'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money('UGX', true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Item'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
