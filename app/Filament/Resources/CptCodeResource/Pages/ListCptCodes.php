<?php

namespace App\Filament\Resources\CptCodeResource\Pages;

use App\Filament\Resources\CptCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCptCodes extends ListRecords
{
    protected static string $resource = CptCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
