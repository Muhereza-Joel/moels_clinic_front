<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends BaseModel
{
    use HasFactory, HasUuid, BelongsToOrganization, SoftDeletes, Auditable;

    protected $table = 'prescriptions';

    protected $fillable = [
        'uuid',
        'organization_id',
        'visit_id',
        'prescribed_by',
        'status',
        'notes',
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

    public function prescribedBy()
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    // Scopes
    public function scopeDispensable($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', 'dispensed');
    }
}
