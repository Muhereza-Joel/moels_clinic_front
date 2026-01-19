<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends BaseModel
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'uuid',
        'organization_id',
        'recipient_patient_id',
        'recipient_user_id',
        'channel',
        'message_type',
        'content',
        'status',
        'scheduled_at',
        'sent_at',
        'provider_ref',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'recipient_patient_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'queued')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            });
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }
}
