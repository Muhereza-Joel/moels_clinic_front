<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Patient extends BaseModel
{
    use HasFactory, BelongsToOrganization, SoftDeletes;

    protected $table = 'patients';

    protected $fillable = [
        'uuid',
        'organization_id',
        'mrn',
        'first_name',
        'last_name',
        'sex',
        'date_of_birth',
        'national_id',
        'email',
        'phone',
        'address',
        'emergency_contact',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_of_birth' => 'date',
        'emergency_contact' => 'array'
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Automatically generate MRN when creating a patient.
     */
    protected static function booted(): void
    {
        static::creating(function (Patient $patient) {
            if (empty($patient->mrn)) {
                $patient->mrn = self::generateMrn();
            }
        });
    }

    /**
     * Generate MRN using the current user's organization code as prefix.
     *
     * Example: KLA-PT-0F3A9K
     */
    public static function generateMrn(): string
    {
        $user = Auth::user();

        if (! $user || ! $user->organization) {
            throw new \RuntimeException('Cannot generate MRN: user or organization not available.');
        }

        $orgCode = strtoupper($user->organization->code);
        $patientPrefix = config('patients.patient_prefix', 'PT-');
        $suffixLength = 6;

        $max = (36 ** $suffixLength) - 1;

        do {
            $rand = random_int(0, $max);
            $base36 = base_convert($rand, 10, 36);

            $suffix = strtoupper(
                str_pad($base36, $suffixLength, '0', STR_PAD_LEFT)
            );

            $mrn = "{$orgCode}-{$patientPrefix}{$suffix}";
        } while (self::where('mrn', $mrn)->exists());

        return $mrn;
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'recipient_patient_id');
    }

    /**
     * Return full name for display purposes
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('mrn', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });
    }
}
