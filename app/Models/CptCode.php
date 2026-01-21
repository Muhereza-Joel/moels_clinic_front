<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CptCode extends BaseModel
{

    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, Auditable;

    protected $table = 'cpt_codes';

    protected $fillable = [
        'uuid',
        'organization_id',
        'code',
        'description',
        'category',
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
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'cpt_code', 'code');
    }
}
