<?php

namespace App\Filament\Resources\CptCodeResource\Pages;

use App\Filament\Resources\CptCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCptCode extends EditRecord
{
    protected static string $resource = CptCodeResource::class;

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
