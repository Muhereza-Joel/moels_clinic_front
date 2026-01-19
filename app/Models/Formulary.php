<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formulary extends BaseModel
{
    use HasFactory;

    protected $table = 'formulary';

    protected $fillable = [
        'uuid',
        'organization_id',
        'drug_code',
        'name',
        'generic_name',
        'atc_code',
        'strength',
        'form',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function drugs()
    {
        return $this->hasMany(Drug::class, 'drug_code', 'drug_code');
    }
}
