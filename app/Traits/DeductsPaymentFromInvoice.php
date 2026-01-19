<?php

namespace App\Traits;

use App\Models\Invoice;

trait DeductsPaymentFromInvoice
{
    /**
     * Boot the trait.
     */
    protected static function bootDeductsPaymentFromInvoice(): void
    {
        static::created(function ($payment) {
            if (! $payment->invoice_id) {
                return;
            }

            $invoice = Invoice::find($payment->invoice_id);

            if ($invoice) {
                // Deduct the payment amount from the invoice total_amount
                $invoice->decrement('total_amount', $payment->amount);

                // Optionally, update status if fully paid
                if ($invoice->total_amount <= 0) {
                    $invoice->updateQuietly(['status' => 'paid']);
                }
            }
        });

        static::deleted(function ($payment) {
            if (! $payment->invoice_id) {
                return;
            }

            $invoice = Invoice::find($payment->invoice_id);

            if ($invoice) {
                // Restore the amount if a payment is deleted
                $invoice->increment('total_amount', $payment->amount);

                // Optionally, reset status back to 'unpaid'
                if ($invoice->total_amount > 0) {
                    $invoice->updateQuietly(['status' => 'unpaid']);
                }
            }
        });
    }
}
