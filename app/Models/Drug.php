<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Drug extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Auditable;

    protected $table = 'drugs';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        // Core
        'uuid',
        'organization_id',
        'drug_code',
        'name',
        'generic_name',
        'brand_name',
        'manufacturer',
        'form',
        'strength',

        // Classification
        'category_id',
        'subcategory_id',
        'therapeutic_class',
        'pharmacologic_class',

        // Pharmaceutical details
        'unit_of_measure',
        'units_per_pack',

        // Inventory
        'stock_quantity',
        'reorder_level',
        'reorder_quantity',
        'maximum_stock',
        'unit_price',
        'cost_price',
        'selling_price',
        'wholesale_price',

        // Batch / expiry
        'batch_number',
        'expiry_date',
        'manufacture_date',

        // Storage
        'storage_condition',
        'storage_location',
        'storage_instructions',

        // Regulatory
        'regulatory_number',
        'requires_prescription',
        'is_controlled_substance',
        'controlled_schedule',
        'is_dangerous_drug',

        // Clinical
        'indications',
        'contraindications',
        'side_effects',
        'dosage_instructions',
        'administration_route',
        'special_precautions',

        // Supplier
        'primary_supplier_id',
        'secondary_supplier_id',
        'supplier_code',
        'lead_time_days',

        // Usage tracking
        'minimum_order_quantity',
        'maximum_order_quantity',
        'monthly_usage',
        'last_purchase_date',
        'last_dispensed_date',

        // Status & flags
        'is_active',
        'is_discontinued',
        'discontinued_date',
        'discontinued_reason',
        'is_branded',
        'is_generic',

        // Meta
        'notes',
        'alternative_names',
    ];

    /**
     * Attribute casting
     */
    protected $casts = [
        // Numbers
        'stock_quantity' => 'integer',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'maximum_stock' => 'integer',
        'units_per_pack' => 'integer',
        'monthly_usage' => 'integer',
        'lead_time_days' => 'integer',

        // Prices
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',

        // Dates
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'last_purchase_date' => 'date',
        'last_dispensed_date' => 'date',
        'discontinued_date' => 'date',

        // Booleans
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'is_dangerous_drug' => 'boolean',
        'is_discontinued' => 'boolean',
        'is_branded' => 'boolean',
        'is_generic' => 'boolean',

        // JSON
        'alternative_names' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Drug $drug) {
            if (empty($drug->drug_code)) {
                $drug->drug_code = self::generateDrugCode();
            }
        });
    }




    public static function generateDrugCode(): string
    {
        $suffixLength = 6;
        $max = (36 ** $suffixLength) - 1;

        do {
            $rand = random_int(0, $max);
            $base36 = base_convert($rand, 10, 36);

            $suffix = strtoupper(
                str_pad($base36, $suffixLength, '0', STR_PAD_LEFT)
            );

            $code = "DRG-{$suffix}";
        } while (self::where('drug_code', $code)->exists());

        return $code;
    }


    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function category()
    {
        return $this->belongsTo(DrugCategory::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(DrugCategory::class, 'subcategory_id');
    }

    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function secondarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'secondary_supplier_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class, 'drug_code', 'drug_code');
    }

    /* -----------------------------------------------------------------
     |  Query Scopes
     | -----------------------------------------------------------------
     */

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
            ->where('is_discontinued', false);
    }

    public function scopeLowStock(Builder $query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->where('is_active', true);
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeRequiresPrescription(Builder $query)
    {
        return $query->where('requires_prescription', true);
    }

    public function scopeControlled(Builder $query)
    {
        return $query->where('is_controlled_substance', true);
    }

    /* -----------------------------------------------------------------
     |  Business Logic Helpers
     | -----------------------------------------------------------------
     */

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->reorder_level;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null &&
            $this->expiry_date->isPast();
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->expiry_date !== null &&
            $this->expiry_date->between(now(), now()->addDays($days));
    }

    public function needsReorder(): bool
    {
        return $this->isLowStock() && !$this->is_discontinued;
    }

    public function increaseStock(int $quantity): bool
    {
        $this->increment('stock_quantity', $quantity);
        return true;
    }

    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock_quantity < $quantity) {
            return false;
        }

        $this->decrement('stock_quantity', $quantity);
        $this->last_dispensed_date = now();
        $this->save();

        return true;
    }

    public function discontinue(string $reason = null): bool
    {
        $this->update([
            'is_discontinued' => true,
            'discontinued_date' => now(),
            'discontinued_reason' => $reason,
            'is_active' => false,
        ]);

        return true;
    }
}
