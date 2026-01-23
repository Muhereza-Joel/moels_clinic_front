<?php

namespace App\Filament\Resources\DrugSaleResource\Pages;

use App\Filament\Resources\DrugSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDrugSale extends EditRecord
{
    protected static string $resource = DrugSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
