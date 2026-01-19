<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function appointmentsCreated()
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }

    public function medicalRecordsAuthored()
    {
        return $this->hasMany(MedicalRecord::class, 'authored_by');
    }

    public function labOrdersOrdered()
    {
        return $this->hasMany(LabOrder::class, 'ordered_by');
    }

    public function paymentsRecorded()
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'performed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDoctors($query)
    {
        return $query->hasRole('doctor');
    }

    public function scopeNurses($query)
    {
        return $query->hasRole('nurse');
    }
}
