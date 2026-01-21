<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabResult extends BaseModel
{
    use HasFactory, HasUuid, BelongsToOrganization, SoftDeletes, Auditable;

    protected $table = 'lab_results';

    protected $fillable = [
        'uuid',
        'organization_id',
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

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

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
