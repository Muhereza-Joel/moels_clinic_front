<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\PermissionRegistrar;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->booted(function () {
            $user = Auth::user();

            if ($user) {
                if ($user->hasRole('super_admin')) {
                    app(PermissionRegistrar::class)->setPermissionsTeamId(null);
                } else {
                    $tenantId = Filament::getTenant()?->id ?? $user->organization_id;
                    app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);
                }
            }
        });
    }
}
