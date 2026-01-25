<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\Resources\SupplierResource\RelationManagers;
use App\Models\Supplier;
use App\Enums\SupplierRating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([

                    /* -------------------------------------------------
                 | Step 1: Basic & Contact Information
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Basic Information')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('e.g. ABC Pharmaceuticals Ltd')
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Registered supplier name' : null),

                            Forms\Components\TextInput::make('code')
                                ->maxLength(50)
                                ->placeholder('SUP-001')
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Internal supplier reference code' : null),

                            Forms\Components\TextInput::make('contact_person')
                                ->maxLength(255)
                                ->placeholder('John Doe')
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Primary contact person' : null),

                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->maxLength(255)
                                ->placeholder('contact@example.com'),

                            Forms\Components\TextInput::make('phone')
                                ->tel()
                                ->required()
                                ->placeholder('+256 700 000000'),

                            Forms\Components\TextInput::make('alternative_phone')
                                ->tel()
                                ->placeholder('+256 701 000000'),
                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | Step 2: Address & Location
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Address & Location')
                        ->schema([
                            Forms\Components\TextInput::make('address')
                                ->maxLength(255)
                                ->placeholder('Plot 10, Kampala Road'),

                            Forms\Components\TextInput::make('city')
                                ->maxLength(100)
                                ->placeholder('Kampala'),

                            Forms\Components\TextInput::make('state')
                                ->maxLength(100)
                                ->placeholder('Central'),

                            Forms\Components\TextInput::make('country')
                                ->maxLength(100)
                                ->default('Uganda'),

                            Forms\Components\TextInput::make('postal_code')
                                ->maxLength(20)
                                ->placeholder('P.O. Box 123'),
                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | Step 3: Financial & Legal Details
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Financial & Legal')
                        ->schema([
                            Forms\Components\TextInput::make('tax_id')
                                ->maxLength(100)
                                ->placeholder('TIN / VAT Number'),

                            Forms\Components\TextInput::make('registration_number')
                                ->maxLength(100)
                                ->placeholder('Company Registration No.'),

                            Forms\Components\TextInput::make('payment_terms')
                                ->maxLength(100)
                                ->placeholder('Net 30'),

                            Forms\Components\TextInput::make('payment_days')
                                ->numeric()
                                ->required()
                                ->default(30)
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Number of days before payment is due' : null),

                            Forms\Components\TextInput::make('credit_limit')
                                ->numeric()
                                ->prefix('UGX')
                                ->placeholder('0.00'),
                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | Step 4: Banking Information
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Banking Details')
                        ->schema([
                            Forms\Components\TextInput::make('bank_name')
                                ->maxLength(255)
                                ->placeholder('Stanbic Bank'),

                            Forms\Components\TextInput::make('bank_account_name')
                                ->maxLength(255)
                                ->placeholder('ABC Pharmaceuticals Ltd'),

                            Forms\Components\TextInput::make('bank_account_number')
                                ->maxLength(50)
                                ->placeholder('0123456789'),

                            Forms\Components\TextInput::make('bank_branch')
                                ->maxLength(255)
                                ->placeholder('Kampala Main Branch'),
                        ])
                        ->columns(2),

                    /* -------------------------------------------------
                 | Step 5: Status & Additional Info
                 | -------------------------------------------------
                 */
                    Forms\Components\Wizard\Step::make('Status & Notes')
                        ->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->default(true)
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Inactive suppliers cannot be selected' : null),

                            Forms\Components\Toggle::make('is_preferred')
                                ->default(false)
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Preferred suppliers appear first' : null),

                            Forms\Components\Select::make('rating')
                                ->label('Supplier Rating')
                                ->options(SupplierRating::labels())
                                ->default(SupplierRating::AVERAGE->value)
                                ->required()
                                ->native(false)
                                ->placeholder('Select a rating')
                                ->helperText(fn() => $form->getOperation() !== 'view' ? 'Choose the internal supplier rating' : null),

                            Forms\Components\TextInput::make('website')
                                ->url()
                                ->maxLength(255)
                                ->placeholder('https://example.com'),

                            Forms\Components\Textarea::make('notes')
                                ->rows(3)
                                ->columnSpanFull()
                                ->placeholder('Internal remarks or special terms'),
                        ])
                        ->columns(2),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('alternative_phone')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('postal_code')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tax_id')
                    ->label('TIN Number')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_number')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_terms')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('credit_limit')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_days')
                    ->placeholder("---")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank_name')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account_name')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account_number')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_branch')
                    ->placeholder("---")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_preferred')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                Tables\Columns\TextColumn::make('rating')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('website')
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
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
