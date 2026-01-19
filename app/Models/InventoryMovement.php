<?php
// app/Models/InventoryMovement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends BaseModel
{
    use HasFactory;

    protected $table = 'inventory_movements';

    protected $fillable = [
        'uuid',
        'organization_id',
        'drug_id',
        'movement_type',
        'related_prescription_item_id',
        'quantity',
        'reason',
        'reference',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'performed_at' => 'datetime'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class, 'related_prescription_item_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
