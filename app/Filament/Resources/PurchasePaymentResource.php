<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchasePaymentResource\Pages;
use App\Filament\Resources\PurchasePaymentResource\RelationManagers;
use App\Models\PurchasePayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchasePaymentResource extends Resource
{
    protected static ?string $model = PurchasePayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Payment Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Core payment information' : null)
                    ->schema([

                        Forms\Components\Select::make('purchase_order_id')
                            ->label('Purchase Order')
                            ->relationship(
                                name: 'purchaseOrder',
                                titleAttribute: 'order_number'
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the related purchase order' : null),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount Paid')
                            ->required()
                            ->numeric()
                            ->prefix('UGX')
                            ->placeholder('e.g. 500000')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Total amount paid' : null),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Date payment was made' : null),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Payment Method & Reference')
                    ->description('How the payment was made')
                    ->schema([

                        Forms\Components\Select::make('payment_method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'momo' => 'Mobile Money',
                                'bank' => 'Bank Transfer',
                            ])
                            ->placeholder('Select payment method')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Choose how the payment was made' : null),

                        Forms\Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->maxLength(255)
                            ->placeholder('Transaction / receipt number')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Optional reference for tracking' : null),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->placeholder('Any additional payment details...')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Optional internal notes' : null)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('created_by')
                    ->default(fn() => auth()->id()),

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
                Tables\Columns\TextColumn::make('purchase_order_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_by')
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
            'index' => Pages\ListPurchasePayments::route('/'),
            'create' => Pages\CreatePurchasePayment::route('/create'),
            'view' => Pages\ViewPurchasePayment::route('/{record}'),
            'edit' => Pages\EditPurchasePayment::route('/{record}/edit'),
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
