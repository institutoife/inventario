<?php

namespace App\Providers\Filament;

use App\Filament\Navigation\LegacyNavigation;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AssetStats;
use App\Http\Middleware\ActiveUser;
use App\Http\Middleware\EnsurePanelAccess;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('admin')->path('panel')->login()
            ->brandName('Activos fijos')->colors(['primary' => Color::Blue])
            ->sidebarCollapsibleOnDesktop()->globalSearch(false)->strictAuthorization()
            ->navigationGroups(['Inventario', 'Organización', 'Administración'])
            ->navigationItems(LegacyNavigation::items())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([Dashboard::class])
            ->widgets([AccountWidget::class, AssetStats::class])
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class, ActiveUser::class, EnsurePanelAccess::class], isPersistent: true);
    }
}
