<?php

namespace App\Filament\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Toggle;

class DrugFilters
{
    /**
     * Return all filters for the Drug model.
     */
    public static function all(): array
    {
        return [
            // Status filters (grouped together)
            Filter::make('status')
                ->label('Drug Status')
                ->form([
                    Checkbox::make('is_active')
                        ->label('Active Only')
                        ->default(false),
                    Checkbox::make('is_discontinued')
                        ->label('Discontinued Only')
                        ->default(false),
                    Checkbox::make('is_low_stock')
                        ->label('Low Stock Only')
                        ->default(false),
                    Checkbox::make('needs_reorder')
                        ->label('Needs Reorder')
                        ->default(false),
                ])
                ->query(function (Builder $query, array $data) {
                    $query->when($data['is_active'] ?? false, fn($q) => $q->active());
                    $query->when($data['is_discontinued'] ?? false, fn($q) => $q->where('is_discontinued', true));
                    $query->when($data['is_low_stock'] ?? false, fn($q) => $q->lowStock());
                    $query->when(
                        $data['needs_reorder'] ?? false,
                        fn($q) => $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                            ->where('is_discontinued', false)
                    );

                    return $query;
                })
                ->indicateUsing(function (array $data) {
                    $indicators = [];

                    if ($data['is_active'] ?? false) {
                        $indicators[] = 'Active';
                    }
                    if ($data['is_discontinued'] ?? false) {
                        $indicators[] = 'Discontinued';
                    }
                    if ($data['is_low_stock'] ?? false) {
                        $indicators[] = 'Low Stock';
                    }
                    if ($data['needs_reorder'] ?? false) {
                        $indicators[] = 'Needs Reorder';
                    }

                    return !empty($indicators) ? implode(', ', $indicators) : null;
                }),

            // Expiry filters (grouped together)
            Filter::make('expiry')
                ->label('Expiry Status')
                ->form([
                    Checkbox::make('is_expired')
                        ->label('Expired Only')
                        ->default(false),
                    TextInput::make('expiring_days')
                        ->numeric()
                        ->label('Expiring within (days)')
                        ->placeholder('e.g., 30')
                        ->minValue(1),
                ])
                ->query(function (Builder $query, array $data) {
                    $query->when(
                        $data['is_expired'] ?? false,
                        fn($q) => $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now())
                    );

                    if (!empty($data['expiring_days'])) {
                        $query->expiringSoon($data['expiring_days']);
                    }

                    return $query;
                })
                ->indicateUsing(function (array $data) {
                    $indicators = [];

                    if ($data['is_expired'] ?? false) {
                        $indicators[] = 'Expired';
                    }
                    if (!empty($data['expiring_days'])) {
                        $indicators[] = "Expiring within {$data['expiring_days']} days";
                    }

                    return !empty($indicators) ? implode(', ', $indicators) : null;
                }),


            // Classification filters (grouped together)
            Filter::make('classification')
                ->label('Drug Classification')
                ->form([
                    Checkbox::make('requires_prescription')
                        ->label('Requires Prescription')
                        ->default(false),
                    Checkbox::make('is_controlled')
                        ->label('Controlled Substance')
                        ->default(false),
                    Checkbox::make('is_dangerous')
                        ->label('Dangerous Drug')
                        ->default(false),
                ])
                ->query(function (Builder $query, array $data) {
                    $query->when(
                        $data['requires_prescription'] ?? false,
                        fn($q) => $q->requiresPrescription()
                    );
                    $query->when(
                        $data['is_controlled'] ?? false,
                        fn($q) => $q->controlled()
                    );
                    $query->when(
                        $data['is_dangerous'] ?? false,
                        fn($q) => $q->where('is_dangerous_drug', true)
                    );

                    return $query;
                })
                ->indicateUsing(function (array $data) {
                    $indicators = [];

                    if ($data['requires_prescription'] ?? false) {
                        $indicators[] = 'Requires Prescription';
                    }
                    if ($data['is_controlled'] ?? false) {
                        $indicators[] = 'Controlled Substance';
                    }
                    if ($data['is_dangerous'] ?? false) {
                        $indicators[] = 'Dangerous Drug';
                    }

                    return !empty($indicators) ? implode(', ', $indicators) : null;
                }),
        ];
    }

    /**
     * Return only essential filters for a more compact layout.
     */
    public static function essentials(): array
    {
        return [
            // Compact status filter using Toggle
            Filter::make('active_status')
                ->label('Status')
                ->form([
                    Toggle::make('active_only')
                        ->label('Active Only')
                        ->default(false),
                    Toggle::make('low_stock_only')
                        ->label('Low Stock')
                        ->default(false),
                    Toggle::make('needs_reorder_only')
                        ->label('Needs Reorder')
                        ->default(false),
                ])
                ->query(function (Builder $query, array $data) {
                    $query->when($data['active_only'] ?? false, fn($q) => $q->active());
                    $query->when($data['low_stock_only'] ?? false, fn($q) => $q->lowStock());
                    $query->when(
                        $data['needs_reorder_only'] ?? false,
                        fn($q) => $q->whereColumn('stock_quantity', '<=', 'reorder_level')
                            ->where('is_discontinued', false)
                    );

                    return $query;
                })
                ->indicateUsing(function (array $data) {
                    $indicators = [];

                    if ($data['active_only'] ?? false) {
                        $indicators[] = 'Active';
                    }
                    if ($data['low_stock_only'] ?? false) {
                        $indicators[] = 'Low Stock';
                    }
                    if ($data['needs_reorder_only'] ?? false) {
                        $indicators[] = 'Needs Reorder';
                    }

                    return !empty($indicators) ? 'Status: ' . implode(', ', $indicators) : null;
                }),

            // Compact expiry filter
            Filter::make('expiry_compact')
                ->label('Expiry')
                ->form([
                    Toggle::make('expired_only')
                        ->label('Expired Only')
                        ->default(false),
                    TextInput::make('expiring_soon')
                        ->numeric()
                        ->label('Expiring (days)')
                        ->placeholder('30')
                        ->minValue(1),
                ])
                ->query(function (Builder $query, array $data) {
                    $query->when(
                        $data['expired_only'] ?? false,
                        fn($q) => $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now())
                    );

                    if (!empty($data['expiring_soon'])) {
                        $query->expiringSoon($data['expiring_soon']);
                    }

                    return $query;
                })
                ->indicateUsing(function (array $data) {
                    $indicators = [];

                    if ($data['expired_only'] ?? false) {
                        $indicators[] = 'Expired';
                    }
                    if (!empty($data['expiring_soon'])) {
                        $indicators[] = "Expiring in {$data['expiring_soon']}d";
                    }

                    return !empty($indicators) ? 'Expiry: ' . implode(', ', $indicators) : null;
                }),
        ];
    }
}
