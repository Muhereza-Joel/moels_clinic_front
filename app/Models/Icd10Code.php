<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Icd10Code extends Model
{
    protected $table = 'icd10_codes';

    protected $fillable = [
        'uuid',
        'code',
        'description',
        'chapter',
        'block',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public $timestamps = false;

    // Relationships
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'icd10_code', 'code');
    }
}
