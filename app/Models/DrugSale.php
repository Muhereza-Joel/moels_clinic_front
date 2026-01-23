<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class DrugSale extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, Auditable;

    protected $table = 'drug_sales';

    protected $fillable = [
        'uuid',
        'organization_id',
        'drug_id',
        'patient_id',
        'customer_name',
        'customer_contact',
        'quantity',
        'unit_price',
        'total_price',
        'sale_date',
        'user_id',
        'payment_method',
        'payment_status',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        // When a sale is created, decrease stock
        static::created(function (DrugSale $sale) {
            if ($sale->drug) {
                $sale->drug->decreaseStock($sale->quantity);
            }
        });

        // When a sale is deleted (soft delete or hard delete), restore stock
        static::deleted(function (DrugSale $sale) {
            if ($sale->drug) {
                $sale->drug->increaseStock($sale->quantity);
            }
        });

        // When a soft-deleted sale is restored, decrease stock again
        static::restored(function (DrugSale $sale) {
            if ($sale->drug) {
                $sale->drug->decreaseStock($sale->quantity);
            }
        });
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
