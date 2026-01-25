<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SetTenant;
use App\Models\Organization;

class DashboardPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('clinika')
            ->sidebarWidth('16rem')
            ->databaseTransactions()
            ->simplePageMaxContentWidth(MaxWidth::Small)
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(fn() => view('filament.dashboard.logo'))
            ->brandLogoHeight('auto')
            ->colors([
                'primary' => Color::Teal,
            ])
            ->font('sans-serif - system-ui - -apple-system - BlinkMacSystemFont - "Segoe UI" - Roboto - "Helvetica Neue" - Arial - sans-serif')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,

            ])
            ->navigationGroups([
                'Sales Management',
                'Inventory Management',
                'Medical Records',
                'Filament Shield',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetTenant::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->tenant(
                model: Organization::class,
            )
            ->tenantMenu(true)
            ->tenantMiddleware([
                \BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant::class,
            ], isPersistent: true)
            ->plugins(
                [
                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                ]
            );
    }
}
