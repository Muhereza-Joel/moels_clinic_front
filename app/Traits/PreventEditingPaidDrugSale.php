<?php

namespace App\Traits;

use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

trait PreventEditingPaidDrugSale
{
    /**
     * Prevent updating if the record is paid.
     *
     * @throws ValidationException
     */
    public function preventEditingPaid(): void
    {
        if (isset($this->payment_status) && $this->payment_status === 'paid') {
            // Send a Filament notification
            Notification::make()
                ->title('Action Denied')
                ->body('This sale has already been paid and cannot be edited.')
                ->danger()
                ->send();

            // Throw a validation exception to stop backend execution
            throw ValidationException::withMessages([
                'payment_status' => 'This sale has already been paid and cannot be edited.',
            ]);
        }
    }
}
