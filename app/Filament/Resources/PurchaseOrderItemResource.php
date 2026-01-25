<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderItemResource\Pages;
use App\Filament\Resources\PurchaseOrderItemResource\RelationManagers;
use App\Models\PurchaseOrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class PurchaseOrderItemResource extends Resource
{
    protected static ?string $model = PurchaseOrderItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationLabel = 'Purchase Stock Taking';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* -------------------------------------------------
             | Order & Item Type
             -------------------------------------------------*/
                Forms\Components\Section::make('Order & Item Type')
                    ->schema([

                        Forms\Components\Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'order_number')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('item_type')
                            ->options([
                                'drug' => 'Drug / Medicine',
                                'general' => 'General Item / Supply',
                            ])
                            ->default('drug')
                            ->required()
                            ->live()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the type of item being ordered' : null),

                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Drug Selection (ONLY for drugs)
             -------------------------------------------------*/
                Forms\Components\Section::make('Drug Selection')
                    ->visible(fn($get) => $get('item_type') === 'drug')
                    ->schema([

                        Forms\Components\Select::make('drug_id')
                            ->relationship('drug', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn($get) => $get('item_type') === 'drug')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $drug = \App\Models\Drug::find($state);

                                if ($drug) {
                                    $set('item_name', $drug->name);
                                    $set('strength', $drug->strength);
                                    $set('form', $drug->form);
                                }
                            })
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the medicine' : null),

                    ]),

                /* -------------------------------------------------
             | Item Details
             -------------------------------------------------*/
                Forms\Components\Section::make('Item Details')
                    ->schema([

                        Forms\Components\TextInput::make('item_name')
                            ->label('Item Name / Description')
                            ->required()
                            ->disabled(fn($get) => $get('item_type') === 'drug')
                            ->dehydrated(),

                        Forms\Components\TextInput::make('strength')
                            ->label('Strength')
                            ->visible(fn($get) => $get('item_type') === 'drug')
                            ->dehydrated(),

                        Forms\Components\Select::make('form')
                            ->label('Dosage Form')
                            ->visible(fn($get) => $get('item_type') === 'drug')
                            ->options([
                                'tablet' => 'Tablet',
                                'capsule' => 'Capsule',
                                'syrup' => 'Syrup',
                                'suspension' => 'Suspension',
                                'injection' => 'Injection',
                                'cream' => 'Cream',
                                'ointment' => 'Ointment',
                                'drops' => 'Drops',
                                'inhaler' => 'Inhaler',
                            ])
                            ->dehydrated(),

                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Quantity & Pricing
             -------------------------------------------------*/
                Forms\Components\Section::make('Quantity & Pricing')
                    ->schema([

                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->live()
                            ->afterStateUpdated(
                                fn($get, $set) =>
                                $set('total_price', $get('quantity') * $get('unit_price'))
                            ),

                        Forms\Components\TextInput::make('quantity_received')
                            ->numeric()
                            ->default(0)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Updated when goods are received' : null),

                        Forms\Components\TextInput::make('unit_price')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('UGX')
                            ->live()
                            ->afterStateUpdated(
                                fn($get, $set) =>
                                $set('total_price', $get('quantity') * $get('unit_price'))
                            ),

                        Forms\Components\TextInput::make('total_price')
                            ->numeric()
                            ->prefix('UGX')
                            ->disabled()
                            ->dehydrated(),

                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Batch & Expiry (Drugs only)
             -------------------------------------------------*/
                Forms\Components\Section::make('Batch & Expiry')
                    ->visible(fn($get) => $get('item_type') === 'drug')
                    ->schema([

                        Forms\Components\TextInput::make('batch_number')
                            ->helperText('Manufacturer batch number'),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->required(fn($get) => $get('item_type') === 'drug')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Mandatory for medicines' : null),

                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Notes
             -------------------------------------------------*/
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID'),
                Tables\Columns\TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('drug_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('drug_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drug_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drug_strength')
                    ->searchable(),
                Tables\Columns\TextColumn::make('drug_form')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_received')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseOrderItems::route('/'),
            'create' => Pages\CreatePurchaseOrderItem::route('/create'),
            'view' => Pages\ViewPurchaseOrderItem::route('/{record}'),
            'edit' => Pages\EditPurchaseOrderItem::route('/{record}/edit'),
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
