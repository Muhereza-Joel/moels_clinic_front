<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Auditable;

    protected $table = 'visits';

    protected $fillable = [
        'uuid',
        'organization_id',
        'patient_id',
        'appointment_id',
        'doctor_id',
        'visit_date',
        'status',
        'chief_complaint',
        'triage_json',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'triage_json' => 'array'
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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }
}
