<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Inbox;
use App\Models\Tenant;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use WallaceMartinss\FilamentEvolution\FilamentEvolutionPlugin;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->homeUrl(function (): string {
                try {
                    return Inbox::getUrl();
                } catch (\Throwable) {
                    return '/admin';
                }
            })
            ->login()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('Elite Atendimento')
            ->favicon(asset('favicon.ico'))
            ->tenant(Tenant::class, ownershipRelationship: 'tenant', slugAttribute: 'slug')
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Rose,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->navigationGroups([
                NavigationGroup::make('Atendimento')
                    ->icon('heroicon-o-chat-bubble-left-right'),
                NavigationGroup::make('Vendas')
                    ->icon('heroicon-o-currency-dollar'),
                NavigationGroup::make('Marketing')
                    ->icon('heroicon-o-megaphone'),
                NavigationGroup::make('Catálogo')
                    ->icon('heroicon-o-truck'),
                NavigationGroup::make('Base de Conhecimento')
                    ->icon('heroicon-o-book-open'),
                NavigationGroup::make('Configurações')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->plugins([
                FilamentEvolutionPlugin::make()
                    ->whatsappInstanceResource()
                    ->viewMessageHistory()
                    ->viewWebhookLogs(),
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
