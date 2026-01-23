<?php

namespace App\Filament\Resources\DrugSaleResource\Pages;

use App\Filament\Resources\DrugSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDrugSale extends ViewRecord
{
    protected static string $resource = DrugSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->disabled(fn($record) => $record->payment_status === 'paid')
        ];
    }
}
