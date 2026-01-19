<?php

namespace App\Filament\Resources\PrescriptionResource\RelationManagers;

use App\Models\Drug;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PrescriptionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescriptionItems';

    protected static ?string $title = 'Prescription Items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('drug_code')
                    ->label('Drug')
                    ->options(
                        Drug::query()
                            ->orderBy('name')
                            ->pluck('name', 'drug_code')
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $drug = Drug::where('drug_code', $state)->first();

                        if ($drug) {
                            $set('drug_name', $drug->name);
                        }
                    })
                    ->helperText('Select prescribed drug'),

                Forms\Components\TextInput::make('drug_name')
                    ->disabled()
                    ->dehydrated()
                    ->required(),

                Forms\Components\TextInput::make('dosage')
                    ->placeholder('e.g. 500 mg')
                    ->required(),

                Forms\Components\Select::make('route')
                    ->options([
                        'oral' => 'Oral',
                        'iv' => 'IV',
                        'im' => 'IM',
                        'sc' => 'Subcutaneous',
                        'topical' => 'Topical',
                        'inhalation' => 'Inhalation',
                    ])
                    ->required(),

                Forms\Components\Select::make('frequency')
                    ->options([
                        'once_daily'   => 'Once daily',
                        'twice_daily'  => 'Twice daily',
                        'three_times'  => 'Three times daily',
                        'four_times'   => 'Four times daily',
                        'every_8h'     => 'Every 8 hours',
                        'every_12h'    => 'Every 12 hours',
                        'prn'          => 'As needed (PRN)',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('duration_days')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->label('Duration (Days)'),

                Forms\Components\TextInput::make('quantity')
                    ->numeric()
                    ->minValue(1)
                    ->required(),

                Forms\Components\Textarea::make('instructions')
                    ->columnSpanFull()
                    ->placeholder('Special instructions for patient or pharmacist'),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('drug_name')
                    ->label('Drug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dosage')
                    ->sortable(),

                Tables\Columns\TextColumn::make('route')
                    ->badge(),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Days'),

                Tables\Columns\TextColumn::make('quantity'),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Drug'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
