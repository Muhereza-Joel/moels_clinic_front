<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization, Auditable;

    protected $fillable = [
        'uuid',
        'organization_id',
        'invoice_id',
        'payment_id',       // optional: link to original payment
        'type',             // refund or adjustment
        'amount',
        'reason',
        'currency',
        'recorded_by',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // In your Payment model (or the model for this form)
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id'); // if it's a parent payment
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}
