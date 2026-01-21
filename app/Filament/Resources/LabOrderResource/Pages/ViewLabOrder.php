<?php

namespace App\Filament\Resources\LabOrderResource\Pages;

use App\Filament\Resources\LabOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLabOrder extends ViewRecord
{
    protected static string $resource = LabOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
