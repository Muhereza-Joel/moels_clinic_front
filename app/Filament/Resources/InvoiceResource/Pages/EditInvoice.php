<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('refresh')
                ->label('Refresh Totals')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $this->redirect(request()->header('Referer'));
                })
                ->modalHeading('Refresh Invoice Totals')
                ->modalDescription('This will reload the latest totals from the database.')
                ->modalSubmitActionLabel('Refresh Now')
                ->visible(fn() => $this->record->invoiceItems()->exists()),

            Actions\Action::make('print')
                ->label('Print Invoice')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('invoices.print', $this->record))
                ->openUrlInNewTab(),


            Actions\Action::make('preview')
                ->label('Preview PDF')
                ->icon('heroicon-o-document')
                ->color('info')
                ->url(fn() => route('invoices.preview', $this->record))
                ->openUrlInNewTab(),

            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Invoice updated successfully. Totals have been recalculated.';
    }

    protected function afterSave(): void
    {
        // Recalculate totals after saving
        $this->getRecord()->recalculateTotals();

        // Clear any cache for this invoice
        Cache::forget("invoice_{$this->getRecord()->id}_totals");
    }
}
