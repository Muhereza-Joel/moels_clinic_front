<?php

namespace App\Models;

use App\Traits\BelongsToOrganization;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use HasUuid, BelongsToOrganization, SoftDeletes;

    protected $table = 'audit_logs';

    public const ACTIONS = [
        'create',
        'update',
        'delete',
        'soft_delete',
        'restore',
        'status_change',
        'force_delete',
    ];

    protected $fillable = [
        'uuid',
        'organization_id',
        'actor_user_id',
        'entity_table',
        'entity_id',
        'action',
        'changes_json',
        'ip_address',
        'user_agent',
        'url',
        'http_method',
        'severity',
        'correlation_id',
    ];

    protected $casts = [
        'changes_json' => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
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
