<?php

namespace App\Providers\Filament;

use Jeffgreco13\FilamentBreezy\BreezyCore;
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
            ->id('clinika')
            ->path('clinika')
            ->spa()
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
            ->widgets([])
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
            ->authGuard('web')
            ->authMiddleware([
                Authenticate::class,
            ])->tenant(
                model: Organization::class,
                slugAttribute: 'slug',
            )
            ->tenantMenu(true)
            ->tenantMiddleware([
                \BezhanSalleh\FilamentShield\Middleware\SyncShieldTenant::class,
            ], isPersistent: true)
            ->plugins(
                [
                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                    BreezyCore::make()
                        ->myProfile(
                            shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
                            userMenuLabel: 'My Profile', // Customizes the 'account' link label in the panel User Menu (default = null)
                            shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
                            navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
                            hasAvatars: false, // Enables the avatar upload form component (default = false)

                        )

                ]
            );
    }
}
