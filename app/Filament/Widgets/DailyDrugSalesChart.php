<?php

namespace App\Filament\Widgets;

use App\Models\DrugSale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DailyDrugSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Daily Drug Sales (Last 7 Days)';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $orgId = \Filament\Facades\Filament::getTenant()?->id;

        $dailySales = DrugSale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as total')
        )
            ->where('organization_id', $orgId)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => $dailySales->pluck('total'),
                    'borderColor' => 'rgba(59,130,246,0.9)',
                    'backgroundColor' => 'rgba(59,130,246,0.3)',
                    'fill' => true,
                ],
            ],
            'labels' => $dailySales->pluck('date'),
        ];
    }
}
