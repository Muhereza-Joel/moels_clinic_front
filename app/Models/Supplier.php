<?php

namespace App\Models;

use App\Enums\SupplierRating;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization;

    protected $table = 'suppliers';

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'alternative_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'registration_number',
        'payment_terms',
        'credit_limit',
        'payment_days',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'is_active',
        'is_preferred',
        'rating',
        'notes',
        'website',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_days' => 'integer',
        'is_active' => 'boolean',
        'is_preferred' => 'boolean',
        'rating' => SupplierRating::class,
    ];

    protected $appends = [
        'full_address',
        'contact_info',
        'status_label',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Relationships
    public function primaryDrugs()
    {
        return $this->hasMany(Drug::class, 'primary_supplier_id');
    }

    public function secondaryDrugs()
    {
        return $this->hasMany(Drug::class, 'secondary_supplier_id');
    }

    public function allDrugs()
    {
        return Drug::where(function ($query) {
            $query->where('primary_supplier_id', $this->id)
                ->orWhere('secondary_supplier_id', $this->id);
        });
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeWithAddress($query)
    {
        return $query->whereNotNull('address')
            ->whereNotNull('city');
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->country,
            $this->postal_code,
        ]);

        return implode(', ', $parts) ?: 'No address provided';
    }

    public function getContactInfoAttribute(): string
    {
        $parts = [];

        if ($this->contact_person) {
            $parts[] = $this->contact_person;
        }

        if ($this->email) {
            $parts[] = $this->email;
        }

        if ($this->phone) {
            $parts[] = $this->phone;
        }

        return implode(' | ', $parts);
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if ($this->is_preferred) {
            return 'Preferred';
        }

        return 'Active';
    }

    public function getBankInfoAttribute(): ?string
    {
        if (!$this->bank_name || !$this->bank_account_number) {
            return null;
        }

        $parts = [
            $this->bank_name,
            $this->bank_account_name,
            $this->bank_account_number,
            $this->bank_branch,
        ];

        return implode(' | ', array_filter($parts));
    }

    public function getDrugCountAttribute(): int
    {
        return $this->allDrugs()->count();
    }

    public function getLastPurchaseDateAttribute(): ?string
    {
        $latestOrder = $this->purchaseOrders()->latest()->first();
        return $latestOrder ? $latestOrder->created_at->format('Y-m-d') : null;
    }

    // Business Logic
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isPreferred(): bool
    {
        return $this->is_active && $this->is_preferred;
    }

    public function canBeDeleted(): bool
    {
        return $this->allDrugs()->count() === 0 && $this->purchaseOrders()->count() === 0;
    }

    public function updateRating(): void
    {
        // Calculate rating based on purchase orders performance
        $orders = $this->purchaseOrders()->where('status', 'completed')->get();

        if ($orders->isEmpty()) {
            $this->rating = null;
            $this->save();
            return;
        }

        $totalOrders = $orders->count();
        $onTimeOrders = $orders->where('delivery_status', 'on_time')->count();
        $qualityIssues = $orders->where('has_quality_issues', true)->count();

        $onTimeRate = ($onTimeOrders / $totalOrders) * 100;
        $qualityRate = (($totalOrders - $qualityIssues) / $totalOrders) * 100;

        $averageScore = ($onTimeRate + $qualityRate) / 2;

        if ($averageScore >= 90) {
            $this->rating = SupplierRating::EXCELLENT;
        } elseif ($averageScore >= 75) {
            $this->rating = SupplierRating::GOOD;
        } elseif ($averageScore >= 60) {
            $this->rating = SupplierRating::AVERAGE;
        } else {
            $this->rating = SupplierRating::POOR;
        }

        $this->save();
    }

    public function getPerformanceMetrics(): array
    {
        $orders = $this->purchaseOrders()->where('status', 'completed')->get();

        if ($orders->isEmpty()) {
            return [
                'total_orders' => 0,
                'total_spent' => 0,
                'on_time_rate' => 0,
                'quality_rate' => 0,
                'average_lead_time' => 0,
            ];
        }

        $totalSpent = $orders->sum('total_amount');
        $onTimeOrders = $orders->where('delivery_status', 'on_time')->count();
        $qualityIssues = $orders->where('has_quality_issues', true)->count();
        $averageLeadTime = $orders->avg('actual_lead_time_days');

        return [
            'total_orders' => $orders->count(),
            'total_spent' => $totalSpent,
            'on_time_rate' => ($onTimeOrders / $orders->count()) * 100,
            'quality_rate' => (($orders->count() - $qualityIssues) / $orders->count()) * 100,
            'average_lead_time' => round($averageLeadTime, 1),
        ];
    }

    // Validation Rules
    public static function getValidationRules($id = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code,' . $id . ',id,organization_id,' . auth()->user()->organization_id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_days' => 'nullable|integer|min:0|max:365',
            'is_active' => 'boolean',
            'is_preferred' => 'boolean',
        ];
    }
}
