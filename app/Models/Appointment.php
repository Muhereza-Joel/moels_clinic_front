<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Auditable;

    protected $table = 'appointments';

    protected $fillable = [
        'uuid',
        'sequence',
        'organization_id',
        'patient_id',
        'doctor_id',
        'room_id',
        'scheduled_start',
        'scheduled_end',
        'status',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime'
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function booted(): void
    {
        static::creating(function ($appointment) {
            if (!$appointment->sequence) {
                $appointment->sequence = static::generateSequence();
            }
        });
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

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function visit()
    {
        return $this->hasOne(Visit::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_start', '>', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_start', today())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    protected static function generateSequence(): string
    {
        $prefix = 'APT';

        // Use scheduled_start or now if not set
        $date = now();
        if (!empty(static::$creatingScheduledStart ?? null)) {
            $date = static::$creatingScheduledStart;
        }

        $datePart = $date->format('Ymd'); // YYYYMMDD
        $timePart = $date->format('Hi');  // HHMM

        // Count existing appointments for the same day
        $dailyCount = static::whereDate('scheduled_start', $date->toDateString())->count() + 1;

        // Pad the counter to 3 digits
        $counter = str_pad($dailyCount, 3, '0', STR_PAD_LEFT);

        // Combine: APT-YYYYMMDD-HHMM-XXX
        return "{$prefix}-{$datePart}-{$timePart}-{$counter}";
    }
}
