<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use App\Traits\RecalculatesInvoiceTotals;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization, RecalculatesInvoiceTotals;

    protected $table = 'invoices';

    protected $fillable = [
        'uuid',
        'organization_id',
        'patient_id',
        'visit_id',
        'invoice_number',
        'status',
        'subtotal_amount',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'issued_at',
        'due_at',
        'notes',
    ];

    protected $casts = [
        'subtotal_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime'
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Generate invoice number before creating
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });

        // Recalculate totals when discount changes
        static::updated(function ($invoice) {
            if ($invoice->isDirty('discount_amount')) {
                $invoice->recalculateTotals();
            }
        });
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-';
        $year = date('Y');
        $month = date('m');

        // Get last invoice number for this month
        $lastInvoice = static::where('invoice_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}{$year}{$month}{$newNumber}";
    }


    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Computed Attributes
    public function getBalanceAttribute()
    {
        $paid = $this->payments->sum('amount');
        return $this->total_amount - $paid;
    }

    public function getIsPaidAttribute()
    {
        return $this->balance <= 0;
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['issued', 'partially_paid']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
            ->whereIn('status', ['issued', 'partially_paid']);
    }

    // In App\Models\Invoice.php
    public function recalculateTotals(): void
    {
        // Calculate subtotal from all invoice items
        $this->subtotal_amount = $this->invoiceItems()->sum('total_amount');

        // Calculate tax (assuming percentage based on subtotal)
        // You can adjust this logic based on your tax calculation needs
        $taxRate = 0; // 18% tax rate for example
        $this->tax_amount = $this->subtotal_amount * $taxRate;

        // Calculate total
        $this->total_amount = $this->subtotal_amount + $this->tax_amount - $this->discount_amount;

        // Save without triggering events to avoid infinite loops
        $this->saveQuietly();
    }
}
