<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends BaseModel
{
    use HasFactory, SoftDeletes, BelongsToOrganization, Auditable;

    protected $table = 'rooms';

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
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

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class);
    }


    // Scopes
    public function scopeAvailable($query, $start, $end)
    {
        return $query->whereDoesntHave('appointments', function ($q) use ($start, $end) {
            $q->where(function ($query) use ($start, $end) {
                $query->whereBetween('scheduled_start', [$start, $end])
                    ->orWhereBetween('scheduled_end', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('scheduled_start', '<', $start)
                            ->where('scheduled_end', '>', $end);
                    });
            })->whereNotIn('status', ['cancelled']);
        });
    }
}
