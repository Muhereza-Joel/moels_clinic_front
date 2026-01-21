<?php

namespace App\Filament\Resources\LabOrderResource\Pages;

use App\Filament\Resources\LabOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLabOrder extends EditRecord
{
    protected static string $resource = LabOrderResource::class;

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
