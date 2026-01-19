<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceItem extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization;

    protected $table = 'invoice_items';

    protected $fillable = [
        'uuid',
        'invoice_id',
        'item_type',
        'description',
        'unit_price',
        'quantity',
        'total_amount',
        'metadata_json',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'metadata_json' => 'array'
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Recalculate invoice totals when item changes
        static::saved(function ($item) {
            if ($item->invoice) {
                $item->invoice->recalculateTotals();
            }
        });

        static::deleted(function ($item) {
            if ($item->invoice) {
                $item->invoice->recalculateTotals();
            }
        });

        static::restored(function ($item) {
            if ($item->invoice) {
                $item->invoice->recalculateTotals();
            }
        });
    }

    /**
     * Calculate total amount
     */
    public function calculateTotal(): float
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Update total before saving
     */
    public function updateTotal(): void
    {
        $this->total_amount = $this->calculateTotal();
    }

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}
