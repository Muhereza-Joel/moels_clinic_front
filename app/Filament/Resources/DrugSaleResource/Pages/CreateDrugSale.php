<?php

namespace App\Filament\Resources\DrugSaleResource\Pages;

use App\Filament\Resources\DrugSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateDrugSale extends CreateRecord
{
    protected static string $resource = DrugSaleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $drug = \App\Models\Drug::findOrFail($data['drug_id']);

        if ($data['quantity'] > $drug->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock. Only {$drug->stock_quantity} unit(s) available.",
            ]);
        }

        return $data;
    }
}
