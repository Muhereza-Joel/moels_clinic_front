<?php

namespace App\Filament\Resources\DrugSaleResource\Pages;

use App\Filament\Resources\DrugSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDrugSales extends ListRecords
{
    protected static string $resource = DrugSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
