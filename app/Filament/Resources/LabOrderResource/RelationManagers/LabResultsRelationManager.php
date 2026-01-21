<?php

namespace App\Filament\Resources\LabOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LabResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'results';

    protected static ?string $title = 'Lab Results';

    protected static ?string $recordTitleAttribute = 'analyte_code';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Analyte Information')
                    ->description('Define the laboratory test and its expected range.')
                    ->schema([
                        Forms\Components\TextInput::make('analyte_code')
                            ->label('Analyte / Test Code')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. HGB, WBC, GLU')
                            ->helperText('Short code identifying the laboratory analyte.'),

                        Forms\Components\TextInput::make('unit')
                            ->label('Unit of Measurement')
                            ->maxLength(50)
                            ->placeholder('e.g. g/dL, mmol/L, %')
                            ->helperText('Unit used to interpret the numeric result.'),

                        Forms\Components\TextInput::make('reference_range')
                            ->label('Reference Range')
                            ->maxLength(100)
                            ->placeholder('e.g. 4.5 – 11.0')
                            ->helperText('Normal range for this analyte.'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Result Value')
                    ->description('Enter the test result obtained from the laboratory.')
                    ->schema([
                        Forms\Components\TextInput::make('value_numeric')
                            ->label('Numeric Result')
                            ->numeric()
                            ->placeholder('e.g. 5.8')
                            ->helperText('Use numeric values where applicable.'),

                        Forms\Components\TextInput::make('value_text')
                            ->label('Text Result')
                            ->maxLength(255)
                            ->placeholder('e.g. Positive, Negative, Reactive')
                            ->helperText('For qualitative or descriptive results.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Result Status')
                    ->description('Finalize and validate the test outcome.')
                    ->schema([
                        Forms\Components\Toggle::make('flagged')
                            ->label('Flag as Abnormal')
                            ->helperText('Enable if the result is outside the reference range.'),

                        Forms\Components\DateTimePicker::make('result_date')
                            ->label('Result Date & Time')
                            ->required()
                            ->default(now())
                            ->helperText('When the result was generated or validated.'),
                    ])
                    ->columns(2),
            ]);
    }


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('analyte_code')
            ->columns([
                Tables\Columns\TextColumn::make('analyte_code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('value_numeric')
                    ->label('Value')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit'),

                Tables\Columns\IconColumn::make('flagged')
                    ->boolean()
                    ->label('Flag'),

                Tables\Columns\TextColumn::make('result_date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('flagged')
                    ->query(fn($query) => $query->where('flagged', true)),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
