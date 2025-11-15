<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\HealthCheckResults;
use App\Http\Middleware\Authenticate;
use CraftForge\FilamentLanguageSwitcher\FilamentLanguageSwitcherPlugin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Muazzam\SlickScrollbar\SlickScrollbarPlugin;
// use App\Filament\AvatarProviders\UserAvatarProvider;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
//            ->defaultAvatarProvider(UserAvatarProvider::class)
            ->id('admin')
            ->login(Login::class)
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            ->unsavedChangesAlerts()
            ->brandLogo(fn () => view('filament.app.logo'))
            ->brandName('fireflow')
            ->brandLogoHeight('1.25rem')
            ->navigationGroups([
                'Shop',
                'Blog',
                'Databases',
            ])
            ->databaseNotifications()
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
          //  ->defaultAvatarProvider(BoringAvatarsProvider::class)
            ->spa()
        //    ->viteTheme('resources/css/filament/admin/theme.css')
        //    ->plugins([
        //        FilamentFullCalendarPlugin::class,
        //    ])
            ->plugins([
                SlickScrollbarPlugin::make(),
            ])
            ->plugins([
                FilamentApexChartsPlugin::make(),
            ])
            ->plugins([
                SpotlightPlugin::make(),
            ])
            ->plugin(
                FilamentSpatieLaravelHealthPlugin::make()
                    ->authorize(fn (): bool => auth()->user()->email === 'admin@example.com')
                    ->usingPage(HealthCheckResults::class)
            )
            ->plugins([FilamentFullCalendarPlugin::make()])
            ->plugins([
                FilamentLanguageSwitcherPlugin::make(),
                BreezyCore::make()->myProfile(true, /* shouldRegisterUserMenu */ false, /* shouldRegisterNavigation */ true, /* hasAvatars */ true, /* slug */ 'my-profile', /* navigationGroup */ 'account', /* userMenuLabel */ null),
            ])
            ->colors([
                'primary' => Color::Blue,
            ]);
    }
}
