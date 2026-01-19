<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Patient Register';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Patient Information')
                    ->description('Basic personal and identification details.')
                    ->schema([

                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter first name'),

                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter last name'),

                        Forms\Components\Select::make('sex')
                            ->label('Sex')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->native(false)
                            ->placeholder('Select sex'),

                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->maxDate(now())
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Used to calculate patient age' : null),

                        Forms\Components\TextInput::make('national_id')
                            ->label('National ID Number')
                            ->maxLength(255)
                            ->placeholder('Optional'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->description('How we can reach the patient.')
                    ->schema([

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255)
                            ->placeholder('example@email.com'),

                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('+256 7XX XXX XXX')
                            ->helperText(fn() => $form->getOperation() !== 'view' ? 'Ugandan phone number' : null),

                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Physical address'),

                        Forms\Components\TextInput::make('emergency_contact')
                            ->label('Emergency Contact')
                            ->placeholder('Name & phone number'),

                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Notes')
                    ->schema([

                        Forms\Components\Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Any important medical or administrative notes'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Patient')
                            ->default(true)
                            ->required(),

                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mrn')
                    ->label('Medical Record Number')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->placeholder("---")
                    ->date()
                    ->searchable(),
                Tables\Columns\TextColumn::make('national_id')
                    ->placeholder("---")
                    ->label('NIN Number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->placeholder("---")
                    ->label('Email Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone Number')
                    ->placeholder("---")
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->toggleable(isToggledHiddenByDefault: true)
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view' => Pages\ViewPatient::route('/{record}'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
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
