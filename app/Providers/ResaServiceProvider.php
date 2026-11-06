<?php

namespace Modules\Resa\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Traits\PathNamespace;
use Filament\Support\Facades\FilamentAsset;
use Modules\Resa\Models;
use Modules\Resa\Policies;
use App\Filament\PanelRegistry\DirectMenuItem;
use App\Filament\PanelRegistry\ModuleDefinedMenusRegistry;
use Modules\Resa\Filament\Resources\ReservationResource;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Observers\ReservationObserver;

class ResaServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Resa';

    protected string $nameLower = 'resa';

    

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
        $this->registerFilamentAssets();
        Reservation::observe(ReservationObserver::class);
        // $this->publishes([
        //     module_path($this->name, 'resources/assets') => public_path('resa/assets'),

        // ], 'resa-assets');
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
        $this->registerPolicies();
         // PanificationGPD Menu Acces Rapide et Preferences
         $this->registerDirectMenuItems();
    }

    public function registerPolicies()
    {
        $policies = [
            Models\Salle::class => Policies\SallePolicy::class,
            Models\Reservation::class => Policies\ReservationPolicy::class,
            Models\SallePoste::class => Policies\SallePostePolicy::class,
            Models\TypePoste::class => Policies\TypePostePolicy::class,
            Models\TypeSalle::class => Policies\TypeSallePolicy::class,
            Models\Formation::class => Policies\FormationPolicy::class,
            Models\PosteFormation::class => Policies\PosteCapacitePolicy::class,
            Models\RessourceReservation::class => Policies\RessourceReservationPolicy::class,
        ];

        foreach ($policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    public function registerDirectMenuItems()
    {
        app(ModuleDefinedMenusRegistry::class)->registerDirectMenuItems([
            DirectMenuItem::make()
                ->name('Reservation')
                
                ->children([
                    DirectMenuItem::make()
                        ->name('Liste')
                       
                        ->url(fn () => ReservationResource\Pages\ListReservations::getUrl(panel: 'resa'))
                        ->visible(fn () =>  ReservationResource::canAccess()),
                   
                ]),
           // ], 'planificationgpd');
        ], );
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->name, 'config/config.php') => config_path($this->nameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        $componentNamespace = $this->module_namespace($this->name, $this->app_path(config('modules.paths.generator.component-class.path')));
        Blade::componentNamespace($componentNamespace, $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }

    private function registerFilamentAssets()
    {
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );
    }

    private function getAssets()
    {
        return [
           // Css::make('module-resa', __DIR__.'/../../resources/dist/module-resa.css')->loadedOnRequest(),
            // Js::make('fullcalendar-core', asset('resa/assets/js/fullcalendar/core@6.1.19/index.global.min.js'))->loadedOnRequest(),
            // Js::make('fullcalendar-daygrid', asset('resa/assets/js/fullcalendar/daygrid@6.1.19/index.global.min.js'))->loadedOnRequest(),
            // Js::make('fullcalendar-interaction', asset('resa/assets/js/fullcalendar/interaction@6.1.19/index.global.min.js'))->loadedOnRequest(),
            // Js::make('fullcalendar-locale-fr', asset('resa/assets/js/fullcalendar/locales/fr.global.min.js'))->loadedOnRequest(),
        ];
    }

    private function getAssetPackageName()
    {
        return 'fanlab/resa';

    }
}
