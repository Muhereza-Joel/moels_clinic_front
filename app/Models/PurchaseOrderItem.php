<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization, Auditable;

    protected $fillable = [
        'purchase_order_id',
        'drug_id',
        'item_type',
        'drug_code',
        'drug_name',
        'item_name',
        'strength',
        'form',
        'quantity',
        'quantity_received',
        'unit_price',
        'total_price',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->quantity_received;
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->quantity_received >= $this->quantity;
    }

    public function getReceivedPercentageAttribute(): float
    {
        return $this->quantity > 0 ? ($this->quantity_received / $this->quantity) * 100 : 0;
    }
}
