<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends BaseModel
{
    use HasFactory;

    protected $table = 'medical_records';

    protected $fillable = [
        'uuid',
        'organization_id',
        'visit_id',
        'record_type',
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

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
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
        return $query->where('record_type', 'diagnosis');
    }

    public function scopeProgressNotes($query)
    {
        return $query->where('record_type', 'progress_note');
    }
}
