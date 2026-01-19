<?php

namespace App\Traits;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

trait RecalculatesInvoiceTotals
{
    /**
     * Recalculate invoice totals from items
     */
    public function recalculateTotals(bool $save = true): array
    {
        // Calculate subtotal from all invoice items
        $subtotal = $this->invoiceItems()->sum('total_amount');

        // Get tax rate - you might want to store this per invoice or in settings
        $taxRate = $this->tax_rate ?? config('app.default_tax_rate', 0);

        // Calculate tax
        $tax = $subtotal * $taxRate;

        // Get discount (already stored on invoice)
        $discount = $this->discount_amount ?? 0;

        // Calculate total
        $total = $subtotal + $tax - $discount;

        // Update the model instance
        $this->subtotal_amount = $subtotal;
        $this->tax_amount = $tax;
        $this->total_amount = $total;

        if ($save) {
            // Use updateQuietly to avoid triggering model events
            $this->updateQuietly([
                'subtotal_amount' => $subtotal,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'updated_at' => now(),
            ]);
        }

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
        ];
    }

    /**
     * Recalculate totals with database lock to prevent race conditions
     */
    public function recalculateTotalsWithLock(): array
    {
        return DB::transaction(function () {
            // Lock the invoice for update
            $invoice = Invoice::lockForUpdate()->find($this->id);

            if (!$invoice) {
                throw new \Exception("Invoice not found");
            }

            // Calculate totals using the locked invoice instance
            $result = $invoice->recalculateTotals();

            // Refresh the current instance with updated data
            $this->refresh();

            return $result; // Explicitly return the result
        });
    }

    /**
     * Recalculate totals without locking
     */
    public function recalculateTotalsSimple(): array
    {
        // This is a simpler version without locking
        return $this->recalculateTotals();
    }

    /**
     * Quick recalculate and save
     */
    public function quickRecalculate(): self
    {
        $this->recalculateTotals();
        return $this;
    }

    /**
     * Get invoice summary for display
     */
    public function getInvoiceSummary(): array
    {
        return [
            'items_count' => $this->invoiceItems()->count(),
            'subtotal' => $this->subtotal_amount,
            'tax' => $this->tax_amount,
            'discount' => $this->discount_amount,
            'total' => $this->total_amount,
            'currency' => $this->currency,
            'last_updated' => optional($this->updated_at)->diffForHumans() ?? 'Never',
        ];
    }

    /**
     * Get formatted totals for display
     */
    public function getFormattedTotals(): array
    {
        $summary = $this->getInvoiceSummary();

        return [
            'subtotal_formatted' => number_format($summary['subtotal'], 2),
            'tax_formatted' => number_format($summary['tax'], 2),
            'discount_formatted' => number_format($summary['discount'], 2),
            'total_formatted' => number_format($summary['total'], 2),
            'currency_symbol' => $this->getCurrencySymbol(),
        ];
    }

    /**
     * Get currency symbol based on currency code
     */
    public function getCurrencySymbol(): string
    {
        return match ($this->currency) {
            'UGX' => 'UGX',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'KES' => 'KSh',
            default => $this->currency,
        };
    }

    /**
     * Check if invoice needs recalculation
     */
    public function needsRecalculation(): bool
    {
        // Calculate current totals from items
        $subtotal = $this->invoiceItems()->sum('total_amount');
        $taxRate = $this->tax_rate ?? config('app.default_tax_rate', 0.18);
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax - ($this->discount_amount ?? 0);

        // Compare with stored values
        return abs($this->subtotal_amount - $subtotal) > 0.01 ||
            abs($this->tax_amount - $tax) > 0.01 ||
            abs($this->total_amount - $total) > 0.01;
    }
}
