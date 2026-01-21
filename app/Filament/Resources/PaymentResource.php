<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?string $navigationLabel = 'Invoice Payments';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                /* -------------------------------------------------
             | Invoice & Amount
             -------------------------------------------------*/
                Forms\Components\Section::make('Invoice & Amount')
                    ->schema([

                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship(
                                name: 'invoice',
                                titleAttribute: 'invoice_number',
                                modifyQueryUsing: fn(Builder $query) => $query->where('total_amount', '>', 0)
                            )
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn(Invoice $record) => "{$record->invoice_number}")
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $invoice = \App\Models\Invoice::find($state);
                                    if ($invoice) {
                                        // show current invoice total in a separate field
                                        $set('current_invoice_amount', $invoice->total_amount);
                                    }
                                } else {
                                    $set('current_invoice_amount', null);
                                }
                            })
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the invoice this payment is applied to' : null),



                        Forms\Components\TextInput::make('amount')
                            ->label('Amount Paid')
                            ->numeric()
                            ->required()
                            ->prefix('UGX')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Amount received from the patient' : null),


                    ])
                    ->columns(2),

                /* -------------------------------------------------
             | Payment Details
             -------------------------------------------------*/
                Forms\Components\Section::make('Payment Details')
                    ->schema([

                        Forms\Components\Select::make('method')
                            ->label('Payment Method')
                            ->required()
                            ->options([
                                'cash' => 'Cash',
                                'mobile_money' => 'Mobile Money',
                                'card' => 'Card Payment',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'How the payment was made' : null),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Payment Date')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('reference')
                            ->label('Reference / Transaction ID')
                            ->maxLength(255)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Optional: MoMo or bank transaction reference' : null),

                        Forms\Components\Hidden::make('authored_by')
                            ->default(fn() => auth()->id())
                            ->dehydrated(true),

                    ])
                    ->columns(2),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->numeric()
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Payment Method')
                    ->placeholder('---')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'mobile_money' => 'warning',
                        'card' => 'info',
                        'bank_transfer' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'cash' => 'Cash',
                        'mobile_money' => 'Mobile Money',
                        'card' => 'Card Payment',
                        'bank_transfer' => 'Bank Transfer',
                        default => '—',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->placeholder("---")
                    ->numeric()
                    ->money('UGX', true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->placeholder("---")
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->placeholder("---")
                    ->searchable(),
                // Tables\Columns\TextColumn::make('recordedBy.name')
                //     ->placeholder("---")
                //     ->numeric()
                //     ->sortable(),
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
            ->recordUrl(fn() => null)
            ->actions([])
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
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
