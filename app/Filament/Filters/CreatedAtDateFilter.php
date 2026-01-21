<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CreatedAtDateFilter
{
    /**
     * Build a reusable filter for created_at column.
     */
    public static function make(): Filter
    {
        return Filter::make('created_at')
            ->label('Created Between')
            ->form([
                \Filament\Forms\Components\Select::make('preset')
                    ->label('Quick Range')
                    ->options([
                        'today' => 'Today',
                        '7_days' => 'Last 7 Days',
                        '30_days' => 'Last 30 Days',
                        'this_month' => 'This Month',
                        'this_year' => 'This Year',
                    ]),
                \Filament\Forms\Components\DateTimePicker::make('from')->label('From'),
                \Filament\Forms\Components\DateTimePicker::make('until')->label('Until'),

            ])
            ->query(function (Builder $query, array $data) {
                $table = $query->getModel()->getTable();

                if (! empty($data['preset'])) {
                    return match ($data['preset']) {
                        'today'      => $query->whereDate("$table.created_at", now()->toDateString()),
                        '7_days'     => $query->whereDate("$table.created_at", '>=', now()->subDays(7)),
                        '30_days'    => $query->whereDate("$table.created_at", '>=', now()->subDays(30)),
                        'this_month' => $query->whereMonth("$table.created_at", now()->month)
                            ->whereYear("$table.created_at", now()->year),
                        'this_year'  => $query->whereYear("$table.created_at", now()->year),
                        default      => $query,
                    };
                }

                return $query
                    ->when($data['from'] ?? null, fn($q, $date) => $q->where("$table.created_at", '>=', $date))
                    ->when($data['until'] ?? null, fn($q, $date) => $q->where("$table.created_at", '<=', $date));
            });
    }
}
