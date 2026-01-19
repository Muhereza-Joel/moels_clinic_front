<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization;

    protected $table = 'medical_records';

    protected $fillable = [
        'uuid',
        'organization_id',
        'visit_id',
        'patient_id',
        'record_type_id',
        'title',
        'content',
        'data_json',
        'authored_by',
        'icd10_code',
        'cpt_code',
    ];

    protected $casts = [
        'data_json' => 'array'
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

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordType()
    {
        return $this->belongsTo(RecordType::class, 'record_type_id');
    }

    public function authoredBy()
    {
        return $this->belongsTo(User::class, 'authored_by');
    }

    public function icd10()
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code', 'code');
    }

    public function cpt()
    {
        return $this->belongsTo(CptCode::class, 'cpt_code', 'code');
    }

    // Scopes
    public function scopeDiagnoses($query)
    {
        return $query->where('record_type_id', 'diagnosis');
    }

    public function scopeProgressNotes($query)
    {
        return $query->where('record_type_id', 'progress_note');
    }
}
