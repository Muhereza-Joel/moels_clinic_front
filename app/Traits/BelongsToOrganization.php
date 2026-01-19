<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    /**
     * Boot the BelongsToOrganization trait.
     */
    protected static function bootBelongsToOrganization(): void
    {
        /**
         * Automatically attach organization_id on creation.
         */
        static::creating(function ($model) {
            // Respect manually set organization_id (seeders, imports, jobs)
            if (! empty($model->organization_id)) {
                return;
            }

            if (Auth::check() && Auth::user()->organization_id) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });

        /**
         * Automatically enforce organization_id on update.
         */
        static::updating(function ($model) {
            // Only override if not manually set
            if (empty($model->organization_id) && Auth::check() && Auth::user()->organization_id) {
                $model->organization_id = Auth::user()->organization_id;
            }
        });

        /**
         * Apply global organization scope.
         */
        static::addGlobalScope('organization', function (Builder $builder) {
            // Never scope console operations
            if (app()->runningInConsole()) {
                return;
            }

            if (! Auth::check() || ! Auth::user()->organization_id) {
                return;
            }

            $builder->where(
                $builder->getModel()->getTable() . '.organization_id',
                Auth::user()->organization_id
            );
        });
    }

    /**
     * Organization relationship.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope query to the authenticated user's organization.
     */
    public function scopeForCurrentOrganization(Builder $query): Builder
    {
        if (Auth::check() && Auth::user()->organization_id) {
            $query->where(
                $this->getTable() . '.organization_id',
                Auth::user()->organization_id
            );
        }

        return $query;
    }
}
