<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\DeliveryStatus;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'uuid',
        'organization_id',
        'order_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'payment_status',
        'delivery_status',
        'total_items',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'payment_due_date',
        'payment_date',
        'shipping_method',
        'tracking_number',
        'shipping_address',
        'has_quality_issues',
        'quality_notes',
        'rejected_items_count',
        'rejected_items_value',
        'estimated_lead_time_days',
        'actual_lead_time_days',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'received_by',
        'received_at',
        'receiving_notes',
        'notes',
        'internal_notes',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'payment_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',

        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'rejected_items_value' => 'decimal:2',

        'total_items' => 'integer',
        'rejected_items_count' => 'integer',
        'estimated_lead_time_days' => 'integer',
        'actual_lead_time_days' => 'integer',
        'days_overdue' => 'integer',
        'delivery_delay_days' => 'integer',

        'has_quality_issues' => 'boolean',
        'attachments' => 'array',

        'status' => PurchaseOrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'delivery_status' => DeliveryStatus::class,
    ];

    protected $appends = [
        'is_overdue',
        'is_delayed',
        'progress_percentage',
        'status_label',
        'payment_status_label',
        'delivery_status_label',
        'formatted_total_amount',
        'formatted_amount_due',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (! $order->order_number) {
                $order->order_number = 'PO-' . now()->format('Ymd-His');
            }
        });
    }


    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived($query)
    {
        return $query->whereIn('status', ['partially_received', 'fully_received']);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('payment_due_date', '<', now())
                    ->whereIn('payment_status', ['pending', 'partial']);
            });
    }

    public function scopeDelayed($query)
    {
        return $query->where('delivery_status', 'delayed')
            ->orWhere(function ($q) {
                $q->where('expected_delivery_date', '<', now())
                    ->whereNotIn('delivery_status', ['delivered', 'cancelled']);
            });
    }

    public function scopeWithQualityIssues($query)
    {
        return $query->where('has_quality_issues', true);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->orWhere('tracking_number', 'like', "%{$search}%")
                ->orWhere('payment_reference', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if (!$this->payment_due_date) {
            return false;
        }

        if (in_array($this->payment_status, ['paid', 'overdue'])) {
            return $this->payment_status === 'overdue';
        }

        return now()->greaterThan($this->payment_due_date) && $this->amount_due > 0;
    }

    public function getIsDelayedAttribute(): bool
    {
        if (!$this->expected_delivery_date) {
            return false;
        }

        if (in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            return $this->delivery_status === 'delayed';
        }

        return now()->greaterThan($this->expected_delivery_date) &&
            !in_array($this->status, ['fully_received', 'cancelled', 'closed']);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->status === 'draft') return 0;
        if ($this->status === 'pending_approval') return 25;
        if ($this->status === 'approved') return 50;
        if ($this->status === 'ordered') return 75;
        if (in_array($this->status, ['partially_received', 'fully_received'])) return 100;
        if ($this->status === 'cancelled') return 0;
        if ($this->status === 'closed') return 100;

        return 0;
    }

    public function getStatusLabelAttribute(): string
    {
        return PurchaseOrderStatus::labels()[$this->status->value] ?? ucfirst($this->status->value);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return PaymentStatus::labels()[$this->payment_status->value] ?? ucfirst($this->payment_status->value);
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return DeliveryStatus::labels()[$this->delivery_status->value] ?? ucfirst($this->delivery_status->value);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2);
    }

    public function getFormattedAmountDueAttribute(): string
    {
        return number_format($this->amount_due, 2);
    }

    public function getQualityStatusAttribute(): string
    {
        if ($this->has_quality_issues) {
            return 'Has Issues';
        }

        if ($this->rejected_items_count > 0) {
            return 'Partial Rejection';
        }

        return 'Good';
    }

    public function getDaysOverdueAttribute()
    {
        return $this->payment_due_date
            ? max(0, now()->diffInDays($this->payment_due_date))
            : null;
    }

    public function getDeliveryDelayDaysAttribute()
    {
        return ($this->actual_delivery_date && $this->expected_delivery_date)
            ? $this->actual_delivery_date->diffInDays($this->expected_delivery_date)
            : null;
    }


    // Business Logic Methods
    public function calculateTotals(): void
    {
        $items = $this->items()->get();

        $this->total_items = $items->sum('quantity');
        $this->subtotal = $items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        // Recalculate discount if needed
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost - $this->discount_amount;
        $this->amount_due = $this->total_amount - $this->amount_paid;

        // Update payment status
        $this->updatePaymentStatus();
        $this->updateDeliveryStatus();

        $this->save();
    }

    public function updatePaymentStatus(): void
    {
        if ($this->amount_due <= 0) {
            $this->payment_status = PaymentStatus::PAID;
        } elseif ($this->amount_paid > 0 && $this->amount_paid < $this->total_amount) {
            $this->payment_status = PaymentStatus::PARTIAL;
        } elseif ($this->is_overdue) {
            $this->payment_status = PaymentStatus::OVERDUE;
        } else {
            $this->payment_status = PaymentStatus::PENDING;
        }
    }

    public function updateDeliveryStatus(): void
    {
        $totalItems = $this->items()->sum('quantity');
        $receivedItems = $this->items()->sum('quantity_received');

        if ($receivedItems >= $totalItems && $totalItems > 0) {
            $this->delivery_status = DeliveryStatus::DELIVERED;
            $this->status = PurchaseOrderStatus::FULLY_RECEIVED;

            // Calculate actual lead time
            if ($this->order_date && $this->actual_delivery_date) {
                $this->actual_lead_time_days = $this->order_date->diffInDays($this->actual_delivery_date);

                // Check if delivery was on time
                if ($this->expected_delivery_date) {
                    $this->delivery_status = $this->actual_delivery_date <= $this->expected_delivery_date
                        ? DeliveryStatus::ON_TIME
                        : DeliveryStatus::DELAYED;
                }
            }
        } elseif ($receivedItems > 0) {
            $this->delivery_status = DeliveryStatus::PARTIALLY_DELIVERED;
            $this->status = PurchaseOrderStatus::PARTIALLY_RECEIVED;
        } elseif ($this->status === 'ordered') {
            $this->delivery_status = DeliveryStatus::PROCESSING;
        }

        // Update supplier rating
        if ($this->status === PurchaseOrderStatus::FULLY_RECEIVED) {
            $this->supplier->updateRating();
        }
    }

    public function receiveItem($itemId, $quantityReceived, $batchNumber = null, $expiryDate = null, $qualityNotes = null): bool
    {
        $item = $this->items()->findOrFail($itemId);

        // Check if quantity is valid
        $maxReceivable = $item->quantity - $item->quantity_received;
        if ($quantityReceived > $maxReceivable) {
            return false;
        }

        // Update item
        $item->quantity_received += $quantityReceived;
        $item->save();

        // Update drug stock
        $drug = $item->drug;
        if ($drug) {
            $drug->increaseStock($quantityReceived, $batchNumber, $expiryDate);

            // Create inventory movement
            InventoryMovement::create([
                'drug_id' => $drug->id,
                'purchase_order_id' => $this->id,
                'movement_type' => 'purchase_receipt',
                'quantity_before' => $drug->stock_quantity - $quantityReceived,
                'quantity_after' => $drug->stock_quantity,
                'quantity_change' => $quantityReceived,
                'unit_price' => $item->unit_price,
                'total_value' => $quantityReceived * $item->unit_price,
                'batch_number' => $batchNumber,
                'expiry_date' => $expiryDate,
                'notes' => $qualityNotes,
                'created_by' => auth()->id(),
            ]);
        }

        // Update order totals and status
        $this->actual_delivery_date = now();
        $this->received_by = auth()->id();
        $this->received_at = now();

        if ($qualityNotes) {
            $this->has_quality_issues = true;
            $this->quality_notes = $qualityNotes;
            $this->rejected_items_count += ($item->quantity - $quantityReceived);
            $this->rejected_items_value += ($item->quantity - $quantityReceived) * $item->unit_price;
        }

        $this->updateDeliveryStatus();
        $this->calculateTotals();

        return true;
    }

    public function approve($approvedBy, $notes = null): bool
    {
        if ($this->status !== PurchaseOrderStatus::PENDING_APPROVAL) {
            return false;
        }

        $this->status = PurchaseOrderStatus::APPROVED;
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->approval_notes = $notes;

        return $this->save();
    }

    public function cancel($reason = null): bool
    {
        if (!in_array($this->status, [PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::PENDING_APPROVAL, PurchaseOrderStatus::APPROVED])) {
            return false;
        }

        $this->status = PurchaseOrderStatus::CANCELLED;
        $this->delivery_status = DeliveryStatus::CANCELLED;
        $this->notes = $reason ? ($this->notes . "\nCancelled: " . $reason) : $this->notes;

        return $this->save();
    }

    public function recordPayment($amount, $method, $reference, $date = null): bool
    {
        if ($amount <= 0 || $amount > $this->amount_due) {
            return false;
        }

        $this->amount_paid += $amount;
        $this->payment_method = $method;
        $this->payment_reference = $reference;
        $this->payment_date = $date ?? now();

        $this->updatePaymentStatus();

        // Create payment record
        PurchasePayment::create([
            'purchase_order_id' => $this->id,
            'amount' => $amount,
            'payment_method' => $method,
            'reference_number' => $reference,
            'payment_date' => $this->payment_date,
            'notes' => "Payment for PO: {$this->order_number}",
            'created_by' => auth()->id(),
        ]);

        return $this->save();
    }

    // Static Methods
    public static function generateOrderNumber(): string
    {
        $prefix = 'PO';
        $year = date('Y');
        $month = date('m');

        $lastOrder = self::where('order_number', 'like', "{$prefix}-{$year}{$month}-%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}-{$year}{$month}-{$nextNumber}";
    }

    public static function getStatistics($organizationId = null): array
    {
        $query = $organizationId ? self::where('organization_id', $organizationId) : self::query();

        $total = $query->count();
        $totalValue = $query->sum('total_amount');
        $pending = $query->where('status', 'pending_approval')->count();
        $overdue = $query->where('payment_status', 'overdue')->count();
        $delayed = $query->where('delivery_status', 'delayed')->count();

        return [
            'total_orders' => $total,
            'total_value' => $totalValue,
            'pending_approval' => $pending,
            'overdue_payments' => $overdue,
            'delayed_deliveries' => $delayed,
            'avg_order_value' => $total > 0 ? $totalValue / $total : 0,
        ];
    }
}
