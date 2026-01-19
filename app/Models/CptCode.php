<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CptCode extends Model
{
    protected $table = 'cpt_codes';

    protected $fillable = [
        'uuid',
        'code',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public $timestamps = false;

    // Relationships
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'cpt_code', 'code');
    }
}
