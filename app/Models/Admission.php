<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, Auditable;

    protected $fillable = [
        'uuid',
        'patient_id',
        'organization_id',
        'ward_id',
        'room_id',
        'admitted_at',
        'discharged_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
