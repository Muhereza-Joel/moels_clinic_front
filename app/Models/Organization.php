<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends BaseModel
{
    use HasFactory, HasUuid;

    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
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

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }
}
