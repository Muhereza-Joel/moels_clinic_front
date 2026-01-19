<?php

namespace App\Filament\Resources\CptCodeResource\Pages;

use App\Filament\Resources\CptCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCptCode extends ViewRecord
{
    protected static string $resource = CptCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
