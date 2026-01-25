<?php

namespace App\Filament\Widgets;

use App\Models\DrugSale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopDrugsChart extends ChartWidget
{
    protected static ?string $heading = 'Top Drugs Sold (Last 30 Days)';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $orgId = \Filament\Facades\Filament::getTenant()?->id;

        $topDrugs = DrugSale::select(
            'drug_id',
            DB::raw('SUM(quantity) as total_quantity')
        )
            ->where('organization_id', $orgId)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('drug_id')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->with('drug')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Top Drugs Sold',
                    'data' => $topDrugs->pluck('total_quantity'),
                    'backgroundColor' => 'rgba(59,130,246,0.7)',
                ],
            ],
            'labels' => $topDrugs->pluck('drug.name'),
        ];
    }
}
