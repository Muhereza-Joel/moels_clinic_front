<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabOrder extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, Auditable;

    protected $table = 'lab_orders';

    protected $fillable = [
        'uuid',
        'organization_id',
        'visit_id',
        'patient_id',
        'ordered_by',
        'order_date',
        'panel_code',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime'
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

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function results()
    {
        return $this->hasMany(LabResult::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['ordered', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
