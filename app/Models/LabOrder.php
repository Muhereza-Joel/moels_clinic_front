<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabOrder extends BaseModel
{
    use HasFactory;

    protected $table = 'lab_orders';

    protected $fillable = [
        'uuid',
        'organization_id',
        'visit_id',
        'ordered_by',
        'order_date',
        'panel_code',
        'status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'datetime'
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

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function results()
    {
        return $this->hasMany(LabResult::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['ordered', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
