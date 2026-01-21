<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'User Accounts';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description(fn() => $form->getOperation() !== 'view' ? 'Basic personal details for the user.' : null)
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->label('Branch / Organisation')
                            ->relationship('organization', 'name')
                            ->required()
                            ->preload()
                            ->native(false)
                            ->placeholder('Select an organisation')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'This user will belong to the selected organisation.' : null),
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->placeholder('Enter the full name of the user')
                            ->required()
                            ->maxLength(191)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'The user\'s full display name.' : null),

                        Forms\Components\TextInput::make('email')
                            ->placeholder('Enter the email address of the user')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191)
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'The user\'s email address used for login and notifications.' : null),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Enter user phone number')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'This is the official ugandan phone number of the user.' : null),

                        Forms\Components\Select::make('roles')
                            ->label('Privilege')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('name', '!=', 'super_admin')
                            )
                            ->preload()
                            ->placeholder("Select prevelage for the user")
                            ->searchable()
                            ->multiple(false) // Explicit: one role per user
                            ->required(),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state)) // Hash password before saving
                            ->dehydrated(fn(?string $state): bool => filled($state)) // Only save if the field is filled
                            ->required(fn(string $operation): bool => $operation === 'create') // Required only on creation
                            ->maxLength(191)
                            ->revealable()
                            ->placeholder("Enter password here")
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Only required when creating a user. Leave blank to keep the current password.' : null),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required()

                    ])->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')
                    ->placeholder("---")
                    ->numeric()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label("Fullname")
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label("Email Address")
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->placeholder('---')
                    ->label('Prevelage'),
                Tables\Columns\TextColumn::make('phone')
                    ->label("Phone Number")
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label("Is Active")
                    ->boolean(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->placeholder("---")
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->placeholder("---")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->placeholder("---")
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->placeholder("---")
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
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
