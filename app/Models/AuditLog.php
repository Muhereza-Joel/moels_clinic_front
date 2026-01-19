<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'uuid',
        'organization_id',
        'actor_user_id',
        'entity_table',
        'entity_id',
        'action',
        'changes_json',
        'ip_address',
    ];

    protected $casts = [
        'changes_json' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    // Scopes
    public function scopeForEntity($query, $table, $entityId)
    {
        return $query->where('entity_table', $table)
            ->where('entity_id', $entityId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('actor_user_id', $userId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
