<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait Auditable
{
    public static function bootAuditable()
    {
        $eventMap = [
            'created' => 'create',
            'updated' => 'update',
            'deleted' => 'delete',
            'restored' => 'restore',
            'forceDeleted' => 'force_delete',
        ];
        foreach ($eventMap as $eloquentEvent => $auditAction) {
            if (in_array($auditAction, AuditLog::ACTIONS, true)) {
                static::$eloquentEvent(function ($model) use ($auditAction) {
                    $model->logAuditEvent($auditAction);
                });
            }
        }
    }

    protected function logAuditEvent(string $action)
    {
        try {
            AuditLog::create([
                'uuid'            => Str::uuid(),
                'organization_id' => $this->organization_id ?? null,
                'actor_user_id'   => Auth::id(),
                'entity_table'    => $this->getTable(),
                'entity_id'       => $this->getKey(),
                'action'          => $action,
                'changes_json'    => $this->getAuditChanges($action),
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
                'url'             => request()->fullUrl(),
                'http_method'     => request()->method(),
                'severity'        => $this->getAuditSeverity($action),
                'correlation_id'  => request()->header('X-Correlation-ID') ?? Str::uuid(),
            ]);
        } catch (\Throwable $e) {
            Log::error("Audit logging failed: " . $e->getMessage());
        }
    }

    protected function getAuditChanges(string $action): array
    {
        if ($action === 'updated') {
            return $this->getDirty();
        }

        return $this->attributesToArray();
    }

    protected function getAuditSeverity(string $action): string
    {
        return match ($action) {
            'delete', 'force_delete' => 'critical',
            'restore'                => 'warning',
            default                   => 'info',
        };
    }
}
