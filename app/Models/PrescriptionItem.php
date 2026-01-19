<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrescriptionItem extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuid, BelongsToOrganization;

    protected $table = 'prescription_items';

    protected $fillable = [
        'uuid',
        'prescription_id',
        'drug_code',
        'drug_name',
        'dosage',
        'route',
        'frequency',
        'duration_days',
        'quantity',
        'instructions',
    ];

    // Relationships
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'related_prescription_item_id');
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class, 'drug_code', 'drug_code');
    }
}
