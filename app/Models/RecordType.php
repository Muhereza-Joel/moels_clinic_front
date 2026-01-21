<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordType extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization, Auditable;

    protected $fillable = [
        'code',
        'label'
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }


    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
