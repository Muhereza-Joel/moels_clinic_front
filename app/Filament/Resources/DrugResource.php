<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DrugResource\Pages;
use App\Filament\Resources\DrugResource\RelationManagers;
use App\Models\Drug;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DrugResource extends Resource
{
    protected static ?string $model = Drug::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([

                    /* -------------------------------------------------
                 | STEP 1: Basic Information
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Basic Info')
                        ->schema([
                            Forms\Components\TextInput::make('drug_code')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(50)
                                ->placeholder('DRG-001'),

                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('generic_name'),
                            Forms\Components\TextInput::make('brand_name'),
                            Forms\Components\TextInput::make('manufacturer'),
                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | STEP 2: Classification & Pharmaceutical
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Classification')
                        ->schema([
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('subcategory_id')
                                ->relationship('subcategory', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\TextInput::make('form')
                                ->placeholder('Tablet / Syrup'),

                            Forms\Components\TextInput::make('strength')
                                ->placeholder('500 mg'),

                            Forms\Components\TextInput::make('unit_of_measure')
                                ->required()
                                ->default('each'),

                            Forms\Components\TextInput::make('units_per_pack')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ])
                        ->columns(3),

                    /* -------------------------------------------------
                 | STEP 3: Inventory & Pricing (AUTO CALC)
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Inventory & Pricing')
                        ->schema([
                            Forms\Components\TextInput::make('stock_quantity')
                                ->numeric()
                                ->required()
                                ->default(0)
                                ->live()
                                ->afterStateUpdated(
                                    fn(Get $get, Set $set) =>
                                    self::recalculateReorder($get, $set)
                                ),

                            Forms\Components\TextInput::make('maximum_stock')
                                ->numeric()
                                ->live()
                                ->afterStateUpdated(
                                    fn(Get $get, Set $set) =>
                                    self::recalculateReorder($get, $set)
                                ),

                            Forms\Components\TextInput::make('reorder_level')
                                ->numeric()
                                ->required()
                                ->default(0),

                            Forms\Components\TextInput::make('reorder_quantity')
                                ->numeric()
                                ->disabled()
                                ->helperText('Auto-calculated'),

                            Forms\Components\TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->prefix('UGX'),

                            Forms\Components\TextInput::make('cost_price')
                                ->numeric()
                                ->prefix('UGX'),

                            Forms\Components\TextInput::make('selling_price')
                                ->numeric()
                                ->prefix('UGX'),

                            Forms\Components\TextInput::make('wholesale_price')
                                ->numeric()
                                ->prefix('UGX'),
                        ])
                        ->columns(4),

                    /* -------------------------------------------------
                 | STEP 4: Batch, Expiry & Storage (AUTO FLAGS)
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Batch & Expiry')
                        ->schema([
                            Forms\Components\TextInput::make('batch_number'),

                            Forms\Components\DatePicker::make('manufacture_date'),

                            Forms\Components\DatePicker::make('expiry_date')
                                ->live(),

                            Forms\Components\Placeholder::make('expiry_warning')
                                ->content(function (Get $get) {
                                    $date = $get('expiry_date');

                                    if (!$date) {
                                        return null;
                                    }

                                    $expiry = Carbon::parse($date);

                                    if ($expiry->isPast()) {
                                        return '❌ This drug is EXPIRED!';
                                    }

                                    if ($expiry->diffInDays(now()) <= 30) {
                                        return '⚠️ This drug is expiring within 30 days.';
                                    }

                                    return '✅ Expiry date is valid.';
                                })
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('storage_condition'),
                            Forms\Components\TextInput::make('storage_location'),

                            Forms\Components\Textarea::make('storage_instructions')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(3),

                    /* -------------------------------------------------
                 | STEP 5: Regulatory, Suppliers & Status
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Status & Suppliers')
                        ->schema([
                            Forms\Components\Toggle::make('requires_prescription')
                                ->default(true),

                            Forms\Components\Toggle::make('is_controlled_substance')
                                ->default(false),

                            Forms\Components\TextInput::make('controlled_schedule')
                                ->visible(fn(Get $get) => $get('is_controlled_substance')),

                            Forms\Components\Toggle::make('is_dangerous_drug')
                                ->default(false),

                            Forms\Components\Select::make('primary_supplier_id')
                                ->relationship('primarySupplier', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\Select::make('secondary_supplier_id')
                                ->relationship('secondarySupplier', 'name')
                                ->searchable()
                                ->preload(),

                            Forms\Components\Toggle::make('is_active')
                                ->default(true),

                            Forms\Components\Toggle::make('is_discontinued')
                                ->default(false),

                            Forms\Components\Textarea::make('notes')
                                ->rows(3)
                                ->columnSpanFull(),
                        ])
                        ->columns(3),
                ])
                    ->columnSpanFull(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('drug_code')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('form')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('strength')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->placeholder("---")
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_level')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
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
                Tables\Columns\TextColumn::make('generic_name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand_name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('manufacturer')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_id')
                    ->label('Parent')
                    ->numeric()
                    ->placeholder('— top level —')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subcategory_id')
                    ->label('Child Category')
                    ->numeric()
                    ->placeholder('— child level —')
                    ->sortable(),
                Tables\Columns\TextColumn::make('therapeutic_class')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('pharmacologic_class')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->searchable(),
                Tables\Columns\TextColumn::make('units_per_pack')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reorder_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maximum_stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('wholesale_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manufacture_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('storage_condition')
                    ->searchable(),
                Tables\Columns\TextColumn::make('storage_location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regulatory_number')
                    ->searchable(),
                Tables\Columns\IconColumn::make('requires_prescription')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_controlled_substance')
                    ->boolean(),
                Tables\Columns\TextColumn::make('controlled_schedule')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_dangerous_drug')
                    ->boolean(),
                Tables\Columns\TextColumn::make('primary_supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('secondary_supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lead_time_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('minimum_order_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maximum_order_quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monthly_usage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_purchase_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_dispensed_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_discontinued')
                    ->boolean(),
                Tables\Columns\TextColumn::make('discontinued_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discontinued_reason')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_branded')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_generic')
                    ->boolean(),
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

    /**
     * Auto-calculate reorder quantity
     */
    protected static function recalculateReorder(Get $get, Set $set): void
    {
        $max = (int) $get('maximum_stock');
        $stock = (int) $get('stock_quantity');

        $set('reorder_quantity', max($max - $stock, 0));
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
            'index' => Pages\ListDrugs::route('/'),
            'create' => Pages\CreateDrug::route('/create'),
            'view' => Pages\ViewDrug::route('/{record}'),
            'edit' => Pages\EditDrug::route('/{record}/edit'),
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
