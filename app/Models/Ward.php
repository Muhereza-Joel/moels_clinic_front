<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ward extends BaseModel
{
    use HasFactory, HasUuid, SoftDeletes, BelongsToOrganization, Auditable;

    protected $fillable = [
        'uuid',
        'organization_id',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
