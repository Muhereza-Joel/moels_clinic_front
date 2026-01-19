<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabResult extends BaseModel
{
    use HasFactory;

    protected $table = 'lab_results';

    protected $fillable = [
        'uuid',
        'lab_order_id',
        'analyte_code',
        'value_text',
        'value_numeric',
        'unit',
        'reference_range',
        'flagged',
        'result_date',
    ];

    protected $casts = [
        'result_date' => 'datetime',
        'flagged' => 'boolean',
        'value_numeric' => 'decimal:4'
    ];

    // Relationships
    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }

    // Scopes
    public function scopeFlagged($query)
    {
        return $query->where('flagged', true);
    }

    public function scopeByAnalyte($query, $analyte)
    {
        return $query->where('analyte_code', $analyte);
    }
}
