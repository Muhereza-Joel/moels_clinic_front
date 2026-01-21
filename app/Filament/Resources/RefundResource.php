<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RefundResource\Pages;
use App\Filament\Resources\RefundResource\RelationManagers;
use App\Models\Payment;
use App\Models\Refund;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Filters\CreatedAtDateFilter;

class RefundResource extends Resource
{
    protected static ?string $model = Refund::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationGroup = 'Sales Management';
    protected static ?string $navigationLabel = 'Refunds on Invoices';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // -----------------------------
                // SECTION: Payment References
                // -----------------------------
                Forms\Components\Section::make('Payment References')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Link this payment to its invoice and optional parent payment.' : null)
                    ->schema([
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'invoice_number')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select the invoice this payment applies to' : null)
                            ->placeholder('Choose an invoice'),

                        Forms\Components\Select::make('payment_id')
                            ->label('Parent Payment (optional)')
                            ->relationship('payment', 'id') // assumes `payment` relation exists
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(function (Payment $record) {
                                $invoiceNumber = $record->invoice?->invoice_number ?? 'N/A';
                                $createdAt = $record->created_at?->format('Y-m-d H:i') ?? 'Unknown';
                                return "{$invoiceNumber} ({$createdAt})";
                            })
                            ->preload()
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Link to an existing payment if this is related' : null)
                            ->placeholder('Choose a parent payment'),
                    ]),

                // -----------------------------
                // SECTION: Payment Info
                // -----------------------------
                Forms\Components\Section::make('Payment Details')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Specify the type and amount of this payment.' : null)
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Payment Type')
                            ->required()
                            ->options([
                                'refund' => 'Refund',
                                'adjustment' => 'Adjustment',
                            ])
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Select whether this is a refund or adjustment' : null)
                            ->placeholder('Select type'),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Enter the payment amount' : null)
                            ->placeholder('e.g., 150.00'),
                    ]),

                // -----------------------------
                // SECTION: Additional Info
                // -----------------------------
                Forms\Components\Section::make('Additional Information')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Optional information related to the payment.' : null)
                    ->schema([
                        Forms\Components\RichEditor::make('reason')
                            ->label('Reason / Notes')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Provide a reason or notes for this payment' : null)
                            ->placeholder('Optional notes...')
                            ->toolbarButtons(['bold', 'italic', 'underline', 'h2', 'h3', 'bulletList', 'orderedList'])
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('recorded_by')
                            ->default(fn() => auth()->id()),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                Tables\Columns\TextColumn::make('invoice.invoice_number')
                    ->numeric()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->placeholder("---")
                    ->numeric()
                    ->money('UGX', true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->numeric()
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
            'index' => Pages\ListRefunds::route('/'),
            'create' => Pages\CreateRefund::route('/create'),
            'view' => Pages\ViewRefund::route('/{record}'),
            'edit' => Pages\EditRefund::route('/{record}/edit'),
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
