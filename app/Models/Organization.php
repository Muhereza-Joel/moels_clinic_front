<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends BaseModel
{
    use HasFactory, HasUuid, Auditable;

    protected $table = 'organizations';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'code',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // Relationships
    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class);
    }

    public function labResults()
    {
        return $this->hasMany(LabResult::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function drugs()
    {
        return $this->hasMany(Drug::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function drugSales()
    {
        return $this->hasMany(DrugSale::class);
    }

    public function drugCategories()
    {
        return $this->hasMany(DrugCategory::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function recordTypes()
    {
        return $this->hasMany(RecordType::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function icd10Codes()
    {
        return $this->hasMany(Icd10Code::class);
    }

    public function cptCodes()
    {
        return $this->hasMany(CptCode::class);
    }
}
