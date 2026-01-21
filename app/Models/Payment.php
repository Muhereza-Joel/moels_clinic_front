<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\DeductsPaymentFromInvoice;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, DeductsPaymentFromInvoice, Auditable;

    protected $table = 'payments';

    protected $fillable = [
        'uuid',
        'organization_id',
        'invoice_id',
        'method',
        'amount',
        'currency',
        'paid_at',
        'reference',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }



    public function canDelete(): bool
    {
        return !in_array($this->status, ['paid', 'void']);
    }
}
