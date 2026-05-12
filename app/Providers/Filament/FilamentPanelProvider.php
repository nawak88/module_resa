<?php

namespace Modules\Resa\Providers\Filament;

use Filament\Pages\Dashboard;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\FontProviders\SpatieGoogleFontProvider;
use Filament\Navigation\NavigationItem;

use App\Filament\AvatarProviders\SkeletorAvatarProvider;
use Modules\Resa\Filament\Widgets\PanelSwitcher;
use App\Providers\Filament\Traits\UsesSkeletorPrefixAndMultitenancyTrait;

use App\Http\Middleware\InitializeTenancyByPath;
use App\Http\Middleware\SetTenantCookieMiddleware;
use App\Http\Middleware\SetTenantDefaultForRoutesMiddleware;
use App\Http\Middleware\ReconfigureSessionDatabaseWhenTenantNotInitialized;

class FilamentPanelProvider extends PanelProvider
{
    use UsesSkeletorPrefixAndMultitenancyTrait;

    private string $module = 'Resa';

    public function panel(Panel $panel): Panel
    {
        $moduleNamespace = $this->getModuleNamespace();

        return $panel
            ->id('resa')
            ->path($this->prefix  . '/resa')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->favicon(asset('assets/images/favicon-32x32.png')) 
            //->font('Inter', provider: SpatieGoogleFontProvider::class)
            ->defaultAvatarProvider(SkeletorAvatarProvider::class)
            ->brandName('Resa')
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->discoverResources(in: module_path($this->module, 'app/Filament/Resources'), for: "$moduleNamespace\\Filament\\Resources")
            ->discoverPages(in: module_path($this->module, 'app/Filament/Pages'), for: "$moduleNamespace\\Filament\\Pages")
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'app/Filament/Widgets'), for: "$moduleNamespace\\Filament\\Widgets")
            ->widgets([
                //PanelSwitcher::class,
                CalendrierWidget::class,
                #Widgets\AccountWidget::class,
                #Widgets\FilamentInfoWidget::class,
            ])
            ->navigationItems([
                NavigationItem::make('Login')
                ->label('Se connecter')
                ->url(fn(): string => route('login'))
                ->icon('heroicon-o-user')
                ->hidden(fn(): bool => Auth::check()),
                NavigationItem::make('Visuel')
                
                //->url(fn()=>route('filament.resa.resources.calendriers.visu'))
                ->icon('heroicon-o-calendar-days')
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                InitializeTenancyByPath::class,
                ReconfigureSessionDatabaseWhenTenantNotInitialized::class,
                SetTenantDefaultForRoutesMiddleware::class,
                SetTenantCookieMiddleware::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                #Authenticate::class,
            ])
            ->topNavigation()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full);
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
