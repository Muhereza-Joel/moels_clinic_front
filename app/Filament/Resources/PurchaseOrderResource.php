<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Wizard::make([

                    /* -------------------------------------------------
                 | STEP 1: Basic Order Info
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Order Details')
                        ->schema([
                            Forms\Components\Select::make('supplier_id')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Supplier for this purchase order' : null),

                            Forms\Components\DatePicker::make('order_date')
                                ->required(),

                            Forms\Components\DatePicker::make('expected_delivery_date'),

                            Forms\Components\Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'pending_approval' => 'Pending Approval',
                                    'approved' => 'Approved',
                                    'ordered' => 'Ordered',
                                    'partially_received' => 'Partially Received',
                                    'fully_received' => 'Fully Received',
                                    'cancelled' => 'Cancelled',
                                    'closed' => 'Closed',
                                ])
                                ->default('pending')
                                ->required(),

                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | STEP 2: Financials
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Financial Summary')
                        ->schema([
                            Forms\Components\TextInput::make('total_items')
                                ->numeric()
                                ->default(0),

                            Forms\Components\TextInput::make('subtotal')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0),

                            Forms\Components\TextInput::make('tax_amount')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0),

                            Forms\Components\TextInput::make('shipping_cost')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0),

                            Forms\Components\TextInput::make('discount_amount')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0),

                            Forms\Components\TextInput::make('total_amount')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0)
                                ->disabled(),

                        ])
                        ->columns(3),

                    /* -------------------------------------------------
                 | STEP 3: Payment
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Payment')
                        ->schema([
                            Forms\Components\Select::make('payment_status')
                                ->options([
                                    'pending' => 'Pending',
                                    'partial' => 'Partial',
                                    'paid' => 'Paid',
                                    'overdue' => 'Overdue',
                                ])
                                ->default('pending')
                                ->required(),

                            Forms\Components\TextInput::make('amount_paid')
                                ->numeric()
                                ->default(0)
                                ->prefix('UGX'),

                            Forms\Components\TextInput::make('amount_due')
                                ->numeric()
                                ->default(0)
                                ->prefix('UGX')
                                ->disabled(),

                            Forms\Components\Select::make('payment_method')
                                ->options([
                                    'cash' => 'Cash',
                                    'momo' => 'Mobile Money',
                                    'bank' => 'Bank Transfer',
                                ]),

                            Forms\Components\TextInput::make('payment_reference')
                                ->placeholder('Transaction / receipt number'),

                            Forms\Components\DatePicker::make('payment_due_date'),
                            Forms\Components\DatePicker::make('payment_date'),

                        ])
                        ->columns(3),

                    /* -------------------------------------------------
                 | STEP 4: Delivery & Receiving
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Delivery & Receiving')
                        ->schema([
                            Forms\Components\Select::make('delivery_status')
                                ->options([
                                    'pending' => 'Pending',
                                    'processing' => 'Processing',
                                    'shipped' => 'Shipped',
                                    'partially_delivered' => 'Partially Delivered',
                                    'delivered' => 'Delivered',
                                    'on_time' => 'On Time',
                                    'delayed' => 'Delayed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->default('pending'),

                            Forms\Components\DatePicker::make('actual_delivery_date'),

                            Forms\Components\TextInput::make('shipping_method'),
                            Forms\Components\TextInput::make('tracking_number'),

                            Forms\Components\Textarea::make('shipping_address')
                                ->columnSpanFull(),

                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | STEP 5: Quality & Approval
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Quality & Approval')
                        ->schema([
                            Forms\Components\Toggle::make('has_quality_issues')
                                ->default(false)
                                ->live(),

                            Forms\Components\Textarea::make('quality_notes')
                                ->visible(fn($get) => $get('has_quality_issues'))
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('rejected_items_count')
                                ->numeric()
                                ->default(0),

                            Forms\Components\TextInput::make('rejected_items_value')
                                ->numeric()
                                ->prefix('UGX')
                                ->default(0),

                            Forms\Components\Select::make('approved_by')
                                ->relationship('approvedBy', 'name'),

                            Forms\Components\DateTimePicker::make('approved_at'),

                            Forms\Components\Textarea::make('approval_notes')
                                ->columnSpanFull(),

                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | STEP 6: Notes & Meta
                 -------------------------------------------------*/
                    Forms\Components\Wizard\Step::make('Notes & Meta')
                        ->schema([
                            Forms\Components\Textarea::make('notes')
                                ->columnSpanFull(),

                            Forms\Components\Textarea::make('internal_notes')
                                ->columnSpanFull(),

                            Forms\Components\FileUpload::make('attachments')
                                ->multiple()
                                ->directory('purchase-orders'),

                            Forms\Components\Hidden::make('created_by')
                                ->default(fn() => auth()->id()),

                        ]),

                ])
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
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('supplier_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delivery_status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_items')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_due')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shipping_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tracking_number')
                    ->searchable(),
                Tables\Columns\IconColumn::make('has_quality_issues')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rejected_items_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rejected_items_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estimated_lead_time_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actual_lead_time_days')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requested_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_by')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('days_overdue')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_delay_days')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'view' => Pages\ViewPurchaseOrder::route('/{record}'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
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
