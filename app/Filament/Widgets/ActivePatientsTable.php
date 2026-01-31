<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;

class ActivePatientsTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'full'; // make it span full width

    protected static ?string $heading = 'Currently Admitted Patients';
    protected static ?int $sort = 3;

    public function table(Tables\Table $table): Tables\Table
    {
        $orgId = Filament::getTenant()?->id;

        return $table
            ->query(
                Patient::query()
                    ->where('organization_id', $orgId)
                    ->active() // uses scopeActive
            )
            ->columns([
                Tables\Columns\TextColumn::make('mrn')
                    ->label('MRN')
                    ->placeholder('---')
                    ->sortable(),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->placeholder('---')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sex')
                    ->placeholder('---')
                    ->label('Sex'),

                Tables\Columns\TextColumn::make('date_of_birth')
                    ->placeholder('---')
                    ->label('DOB')
                    ->date(),

                Tables\Columns\TextColumn::make('phone')
                    ->placeholder('---')
                    ->label('Phone'),

                Tables\Columns\TextColumn::make('email')
                    ->placeholder('---')
                    ->label('Email'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]); // pagination options
    }
}
