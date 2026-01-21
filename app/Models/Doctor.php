<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doctor extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Auditable;

    protected $table = 'doctors';

    protected $fillable = [
        'uuid',
        'organization_id',
        'user_id',
        'specialty',
        'license_number',
        'working_hours_json',
    ];

    protected $casts = [
        'working_hours_json' => 'array'
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'prescribed_by');
    }

    // Scopes
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', $specialty);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }
}
