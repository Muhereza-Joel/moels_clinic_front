<?php

namespace App\Filament\Resources\LabOrderResource\Pages;

use App\Filament\Resources\LabOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLabOrders extends ListRecords
{
    protected static string $resource = LabOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
