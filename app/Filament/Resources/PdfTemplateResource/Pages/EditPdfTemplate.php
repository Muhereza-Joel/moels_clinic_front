<?php

namespace App\Filament\Resources\PdfTemplateResource\Pages;

use App\Filament\Resources\PdfTemplateResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Illuminate\Support\Facades\File;

class EditPdfTemplate extends EditRecord
{
    protected static string $resource = PdfTemplateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('restoreDefault')
                ->label('Restore Default JSON')
                ->requiresConfirmation()
                ->action(fn($record) => $this->restoreDefault($record)),

            Actions\Action::make('preview')
                ->label('Preview PDF')
                ->url(fn($record) => route('pdf.preview', $record))
                ->openUrlInNewTab(),
        ];
    }

    protected function restoreDefault($record)
    {
        $path = resource_path("templates/pdf/defaults/{$record->code}.json");
        if (file_exists($path)) {
            $record->layout = json_decode(file_get_contents($path), true);
            $record->save();
            $this->notify('success', 'Default template restored.');
        } else {
            $this->notify('danger', 'No default template found.');
        }
    }
}
