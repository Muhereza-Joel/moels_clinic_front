<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrugSaleResource\Pages;
use App\Filament\Resources\DrugSaleResource\RelationManagers;
use App\Models\DrugSale;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Drug;
use App\Models\Patient;

class DrugSaleResource extends Resource
{
    protected static ?string $model = DrugSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?string $navigationLabel = 'Pharmacy / Drug Sales';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* =========================
             * DRUG & CUSTOMER
             * ========================= */
                Forms\Components\Section::make('Drug & Customer')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('drug_id')
                            ->label('Drug')
                            ->relationship('drug', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->helperText('Select the drug being sold')
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state) {
                                    return;
                                }

                                $drug = Drug::find($state);

                                if (! $drug) {
                                    return;
                                }

                                // Auto-fill price
                                $set('unit_price', $drug->unit_price);

                                // Cache stock for validation
                                $set('available_stock', $drug->stock_quantity);

                                // Cache unit of measure (e.g. strip, tablet)
                                $set('unit_of_measure', $drug->unit_of_measure);
                            }),

                        Forms\Components\Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'first_name')
                            ->getOptionLabelFromRecordUsing(
                                fn(Patient $record) =>
                                $record->first_name . ' ' . $record->last_name
                            )
                            ->dehydrated(true)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // Reset walk-in customer fields when patient is selected
                                    $set('customer_name', null);
                                    $set('customer_contact', null);
                                }
                            })
                            ->placeholder('Select a patient')
                            ->preload()
                            ->searchable()
                            ->helperText('Select the patient if applicable'),

                        Forms\Components\TextInput::make('customer_name')
                            ->label('Customer Name')
                            ->placeholder('Walk-in customer name')
                            ->visible(fn(callable $get) => blank($get('patient_id')))
                            ->maxLength(255),


                        Forms\Components\TextInput::make('customer_contact')
                            ->label('Customer Contact')
                            ->placeholder('Phone number')
                            ->visible(fn(callable $get) => blank($get('patient_id')))
                            ->maxLength(255),

                    ]),

                /* =========================
             * PRICING
             * ========================= */
                Forms\Components\Section::make('Pricing')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->reactive()
                            ->helperText(
                                fn(callable $get) =>
                                $get('available_stock')
                                    ? 'Available stock: ' . $get('available_stock')
                                    : null
                            )
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $stock = $get('available_stock');

                                if ($stock !== null && $state > $stock) {
                                    Notification::make()
                                        ->title('Insufficient stock')
                                        ->body("Only {$stock} unit(s) available.")
                                        ->danger()
                                        ->send();
                                }

                                $set('total_price', $state * ($get('unit_price') ?? 0));
                            })
                            ->rule(function (callable $get) {
                                return function (string $attribute, $value, $fail) use ($get) {
                                    $stock = $get('available_stock');

                                    if ($stock !== null && $value > $stock) {
                                        $fail("Only {$stock} unit(s) available in stock.");
                                    }
                                };
                            }),

                        Forms\Components\TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->numeric()
                            ->required()
                            ->prefix('UGX')
                            ->suffix(
                                fn(callable $get) =>
                                $get('unit_of_measure')
                                    ? 'Per ' . $get('unit_of_measure')
                                    : null
                            )
                            ->reactive()
                            ->helperText('Auto-filled from inventory')
                            ->afterStateUpdated(
                                fn($state, callable $set, callable $get) =>
                                $set('total_price', ($get('quantity') ?? 0) * $state)
                            ),

                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price')
                            ->numeric()
                            ->prefix('UGX')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                    ]),

                /* =========================
             * PAYMENT
             * ========================= */
                Forms\Components\Section::make('Payment')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'mobile_money' => 'Mobile Money',
                                'card' => 'Card Payment',
                                'bank_transfer' => 'Bank Transfer',
                            ]),

                        Forms\Components\Select::make('payment_status')
                            ->required()
                            ->options([
                                'paid' => 'Paid',
                                'pending' => 'Pending',
                            ])
                            ->default('paid'),

                        Forms\Components\TextInput::make('receipt_number')
                            ->placeholder('Receipt number'),
                    ]),

                /* =========================
             * NOTES
             * ========================= */
                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->placeholder('Any additional notes'),
                    ]),

                /* =========================
             * META
             * ========================= */
                Forms\Components\Hidden::make('available_stock')
                    ->dehydrated(false),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => auth()->id())
                    ->dehydrated(),

                Forms\Components\DateTimePicker::make('sale_date')
                    ->default(now())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID'),
                Tables\Columns\TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('drug_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('receipt_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
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
            ])
            ->filters([
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
            'index' => Pages\ListDrugSales::route('/'),
            'create' => Pages\CreateDrugSale::route('/create'),
            'view' => Pages\ViewDrugSale::route('/{record}'),
            'edit' => Pages\EditDrugSale::route('/{record}/edit'),
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
