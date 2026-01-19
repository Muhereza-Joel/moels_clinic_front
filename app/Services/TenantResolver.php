<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class TenantResolver
{
    protected static ?int $organizationId = null;

    /**
     * Set the current tenant explicitly (e.g. middleware).
     */
    public static function set(int $organizationId): void
    {
        self::$organizationId = $organizationId;
    }

    /**
     * Get the current tenant ID.
     */
    public static function get(): ?int
    {
        if (self::$organizationId) {
            return self::$organizationId;
        }

        // Fallback to Auth if available
        if (Auth::check()) {
            return Auth::user()->organization_id;
        }

        return null;
    }
}
