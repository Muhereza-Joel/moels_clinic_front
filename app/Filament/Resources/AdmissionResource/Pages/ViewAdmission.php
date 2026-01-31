<?php

namespace App\Filament\Resources\AdmissionResource\Pages;

use App\Filament\Resources\AdmissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAdmission extends ViewRecord
{
    protected static string $resource = AdmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
