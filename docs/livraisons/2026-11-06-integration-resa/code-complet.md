# Intégration de Resa 0.0.4 dans Skeletor

L’archive Modules/RH/module_resa.tar.gz contient le module Resa version 0.0.4. Le projet possédait déjà Resa 0.0.2, sous Modules/Resa, dans son propre dépôt Git. La version 0.0.4 a été installée dans ce dossier après sauvegarde complète de l’existant.

72 fichiers ont été ajoutés ou mis à jour. Les 118 fichiers identiques à l’archive ont été conservés tels quels. Le test supplémentaire tests/Feature/ResourcesFormAdminTest.php, déjà présent dans le projet et absent de l’archive, a été conservé. Deux espaces en fin de ligne importés ont été nettoyés dans TypePosteResource.php et TypeSalleResource.php.

Le dépôt .git existant de Resa a été conservé. Les métadonnées et hooks Git de l’archive n’ont pas été importés. L’archive source n’a pas été modifiée. Aucun changement de code n’a été apporté à RH, FPSplanificationstage ni à l’application principale. Le fichier modules_statuses.json activait déjà Resa : il n’a pas été modifié.

Le module utilise les providers déclarés dans son module.json et son autoload PSR-4 existant. Il est découvert et activé au même titre que RH et FPSplanificationstage. Le panel Resa est disponible au chemin de l’instance suivi de /resa.

## Sauvegarde préalable

Sauvegarde des fichiers de l’ancienne version, dépôt Git compris :

/home/maison/Skeletor/storage/app/private/module-backups/Resa-avant-0.0.4-20261106T131857Z.tar.gz

Il s’agit d’une sauvegarde des fichiers, pas d’un export de base de données.

## Validation

- 146 fichiers PHP vérifiés par php -l dans Docker Compose, sans erreur.
- php artisan module:list détecte Resa activé, aux côtés de RH et FPSplanificationstage.
- 231 tests du groupe Resa réussis, avec 494 assertions, en 101,95 secondes.
- Les migrations et seeders nécessaires aux tests ont été exercés exclusivement sur testdb via testmysql.
- git diff --check ne signale plus d’erreur.

Commande des tests dans le conteneur PHP :

```bash
DB_CONNECTION=testmysql DB_DATABASE=testdb php artisan test --group=Resa --compact
```

Le montage /app du conteneur étant vide, la validation a utilisé une copie temporaire de l’application dans le conteneur PHP existant. Aucune migration ni donnée de réservation n’a été appliquée à la base applicative ou au serveur indépendant pendant cette validation.

## Déploiement sur la base de l’instance d’utilisation

Le code et l’activation du module sont en place localement. Pour utiliser les nouvelles colonnes et tables sur la base réelle de l’instance, appliquer les migrations du module depuis la racine de Skeletor. Dans le conteneur PHP d’une installation sans multitenance :

```bash
cd ~/project
composer dump-autoload -o
php artisan optimize:clear
php artisan filament:clear-cached-components
php artisan module:enable Resa
php artisan module:migrate Resa
php artisan db:seed --class='Modules\Resa\Database\Seeders\PermissionsSeeder'
php artisan db:seed --class='Modules\Resa\Database\Seeders\RolesSeeder'
```

Les commandes de migrations et de seed doivent cibler la base de l’instance concernée. En installation multitenante, utiliser la procédure tenant de Skeletor pour cibler le tenant. Le seeder global ResaDatabaseSeeder n’est pas proposé ici car il ajoute aussi des salles, postes et formations d’exemple. Les deux seeders ci-dessus initialisent uniquement les permissions et rôles du module.

L’utilisateur administrateur Resa doit ensuite recevoir le rôle ou les permissions adaptés dans Skeletor. Aucun compte n’a reçu automatiquement de nouveau rôle lors de l’import de fichiers.

## Livrables

- module-resa-complet.zip : tout le module, sans .git ni les archives de livraison elles-mêmes. Son premier dossier est Resa ; à extraire dans Modules sur l’environnement cible.
- code-complet.md : contenu intégral des 72 fichiers installés ou mis à jour.
- fichiers-modifies.txt : liste des fichiers concernés.

## Code complet des fichiers installés

### CHANGELOG.md

```markdown
## 0.0.4 (November 02, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.3 –> 0.0.4
- Merge branch 'tuleap-993859-resa-ajout-list-email-opur-la-gestion' into latest
- Correction et amelioration pour les gestionnaires de Salles Formation, Test Unitaires
- Modification champ Input pour voir les emails deja renseignees + delete + test unitaire

## 0.0.3 (October 23, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.2 –> 0.0.3
- Gestionnaire de categories ressources (validation autonome matériel)
- RessourceGanttWidget en 3 Actions : refactoring conmme CalendrierWidget)
- Resssource statut :  dispo/entretien/indsipo blocage reservation
- Gestionnaire de salle classique
- Resa : Modification dashboard et widget  pour gestionnaire de Formation + test  + attibuation / suppression de permission isGestionnaire()
- Implement art  #780630 : Test Unitaires OK

## 0.0.2 (June 15, 2026)
- Updated VERSION, Updated CHANGELOG.md, Bumped 0.0.1 –> 0.0.2
- Implements art #780630 : refonte modal pour choix salles + test unitaires + correction bug
- Implement art #780630 : Modification et retouche bug
- Implements Art #780630 : Nettoyage du module Resa
- Implements art #780630 : Creation Modal pour se connecter +  creation Resource pour la gestion Reservation des ressources
- Implements art #780630 Modification des ressources, modification dashboard
- Implements art #780630 : utilisation de Guava pour les resources
- Implements art 780630 : rajout de sressources
- Implements art #780630 : rajout secon stagiaire
- Implements art #780630 : Modification des tables, model, , resource et des test unitiaires Ajout de nouveau user modification calendrier

## 0.0.1 (May 18, 2026)
- Created VERSION, Created CHANGELOG.md, Bumped to 0.0.1
- Implements Art #740482 : Creation Gestionnaire pour les salles + modif email + creation Extends User dans Resa +  ajout test unitaire
- Implements Art #740482 : ajout email pour notifications + test unitaires (3 erreurs a corriger)
- Implements Art #740482 : Mise a jour Graphique + modification bug search (creation resource filament V4 par skeletor)
- Implements art #740482 : Modification et ajout test unitaires
- Implements art #740482 : Travaux modification nouveau cahier des charges
- Mise a jour du Module Resa selon nouveau cahier des charges Fanlab Doc a jour Test unitaire ok manque les resources
- Implements art #740482 : mise en place du module Resa
- Commit initial
```

### VERSION

```text
0.0.4
```

### module.json

```json
{
    "name": "Resa",
    "alias": "resa",
    "description": "Module permettant de réserver des salles de travail",
    "keywords": [],
    "priority": 0,
    "providers": [
        "Modules\\Resa\\Providers\\ResaServiceProvider",
        "Modules\\Resa\\Providers\\Filament\\FilamentPanelProvider"

    ],
    "files": [],
    "version": "0.0.4"
}
```

### app/Observers/ReservationObserver.php

```php
<?php

namespace Modules\Resa\Observers;

use Illuminate\Support\Facades\Mail;
use Modules\Resa\Mail\ReservationCree;
use Modules\Resa\Mail\ReservationSupprimee;
use Modules\Resa\Mail\ReservationValidee;
use Modules\Resa\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        foreach ($reservation->destinataires() as $email) {
            Mail::to($email)->send(new ReservationCree($reservation));
        }

        // Réservation née déjà validée (admin, ou formation à validation automatique
        // type "FPV Entraînement") : l'email "updated" ne se déclenchera jamais pour
        // elle (pas de transition de statut), donc on envoie l'email de validation ici.
        // C'est aussi systématiquement le cas d'auto-validation (aucun gestionnaire n'a
        // eu à agir) : on ne les notifie pas, pour éviter de les spammer.
        if ($reservation->isValide()) {
            $this->envoyerEmailValidation($reservation, inclureGestionnaires: false);
        }
    }

    public function updated(Reservation $reservation): void
    {
        if (! $reservation->wasChanged('validation')) {
            return;
        }

        if (! $reservation->isValide()) {
            return;
        }

        // Ici, par contre, un gestionnaire vient d'agir manuellement (toggleValidation) :
        // les destinataires habituels (dont gestionnaires) sont notifiés normalement.
        $this->envoyerEmailValidation($reservation, inclureGestionnaires: true);
    }

    public function deleted(Reservation $reservation): void
    {
        foreach ($reservation->destinataires() as $email) {
            Mail::to($email)->send(new ReservationSupprimee($reservation));
        }
    }

    private function envoyerEmailValidation(Reservation $reservation, bool $inclureGestionnaires): void
    {
        foreach ($reservation->destinataires($inclureGestionnaires) as $email) {
            Mail::to($email)->send(new ReservationValidee($reservation));
        }
    }
}
```

### app/Providers/ResaServiceProvider.php

```php
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
```

### app/Models/Salle.php

```php
<?php
namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Modules\RH\Models\Unite;
use Modules\Resa\Traits\HasTablePrefix;

class Salle extends Model implements Resourceable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'code',
        'libelle_court',
        'libelle_long',
        'type_salle_id',
        'unite_id',
        'email',
        'capacite',
        'image',
        'color',
        'ordre',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function typeSalle(): BelongsTo
    {
        return $this->belongsTo(TypeSalle::class, 'type_salle_id');
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_id');
    }

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_salle_gestionnaires', 'salle_id', 'user_id');
    }

    /**
     * Gestionnaire de la salle elle-même, OU — pour les salles simulateur, où la
     * gestion se fait par formation plutôt que par salle (cf. SalleResource : le champ
     * « Gestionnaires » n'est affiché que pour les salles classiques) — gestionnaire
     * d'au moins une formation disponible sur un poste de cette salle.
     */
    public function isGestionnaire(User $user): bool
    {
        if ($this->gestionnaires()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($this->typeSalle?->mode !== TypeSalle::MODE_SIMULATEUR) {
            return false;
        }

        return $this->sallePostes()
            ->whereHas('formations.gestionnaires', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }

    /**
     * IDs des salles gérées par cet utilisateur (salle directe, ou formation pour les
     * salles simulateur). Voir isGestionnaire() pour le détail des deux cas.
     */
    public static function idsGereesPar(?User $user): \Illuminate\Support\Collection
    {
        if (! $user) {
            return collect();
        }

        $directes = static::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->pluck('id');

        $viaFormation = static::whereHas(
            'sallePostes.formations.gestionnaires',
            fn ($q) => $q->where('user_id', $user->id),
        )->pluck('id');

        return $directes->merge($viaFormation)->unique()->values();
    }

    public function sallePostes(): HasMany
    {
        return $this->hasMany(SallePoste::class, 'salle_id')->orderBy('ordre');
    }

    public function typePostes(): BelongsToMany
    {
        return $this->belongsToMany(
            TypePoste::class,
            (new SallePoste)->getTable(),
            'salle_id',
            'type_poste_id'
        )->withPivot('libelle_court', 'libelle_long', 'ordre')->withTimestamps();
    }

    // Guava Calendar — Resourceable
    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->libelle_court ?? '');
    }
}
```

### app/Models/RessourceReservation.php

```php
<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class RessourceReservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'user_id', 'ressource_id', 'libelle', 'debut', 'fin',
        'batiment', 'service', 'emprunteur_user_id', 'officier_responsable_user_id',
        'validation', 'validated_by', 'validated_at', 'data',
    ];

    protected $casts = [
        'debut'        => 'datetime',
        'fin'          => 'datetime',
        'validation'   => 'integer',
        'validated_at' => 'datetime',
        'data'         => 'array',
    ];

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class, 'ressource_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function emprunteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emprunteur_user_id');
    }

    public function officierResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officier_responsable_user_id');
    }

    /** Grade RH de l'emprunteur (via son dossier Marin), sinon '—'. */
    public function getEmprunteurGradeAttribute(): string
    {
        $marin = $this->emprunteur ? \Modules\RH\Models\Marin::fromUser($this->emprunteur) : null;

        return $marin?->grade?->libelle_court ?? '—';
    }

    /** Grade RH de l'officier responsable (via son dossier Marin), sinon '—'. */
    public function getOfficierResponsableGradeAttribute(): string
    {
        $marin = $this->officierResponsable ? \Modules\RH\Models\Marin::fromUser($this->officierResponsable) : null;

        return $marin?->grade?->libelle_court ?? '—';
    }

    public function isValide(): bool
    {
        return $this->validation === self::VALIDE;
    }

    public function isEnAttente(): bool
    {
        return $this->validation === self::EN_ATTENTE;
    }

    public function valider(User $admin): void
    {
        $this->update([
            'validation'   => self::VALIDE,
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);
    }

    public function invalider(): void
    {
        $this->update([
            'validation'   => self::EN_ATTENTE,
            'validated_by' => null,
            'validated_at' => null,
        ]);
    }

    public function couleur(): string
    {
        return $this->isValide() ? '#22c55e' : '#f59e0b';
    }

    /* ── Guava Calendar ───────────────────────────────────────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        $isAdmin = auth()->user()?->can('resa::admin') ?? false;
        $nom     = $isAdmin ? ($this->user?->nom ?? '') : '';

        $titre = implode("\n", array_filter([
            $this->libelle ?: ($this->user?->nom ?? ''),
            $nom && $this->libelle ? '👤 ' . $nom : '',
            $this->debut?->format('H:i') . ' — ' . $this->fin?->format('H:i'),
        ]));

        return CalendarEvent::make($this)
            ->title($titre)
            ->start($this->debut?->format('Y-m-d H:i:s'))
            ->end($this->fin?->format('Y-m-d H:i:s'))
            ->resourceId($this->ressource_id)
            ->backgroundColor($this->couleur())
            ->editable(! $this->isValide() && auth()->id() === $this->user_id);
    }
}
```

### app/Models/Reservation.php

```php
<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Modules\Resa\Traits\HasTablePrefix;
use App\Models\User;
use Modules\Resa\Models\Formation;

class Reservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid', 'user_id', 'created_by', 'salle_poste_id', 'libelle_court', 'libelle_long',
        'debut', 'fin', 'couleur', 'validation', 'formations_utilisees',
        'sous_categorie_id', 'validated_by', 'validated_at', 'data',
        'presence', 'presence_marquee_par', 'presence_marquee_at',
    ];

    protected $casts = [
        'debut'               => 'datetime',
        'fin'                 => 'datetime',
        'validated_at'        => 'datetime',
        'validation'          => 'integer',
        'formations_utilisees' => 'array',
        'data'                => 'array',
        'presence'            => 'boolean',
        'presence_marquee_at' => 'datetime',
    ];

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    /* ── Relations ────────────────────────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** La personne qui a effectué la réservation — peut différer du stagiaire (user_id)
     *  quand on réserve pour quelqu'un d'autre. */
    public function creePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sallePoste(): BelongsTo
    {
        return $this->belongsTo(SallePoste::class, 'salle_poste_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function sousCategorie(): BelongsTo
    {
        return $this->belongsTo(FormationSousCategorie::class, 'sous_categorie_id');
    }

    public function presenceMarqueePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'presence_marquee_par');
    }

    /* ── Pointeuse ────────────────────────────────────────────────────────── */

    /** Le jour J (et pas avant) : pas de pointage pour une séance future ou passée. */
    public function peutPointer(): bool
    {
        return $this->isValide() && $this->debut?->isToday();
    }

    public function pointer(bool $present, User $marquePar): void
    {
        $this->update([
            'presence'             => $present,
            'presence_marquee_par' => $marquePar->id,
            'presence_marquee_at'  => now(),
        ]);
    }

    /* ── Scopes ───────────────────────────────────────────────────────────── */

    public function scopeValide($query)
    {
        return $query->where('validation', self::VALIDE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('validation', self::EN_ATTENTE);
    }

    /* ── Methodes metier ──────────────────────────────────────────────────── */

    public function isValide(): bool
    {
        return $this->validation === self::VALIDE;
    }

    public function isEnAttente(): bool
    {
        return $this->validation === self::EN_ATTENTE;
    }

    public function valider($admin): void
    {
        $this->update([
            'validation'   => self::VALIDE,
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);
    }

    public function invalider(): void
    {
        $this->update([
            'validation'   => self::EN_ATTENTE,
            'validated_by' => null,
            'validated_at' => null,
        ]);
    }

    /**
     * Noms des stagiaires/participants (data.users, ou le demandeur seul à défaut) —
     * utilisé pour l'affichage calendrier et l'email de validation.
     */
    public function participantsNoms(): array
    {
        $userIds = collect($this->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $this->user_id) {
            $userIds = collect([$this->user_id]);
        }

        if ($userIds->isEmpty()) {
            return [];
        }

        return User::whereIn('id', $userIds)
            ->get()
            ->map(fn ($u) => trim("{$u->nom} {$u->prenom}") ?: $u->email)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Tous les destinataires légitimes d'une notification sur cette réservation :
     * le réservataire (créateur), tous les participants (marin 1, marin 2…), les
     * gestionnaires de la/les formation(s) et de la salle, plus les éventuelles
     * adresses de notification configurées (champ "email" de Salle/Formation).
     * Dédupliqué et filtré sur les adresses valides (cf. ReservationObserver).
     */
    public function destinataires(bool $inclureGestionnaires = true): array
    {
        $emails = [];

        if ($this->creePar?->email) {
            $emails[] = $this->creePar->email;
        } elseif ($this->user?->email) {
            // Anciennes réservations sans created_by : le stagiaire reste le seul
            // repère disponible.
            $emails[] = $this->user->email;
        }

        $userIds = collect($this->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $this->user_id) {
            $userIds = collect([$this->user_id]);
        }
        // NB : ->each(fn ($e) => $emails[] = $e) ne marche PAS ici — les arrow functions
        // PHP capturent $emails par valeur, donc l'ajout se perdait silencieusement.
        // array_push(..., ...$collection) le fait correctement.
        array_push($emails, ...User::whereIn('id', $userIds)->pluck('email')->all());

        // $inclureGestionnaires = false : cas de l'auto-validation (formation à
        // validation_auto, ou création directe par un gestionnaire déjà au courant),
        // pour ne pas spammer les gestionnaires d'un événement qu'ils n'ont pas eu à
        // traiter eux-mêmes — cf. ReservationObserver::created().
        if ($inclureGestionnaires) {
            if (! empty($this->formations_utilisees)) {
                $formations = Formation::whereIn('id', $this->formations_utilisees)->with('gestionnaires')->get();

                foreach ($formations as $formation) {
                    array_push($emails, ...$formation->gestionnaires->pluck('email')->all());

                    if ($formation->email) {
                        array_push($emails, ...explode(',', $formation->email));
                    }
                }
            }

            $salle = $this->sallePoste?->salle;
            if ($salle) {
                array_push($emails, ...$salle->gestionnaires->pluck('email')->all());

                if ($salle->email) {
                    array_push($emails, ...explode(',', $salle->email));
                }
            }
        }

        return collect($emails)
            ->map(fn ($email) => trim((string) $email))
            // Une adresse mal formée (ex. profil RH/LDAP incomplet) ne doit jamais faire
            // planter l'envoi : Mail::to() valide le format immédiatement, avant même
            // la mise en file (ShouldQueue ne protège pas contre ça).
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Préfixe stable pour l'objet des emails (ex. "[Resa #42]") : permet de créer une
     * règle de classement Outlook par réservation ou par module, indépendamment du
     * contenu (qui change selon l'étape : création, validation, rappel).
     */
    public function objetMail(): string
    {
        $quoi = ! empty($this->formations_utilisees)
            ? Formation::whereIn('id', $this->formations_utilisees)->pluck('libelle_court')->join(', ')
            : ($this->sallePoste?->salle?->libelle_court ?? $this->libelle_court);

        $quand = $this->debut?->format('d/m/Y H:i');

        return "[Resa #{$this->id}] {$quoi} — {$quand}";
    }

    /**
     * Vrai si l'utilisateur courant n'a pas besoin d'être distingué des autres pour
     * cette réservation : pas de formation concernée (salle classique), admin complet,
     * gestionnaire d'au moins une des formations utilisées, ou demandeur lui-même.
     * Utilisé uniquement pour l'affichage (couleur) sur le calendrier transversal.
     */
    private function estGereeParUtilisateurCourant(): bool
    {
        if (empty($this->formations_utilisees)) {
            return true;
        }

        $user = auth()->user();
        if (! $user) {
            return true;
        }

        if ($user->id === $this->user_id) {
            return true;
        }

        if ($user->can('resa::admin') || $user->can('resa::gestionnaire_salle')) {
            return true;
        }

        return Formation::whereIn('id', $this->formations_utilisees)
            ->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }

    /* ── Guava Calendar — Eventable ───────────────────────────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        // ── Couleur formation ────────────────────────────────────────────────
        $formationColor = null;
        if (! empty($this->formations_utilisees)) {
            $couleur = Formation::whereIn('id', $this->formations_utilisees)->value('couleur');
            $formationColor = match ($couleur) {
                'primary'   => '#6366f1',
                'secondary' => '#64748b',
                'success'   => '#22c55e',
                'danger'    => '#ef4444',
                'warning'   => '#f59e0b',
                'info'      => '#3b82f6',
                'gray'      => '#6b7280',
                default     => null,
            };
        }
        // En attente : ambre, nettement distinct du vert/couleur formation des réservations
        // validées (même convention que RessourceReservation::couleur()) — le gris utilisé
        // auparavant se distinguait trop peu visuellement sur le calendrier.
        //
        // Quand le calendrier transversal montre aussi les réservations d'autres
        // formations (cf. CalendrierWidget::sallesFormationGlobale()), un gestionnaire
        // doit pouvoir distinguer d'un coup d'œil ce qui relève de sa formation de ce
        // qui ne l'est pas : couleurs grisées/estompées pour « pas la mienne ».
        $estMaFormation = $this->estGereeParUtilisateurCourant();

        $backgroundColor = match (true) {
            $this->isValide() && $estMaFormation   => $formationColor ?? '#22c55e',
            $this->isValide() && ! $estMaFormation => '#94a3b8', // slate — validée, autre formation
            ! $this->isValide() && $estMaFormation => '#f59e0b',
            default                                 => '#cbd5e1', // gris clair — en attente, autre formation
        };

        // ── Label formations ─────────────────────────────────────────────────
        $capacitesLabel = '';
        if (! empty($this->formations_utilisees)) {
            $capacitesLabel = Formation::whereIn('id', $this->formations_utilisees)
                ->orderBy('ordre')
                ->pluck('libelle_court')
                ->join(', ');
        }

        // ── Noms des utilisateurs (si connecté) ──────────────────────────────
        $isConnecte   = auth()->check();
        $isSimulateur = ! empty($this->formations_utilisees);

        $nomsUsers = [];
        if ($isConnecte) {
            // Tous les user IDs : data.users en priorité, sinon user_id seul
            $userIds = collect($this->data['users'] ?? [])->filter()->values();
            if ($userIds->isEmpty() && $this->user_id) {
                $userIds = collect([$this->user_id]);
            }
            if ($userIds->isNotEmpty()) {
                $nomsUsers = \App\Models\User::whereIn('id', $userIds)
                    ->get()
                    ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                    ->filter()
                    ->values()
                    ->toArray();
            }
        }

        // ── Titre affiché ────────────────────────────────────────────────────
        $labelTitre = $isSimulateur
            ? ($capacitesLabel ?: ($this->libelle_court ?? ''))
            : ($this->libelle_court ?? '');

        $lignes = array_filter([
            $labelTitre,
            ...array_map(fn ($n) => '👤 ' . $n, $nomsUsers),
        ]);

        return CalendarEvent::make($this)
            ->title(implode("\n", $lignes))
            ->start($this->debut ? \Carbon\Carbon::parse($this->debut->format('Y-m-d H:i:s'), 'Europe/Paris') : null)
            ->end($this->fin ? \Carbon\Carbon::parse($this->fin->format('Y-m-d H:i:s'), 'Europe/Paris') : null)
            ->resourceId($this->salle_poste_id)
            ->backgroundColor($backgroundColor)
            ->extendedProp('validation', $this->validation)
            ->extendedProp('description', $this->libelle_long)
            ->extendedProp('formations', $this->formations_utilisees ?? [])
            ->extendedProp('noms', $nomsUsers)
            ->extendedProp('capacites_label', $capacitesLabel)
            ->extendedProp('poste', $this->sallePoste?->libelle_court ?? '')
            ->extendedProp('heure_debut', $this->debut?->format('H:i') ?? '')
            ->extendedProp('heure_fin',   $this->fin?->format('H:i') ?? '')
            ->editable(! $this->isValide() && auth()->id() === $this->user_id);
    }
}
```

### app/Models/Formation.php

```php
<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;

class Formation extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'libelle_court',
        'libelle_long',
        'ordre',
        'nb_users_par_poste',
        'email',
        'couleur',
        'validation_auto',
        'notice',
    ];

    protected $casts = [
        'nb_users_par_poste' => 'integer',
        'validation_auto'    => 'boolean',
    ];

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_formation_gestionnaires', 'formation_id', 'user_id');
    }

    public function isGestionnaire(User $user): bool
    {
        return $this->gestionnaires()->where('user_id', $user->id)->exists();
    }

    public function sallePostes(): BelongsToMany
    {
        return $this->belongsToMany(
            SallePoste::class,
            (new PosteFormation)->getTable(),
            'formation_id',
            'salle_poste_id'
        );
    }

    public function sousCategories(): HasMany
    {
        return $this->hasMany(FormationSousCategorie::class)->orderBy('ordre');
    }

    /**
     * Règle de validation effective pour une sous-catégorie donnée (ou la formation
     * elle-même si aucune n'est choisie / qu'il n'y en a pas) — 0 ou 1 sous-catégorie
     * laisse la formation se comporter "comme avant", avec ses propres champs.
     */
    public function validationAuto(?FormationSousCategorie $sousCategorie = null): bool
    {
        return (bool) ($sousCategorie?->validation_auto ?? $this->validation_auto);
    }

    public function notice(?FormationSousCategorie $sousCategorie = null): ?string
    {
        return $sousCategorie?->notice ?? $this->notice;
    }
}
```

### app/Models/FormationSousCategorie.php

```php
<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class FormationSousCategorie extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'formation_id',
        'libelle_court',
        'validation_auto',
        'notice',
        'ordre',
    ];

    protected $casts = [
        'validation_auto' => 'boolean',
        'ordre'           => 'integer',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }
}
```

### app/Models/Ressource.php

```php
<?php

namespace Modules\Resa\Models;

use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\RH\Models\Unite;
use Modules\Resa\Traits\HasTablePrefix;

class Ressource extends Model implements Resourceable
{
    use HasTablePrefix;

    const STATUT_DISPONIBLE   = 'disponible';
    const STATUT_ENTRETIEN    = 'entretien';
    const STATUT_INDISPONIBLE = 'indisponible';

    public const STATUTS = [
        self::STATUT_DISPONIBLE   => 'Disponible',
        self::STATUT_ENTRETIEN    => 'En entretien',
        self::STATUT_INDISPONIBLE => 'Indisponible',
    ];

    protected $fillable = [
        'ressource_categorie_id', 'unite_id', 'libelle', 'libelle_court', 'description', 'ordre', 'actif', 'statut',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    protected $attributes = [
        'statut' => self::STATUT_DISPONIBLE,
    ];

    public function isDisponible(): bool
    {
        return $this->statut === self::STATUT_DISPONIBLE;
    }

    public function getStatutLabelAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(RessourceCategorie::class, 'ressource_categorie_id');
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(RessourceReservation::class, 'ressource_id');
    }

    public function getNomAttribute(): string
    {
        return $this->libelle_court ?? $this->libelle;
    }

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->nom);
    }
}
```

### app/Models/RessourceCategorie.php

```php
<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;

class RessourceCategorie extends Model
{
    use HasTablePrefix;

    protected $fillable = ['libelle', 'libelle_court', 'couleur', 'icone', 'ordre'];

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressource::class, 'ressource_categorie_id')->orderBy('ordre');
    }

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_ressource_categorie_gestionnaires', 'ressource_categorie_id', 'user_id');
    }

    public function isGestionnaire(User $user): bool
    {
        return $this->gestionnaires()->where('user_id', $user->id)->exists();
    }

    public function ressourcesActives(): HasMany
    {
        return $this->hasMany(Ressource::class, 'ressource_categorie_id')
            ->where('actif', true)
            ->orderBy('ordre');
    }

    public function getRessourcesActivesForUserAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        $user    = auth()->user();
        $isAdmin = $user?->can('resa::admin') ?? false;
        $uniteId = $user?->unite_id;

        // Admin → toutes les ressources actives
        if ($isAdmin || ! $uniteId) {
            return $this->ressourcesActives()->get();
        }

        // Utilisateur avec unité → sa propre unité + ressources sans unité
        return $this->ressourcesActives()
            ->where(function ($q) use ($uniteId) {
                $q->whereNull('unite_id')->orWhere('unite_id', $uniteId);
            })
            ->get();
    }
}
```

### app/Policies/FormationPolicy.php

```php
<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Formation;

class FormationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::formation';

    /** Gestionnaire d'au moins une formation : accès à la liste (filtrée aux siennes). */
    public function viewAny(?Authenticatable $user): bool
    {
        if (parent::viewAny($user)) {
            return true;
        }

        return (bool) $user?->can('resa::gestionnaire_formation');
    }

    public function view(?Authenticatable $user, $model): bool
    {
        if (parent::view($user, $model)) {
            return true;
        }

        return $this->estGestionnaire($user, $model);
    }

    public function update(Authenticatable $user, $model): bool
    {
        if (parent::update($user, $model)) {
            return true;
        }

        return $this->estGestionnaire($user, $model);
    }

    private function estGestionnaire(?Authenticatable $user, $model): bool
    {
        if (! $user?->can('resa::gestionnaire_formation')) {
            return false;
        }

        return $model instanceof Formation && $model->isGestionnaire($user);
    }
}
```

### app/Policies/ReservationPolicy.php

```php
<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;

class ReservationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::reservation';

    public function valider(Authenticatable $user, Reservation $reservation): bool
    {
        if ($user->can('resa::admin')) {
            return true;
        }

        if ($user->can('resa::gestionnaire_salle')) {
            $salle = $reservation->sallePoste?->salle;
            if ($salle && $salle->isGestionnaire($user)) {
                return true;
            }
        }

        if ($user->can('resa::gestionnaire_formation') && !empty($reservation->formations_utilisees)) {
            return Formation::whereIn('id', $reservation->formations_utilisees)
                ->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        return false;
    }
}
```

### app/Policies/RessourceReservationPolicy.php

```php
<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\RessourceReservation;

class RessourceReservationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::ressource_reservation';

    public function valider(Authenticatable $user, RessourceReservation $reservation): bool
    {
        if ($user->can('resa::admin')) {
            return true;
        }

        if ($user->can('resa::gestionnaire_ressource')) {
            $categorie = $reservation->ressource?->categorie;
            if ($categorie && $categorie->isGestionnaire($user)) {
                return true;
            }
        }

        return false;
    }
}
```

### app/Mail/ReservationSupprimee.php

```php
<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Reservation;

/**
 * En file d'attente : l'envoi (SMTP) ne doit jamais bloquer ni faire planter la requête
 * web qui supprime la réservation.
 */
class ReservationSupprimee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Annulée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'resa::mail.reservation-supprimee',
        );
    }
}
```

### app/Mail/ReservationCree.php

```php
<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Reservation;

/**
 * En file d'attente : l'envoi (SMTP) ne doit jamais bloquer ni faire planter la requête
 * web qui crée/valide la réservation (ex. serveur mail local indisponible en dev).
 */
class ReservationCree extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        // Préfixe "[Resa #id]" stable (même sur l'email de validation et les rappels) :
        // permet de créer une règle de classement Outlook par réservation.
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Demande envoyée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'resa::mail.reservation-cree',
        );
    }
}
```

### app/Mail/ReservationValidee.php

```php
<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;

/**
 * En file d'attente : l'envoi (SMTP) ne doit jamais bloquer ni faire planter la requête
 * web qui valide la réservation (ex. serveur mail local indisponible en dev).
 */
class ReservationValidee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Validée',
        );
    }

    public function content(): Content
    {
        // Calculé ici plutôt qu'en @php inline dans le template : interaction étrange
        // observée entre @php/@endphp et @foreach dans ce projet (compilation Blade
        // Livewire) qui laissait la variable non définie dans la vue.
        //
        // Priorité à la sous-catégorie de la réservation (ex. "FPV" > "Entraînement")
        // quand il y en a une ; sinon la formation elle-même (cas sans sous-catégorie).
        $sousCategorie = $this->reservation->sousCategorie;

        if ($sousCategorie) {
            $consignes = $sousCategorie->validation_auto && $sousCategorie->notice
                ? collect([$sousCategorie->formation?->libelle_court . ' — ' . $sousCategorie->libelle_court => $sousCategorie->notice])
                : collect();
        } else {
            $consignes = Formation::whereIn('id', $this->reservation->formations_utilisees ?? [])
                ->where('validation_auto', true)
                ->whereNotNull('notice')
                ->pluck('notice', 'libelle_court');
        }

        return new Content(
            view: 'resa::mail.reservation-validee',
            with: ['consignes' => $consignes],
        );
    }
}
```

### app/Providers/Filament/FilamentPanelProvider.php

```php
<?php

namespace Modules\Resa\Providers\Filament;

use Modules\Resa\Filament\Pages\ResaDashboard;
use Filament\Support\Enums\Width;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\LoginModalWidget;

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
                ResaDashboard::class,
            ])
            ->discoverWidgets(in: module_path($this->module, 'app/Filament/Widgets'), for: "$moduleNamespace\\Filament\\Widgets")
            ->widgets([
                LoginModalWidget::class,
                \Modules\Resa\Filament\Widgets\ReservationWidget::class,
                \Modules\Resa\Filament\Widgets\SuiviFormationsWidget::class,
                \Modules\Resa\Filament\Widgets\MesReservationsWidget::class,
                //PanelSwitcher::class,
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
            ->maxContentWidth(Width::Full)
            ->renderHook('panels::body.start', fn (): string => '<style>.fi-wi{gap:1.25rem!important}</style>');
    }

    protected function getModuleNamespace(): string
    {
        return config('modules.namespace').'\\'.$this->module;
    }
}
```

### app/Filament/Pages/MesReservationsPage.php

```php
<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\Resa\Filament\Widgets\MesReservationsWidget;

class MesReservationsPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon           = 'heroicon-o-calendar-days';
    protected static ?string                 $navigationLabel          = 'Mes réservations';
    protected static ?string                 $title                    = 'Mes réservations';
    protected string                         $view                     = 'resa::filament.pages.mes-reservations-page';
    protected static string|\UnitEnum|null   $navigationGroup          = null;
    protected static bool                    $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderWidgets(): array
    {
        return [MesReservationsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return ['limite' => 0];
    }
}
```

### app/Filament/Pages/ReservationsFormationPage.php

```php
<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget;

class ReservationsFormationPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon           = 'heroicon-o-clipboard-document-list';
    protected static ?string                 $navigationLabel          = 'Réservations formation';
    protected static ?string                 $title                    = 'Toutes les réservations';
    protected string                         $view                     = 'resa::filament.pages.reservations-formation-page';
    protected static string|\UnitEnum|null   $navigationGroup          = null;
    protected static bool                    $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return SuiviFormationsWidget::canView();
    }

    protected function getHeaderWidgets(): array
    {
        return [SuiviFormationsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        // "Voir tout" depuis le dashboard (qui ne montre que le mois en cours par
        // défaut) : ici on part sur toute la période, le gestionnaire affine ensuite
        // avec les filtres déjà disponibles sur le widget (statut, période, nom).
        return ['filtrePeriode' => 'tout'];
    }
}
```

### app/Filament/Resources/RessourceReservationResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceReservation;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\ListRessourceReservations;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\CreateRessourceReservation;
use Modules\Resa\Filament\Resources\RessourceReservationResource\Pages\EditRessourceReservation;

class RessourceReservationResource extends Resource
{
    protected static ?string $model = RessourceReservation::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cube';
    protected static ?string                 $navigationLabel = 'Réservations matériels';
    protected static string|\UnitEnum|null   $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 2;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('resa::reservation.index')
            || $user?->can('resa::admin')
            || $user?->can('resa::gestionnaire_ressource');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('ressource_id')
                    ->label('Ressource / Matériel')
                    ->options(fn () => Ressource::where('actif', true)->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                Select::make('user_id')
                    ->label('Réservé par')
                    ->options(fn () => User::orderBy('nom')->pluck('nom', 'id'))
                    ->searchable()
                    ->required()
                    ->default(auth()->id()),

                DateTimePicker::make('debut')
                    ->label('Début')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('fin')
                    ->label('Fin')
                    ->required()
                    ->seconds(false)
                    ->after('debut'),

                Textarea::make('libelle')
                    ->label('Objet')
                    ->rows(2)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('debut', 'desc')
            ->columns([
                TextColumn::make('ressource.libelle_court')
                    ->label('Ressource')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ressource.categorie.libelle')
                    ->label('Catégorie')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('libelle')
                    ->label('Objet')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('user.nom')
                    ->label('Réservé par')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('debut')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('validation')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Validée' : 'En attente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('validatedBy.nom')
                    ->label('Validée par')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('validated_at')
                    ->label('Validée le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('validation')
                    ->label('Statut')
                    ->options([
                        '0' => 'En attente',
                        '1' => 'Validée',
                    ]),

                SelectFilter::make('ressource_id')
                    ->label('Ressource')
                    ->options(fn () => Ressource::where('actif', true)->orderBy('libelle_court')->pluck('libelle_court', 'id')),
            ])
            ->recordActions([
                Action::make('toggleValidation')
                    ->label(fn (RessourceReservation $r) => $r->isValide() ? 'Invalider' : 'Valider')
                    ->icon(fn (RessourceReservation $r) => $r->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (RessourceReservation $r) => $r->isValide() ? 'warning' : 'success')
                    ->iconButton()
                    ->requiresConfirmation(fn (RessourceReservation $r) => ! $r->isValide())
                    ->visible(fn (RessourceReservation $r) => auth()->user()?->can('valider', $r) ?? false)
                    ->action(function (RessourceReservation $r): void {
                        $r->isValide() ? $r->invalider() : $r->valider(auth()->user());
                        Notification::make()
                            ->title($r->fresh()->isValide() ? 'Validée' : 'Invalidée')
                            ->success()->send();
                    }),

                \Filament\Actions\EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()
                    ->visible(fn (RessourceReservation $r) =>
                        auth()->user()?->can('resa::admin') || auth()->id() === $r->user_id
                    ),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkActionGroup::make([
                    BulkAction::make('validerSelection')
                        ->label('Valider la sélection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin') || auth()->user()?->can('resa::gestionnaire_ressource'))
                        ->action(function (Collection $records): void {
                            $autorisees = $records->filter(fn (RessourceReservation $r) => auth()->user()?->can('valider', $r));
                            $autorisees->each(fn (RessourceReservation $r) => $r->valider(auth()->user()));
                            Notification::make()->title($autorisees->count() . ' réservation(s) validée(s)')->success()->send();
                        }),

                    BulkAction::make('invaliderSelection')
                        ->label('Invalider la sélection')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin') || auth()->user()?->can('resa::gestionnaire_ressource'))
                        ->action(function (Collection $records): void {
                            $autorisees = $records->filter(fn (RessourceReservation $r) => auth()->user()?->can('valider', $r));
                            $autorisees->each(fn (RessourceReservation $r) => $r->invalider());
                            Notification::make()->title($autorisees->count() . ' réservation(s) invalidée(s)')->warning()->send();
                        }),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['user', 'ressource.categorie', 'validatedBy']);

        $user = auth()->user();
        $estAdminComplet = $user?->can('resa::admin') || $user?->can('resa::reservation.index');

        if ($user && !$estAdminComplet && $user->can('resa::gestionnaire_ressource')) {
            $query->whereHas('ressource.categorie.gestionnaires', fn ($q) => $q->where('user_id', $user->id));
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessourceReservations::route('/'),
            'create' => CreateRessourceReservation::route('/create'),
            'edit'   => EditRessourceReservation::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/SalleResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Modules\Resa\Filament\Resources\SalleResource\Pages\ListSalles;
use Modules\Resa\Filament\Resources\SalleResource\Pages\CreateSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages\EditSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages;
use Modules\Resa\Filament\Resources\SalleResource\RelationManagers;
use Modules\Resa\Models\Salle;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\TypeSalle;

class SalleResource extends Resource
{
    protected static ?string $model = Salle::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type_salle_id')
                    ->label('Type de salle')
                    ->options(TypeSalle::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->required()
                    ->native(false)
                    ->live(),

                Select::make('lieu_unite_id')
                    ->label('Lieu')
                    ->options(LieuUnite::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->native(false)
                    ->dehydrated(false)
                    ->live()
                    ->afterStateHydrated(function ($set, $record) {
                        if ($record?->unite_id) {
                            $set('lieu_unite_id', Unite::find($record->unite_id)?->lieu_unite_id);
                        }
                    })
                    ->afterStateUpdated(fn ($set) => $set('unite_id', null)),

                Select::make('unite_id')
                    ->label('Unité')
                    ->options(fn ($get) => Unite::when(
                        $get('lieu_unite_id'),
                        fn ($q, $id) => $q->where('lieu_unite_id', $id),
                    )->orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->native(false)
                    ->searchable(),
            TextInput::make('code')
                ->maxLength(255)
                ->default(null),
            TextInput::make('libelle_court')
                ->maxLength(500)
                ->default(null)
                ->label('Titre'),
            Textarea::make('libelle_long')

                ->default(null)
                ->label('Description'),
            FileUpload::make('image')
                ->image()
                ->disk('public')
                ->directory('resa/salles') // ✅ stocké dans storage/app/public/planificationgpd/materiels
                ->visibility('public'),

            Select::make('color')
                ->label('Couleur')
                ->options([
                    'primary'   => 'Primary',
                    'secondary' => 'Secondary',
                    'success'   => 'Success',
                    'danger'    => 'Danger',
                    'warning'   => 'Warning',
                    'info'      => 'Info',
                    'gray'      => 'Gray',
                ])
                ->native(false),
            Textarea::make('data')
                ->columnSpanFull(),
                TextInput::make('capacite')
                    ->numeric()
                    ->minValue(1),
                \Modules\Resa\Filament\Components\EmailListField::make('email')
                    ->visible(fn ($get) => TypeSalle::find($get('type_salle_id'))?->mode === TypeSalle::MODE_CLASSIQUE),
                Select::make('gestionnaires')
                    ->label('Gestionnaires')
                    ->relationship('gestionnaires', 'nom')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Ces utilisateurs peuvent gérer cette salle et valider ses réservations.')
                    ->columnSpanFull()
                    // Pour une salle simulateur, la gestion se fait par formation (Resa > Formations),
                    // pas par salle : éviter de devoir assigner le gestionnaire à deux endroits.
                    ->visible(fn ($get) => TypeSalle::find($get('type_salle_id'))?->mode === TypeSalle::MODE_CLASSIQUE),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre','asc')
            ->reorderable('ordre')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('typeSalle.libelle_court')
                    ->label('Type de salle')
                    ->sortable()
                    ->badge(),
                TextColumn::make('unite.lieuUnite.libelle_court')
                    ->label('Lieu')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                TextColumn::make('unite.libelle_court')
                    ->label('Unité')
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->searchable()
                    ->label('Libelle'),
                TextColumn::make('capacite')
                    ->searchable()
                    ->label('Capacite'),
                TextColumn::make('libelle_long')

                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image')
                ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('color')

                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('lieu')
                ->label('Lieu')
                ->options(LieuUnite::orderBy('ordre')->pluck('libelle_court', 'id'))
                ->query(fn ($query, $data) => $data['value']
                    ? $query->whereHas('unite', fn ($q) => $q->where('lieu_unite_id', $data['value']))
                    : $query
                ),
            SelectFilter::make('type_salle_id')
                ->label('Type de salle')
                ->options(TypeSalle::orderBy('ordre')->pluck('libelle_court', 'id')),
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toolbarActions([
            DeleteBulkAction::make(),
        ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalles::route('/'),
            'create' => CreateSalle::route('/create'),
            'edit' => EditSalle::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/ReservationResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\EditReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\ListReservations;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeSalle;
use Modules\Resa\Models\Formation;
use App\Models\User;
use Filament\Forms\Components\Repeater;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string                 $navigationLabel = 'Reservations';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 1;

    /* ── Acces ────────────────────────────────────────────────────────────── */

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('resa::reservation.index')
            || $user?->can('resa::admin')
            || $user?->can('resa::gestionnaire_salle');
    }

    /* ── Formulaire ───────────────────────────────────────────────────────── */

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Grid::make(2)->schema([

                TextInput::make('libelle_court')
                    ->label('Intitulé de la réservation')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Select::make('salle_poste_id')
                    ->label('Poste / Salle')
                    ->options(function () {
                        return SallePoste::with('salle')
                            ->get()
                            ->mapWithKeys(fn ($p) =>
                                [$p->id => ($p->salle?->libelle_court ?? '?') . ' — ' . $p->libelle_court]
                            );
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->columnSpanFull(),

                DateTimePicker::make('debut')
                    ->label('Date début')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('fin')
                    ->label('Date fin')
                    ->required()
                    ->seconds(false)
                    ->after('debut'),

                // ── Participants (tous types de salles) ──────────────────────
                Repeater::make('participants')
                    ->label('Participants')
                    ->addActionLabel('Ajouter un participant')
                    ->reorderable(false)
                    ->minItems(1)
                    ->maxItems(function ($get) {
                        $posteId = $get('salle_poste_id');
                        if (! $posteId) return 50;
                        $salle = SallePoste::find($posteId)?->salle;
                        return max(1, (int) ($salle?->capacite ?? 50));
                    })
                    ->default(fn () => [['user_id' => auth()->id()]])
                    ->schema([
                        Select::make('user_id')
                            ->label('Personne')
                            ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                            ->searchable()
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // ── Formations (simulateur drone uniquement) ─────────────────
                CheckboxList::make('formations_utilisees')
                    ->label('Formations utilisées')
                    ->options(Formation::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->columns(3)
                    ->columnSpanFull()
                    ->visible(function ($get) {
                        $posteId = $get('salle_poste_id');
                        if (! $posteId) return false;
                        $salle = SallePoste::find($posteId)?->salle;
                        return $salle?->typeSalle?->mode === TypeSalle::MODE_SIMULATEUR;
                    }),

                Textarea::make('libelle_long')
                    ->label('Informations complémentaires')
                    ->rows(3)
                    ->columnSpanFull(),
            ]),
        ]);
    }

    /* ── Table ────────────────────────────────────────────────────────────── */

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('debut', 'desc')
            ->columns([
                TextColumn::make('libelle_court')
                    ->label('Reservation')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('participants_label')
                    ->label('Participants')
                    ->getStateUsing(function (Reservation $record): string {
                        $userIds = $record->data['users'] ?? [];
                        if (empty($userIds) && $record->user_id) {
                            $userIds = [$record->user_id];
                        }
                        if (empty($userIds)) return '—';
                        return User::whereIn('id', $userIds)
                            ->get()
                            ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                            ->filter()
                            ->join(', ') ?: '—';
                    })
                    ->searchable(query: fn (Builder $query, string $search) =>
                        $query->whereHas('user', fn ($q) =>
                            $q->where('nom', 'like', "%{$search}%")
                              ->orWhere('prenom', 'like', "%{$search}%")
                        )
                    )
                    ->wrap(),

                TextColumn::make('sallePoste.salle.libelle_court')
                    ->label('Salle')
                    ->sortable(),

                TextColumn::make('sallePoste.libelle_court')
                    ->label('Poste')
                    ->sortable(),

                TextColumn::make('formations_label')
                    ->label('Formations')
                    ->getStateUsing(function (Reservation $record): string {
                        $ids = $record->formations_utilisees;
                        if (empty($ids) || ! is_array($ids)) return '—';
                        return Formation::whereIn('id', $ids)
                            ->orderBy('ordre')
                            ->pluck('libelle_court')
                            ->join(', ') ?: '—';
                    }),

                TextColumn::make('debut')
                    ->label('Debut')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('fin')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('validation')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Validee' : 'En attente')
                    ->color(fn ($state) => $state ? 'success' : 'warning'),

                TextColumn::make('validatedBy.nom')
                    ->label('Valide par')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('validated_at')
                    ->label('Valide le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('validation')
                    ->label('Statut')
                    ->options([
                        '0' => 'En attente',
                        '1' => 'Validee',
                    ]),

                SelectFilter::make('salle')
                    ->label('Salle')
                    ->relationship('sallePoste.salle', 'libelle_court'),
            ])
            ->recordActions([
                // Toggle validation rapide
                Action::make('toggleValidation')
                    ->label(fn (Reservation $record) => $record->isValide() ? 'Invalider' : 'Valider')
                    ->icon(fn (Reservation $record) => $record->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Reservation $record) => $record->isValide() ? 'warning' : 'success')
                    ->iconButton()
                    ->requiresConfirmation(fn (Reservation $record) => ! $record->isValide())
                    ->modalHeading('Valider la reservation ?')
                    ->modalDescription(fn (Reservation $record) => 'Confirmer la validation de « ' . $record->libelle_court . ' » ?')
                    ->visible(fn (Reservation $record) => auth()->user()?->can('valider', $record))
                    ->action(function (Reservation $record): void {
                        $record->isValide()
                            ? $record->invalider()
                            : $record->valider(auth()->user());

                        Notification::make()
                            ->title($record->fresh()->isValide() ? 'Reservation validee' : 'Reservation invalidee')
                            ->success()
                            ->send();
                    }),

                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),

                BulkActionGroup::make([
                    BulkAction::make('validerSelection')
                        ->label('Valider la selection')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin') || auth()->user()?->can('resa::gestionnaire_salle'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (Reservation $record) =>
                                $record->valider(auth()->user())
                            );

                            Notification::make()
                                ->title($records->count() . ' reservation(s) validee(s)')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('invaliderSelection')
                        ->label('Invalider la selection')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()?->can('resa::admin') || auth()->user()?->can('resa::gestionnaire_salle'))
                        ->action(function (Collection $records): void {
                            $records->each(fn (Reservation $record) => $record->invalider());

                            Notification::make()
                                ->title($records->count() . ' reservation(s) invalidee(s)')
                                ->warning()
                                ->send();
                        }),
                ]),
            ]);
    }

    /* ── Relations ────────────────────────────────────────────────────────── */

    public static function getRelations(): array
    {
        return [];
    }

    /* ── Pages ────────────────────────────────────────────────────────────── */

    public static function getPages(): array
    {
        return [
            'index'  => ListReservations::route('/'),
            'create' => CreateReservation::route('/create'),
            'edit'   => EditReservation::route('/{record}/edit'),
        ];
    }

    /* ── Query de base ────────────────────────────────────────────────────── */

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery()
            ->with(['user', 'sallePoste.salle', 'validatedBy']);

        if ($user?->can('resa::admin') || $user?->can('resa::reservation.index')) {
            return $query;
        }

        // Gestionnaire : uniquement les réservations de ses salles (directement, ou via
        // les formations qu'il gère pour les salles simulateur — cf. Salle::idsGereesPar).
        $salleIds = Salle::idsGereesPar($user);

        return $query->whereHas(
            'sallePoste',
            fn ($q) => $q->whereIn('salle_id', $salleIds)
        );
    }
}
```

### app/Filament/Resources/RessourceCategorieResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeSalles\Schemas\TypeSalleForm;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\ListRessourceCategories;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\CreateRessourceCategorie;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\EditRessourceCategorie;
use Modules\Resa\Models\RessourceCategorie;

class RessourceCategorieResource extends Resource
{
    protected static ?string $model = RessourceCategorie::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-tag';
    protected static ?string                 $navigationLabel = 'Catégories de ressources';
    protected static string|\UnitEnum|null   $navigationGroup = 'Ressources';
    protected static ?int                    $navigationSort  = 10;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('resa::admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('libelle')->label('Libellé')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('libelle_court')->label('Libellé court')->maxLength(50),
            TextInput::make('ordre')->label('Ordre')->numeric()->default(0),
            Select::make('icone')
                ->label('Icône')
                ->searchable()
                ->options(TypeSalleForm::iconOptions())
                ->placeholder('Choisir une icône'),
            Select::make('couleur')
                ->label('Couleur')
                ->options(TypeSalleForm::colorOptions())
                ->placeholder('Choisir une couleur'),
            Select::make('gestionnaires')
                ->label('Gestionnaires')
                ->relationship('gestionnaires', 'nom')
                ->multiple()
                ->searchable()
                ->preload()
                ->helperText('Ces utilisateurs peuvent valider les réservations du matériel de cette catégorie.')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('ordre')->label('#')->sortable(),
                TextColumn::make('libelle')->label('Catégorie')->searchable()->sortable(),
                TextColumn::make('libelle_court')->label('Court'),
                TextColumn::make('couleur')->label('Couleur')->badge(),
                TextColumn::make('ressources_count')->label('Ressources')
                    ->counts('ressources')
                    ->badge(),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessourceCategories::route('/'),
            'create' => CreateRessourceCategorie::route('/create'),
            'edit'   => EditRessourceCategorie::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/RessourceResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Modules\RH\Models\Unite;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\CreateRessource;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\EditRessource;
use Modules\Resa\Filament\Resources\RessourceResource\Pages\ListRessources;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;

class RessourceResource extends Resource
{
    protected static ?string $model = Ressource::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cube';
    protected static ?string                 $navigationLabel = 'Ressources';
    protected static string|\UnitEnum|null   $navigationGroup = 'Ressources';
    protected static ?int                    $navigationSort  = 11;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('resa::admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ressource_categorie_id')
                ->label('Catégorie')
                ->options(RessourceCategorie::orderBy('ordre')->pluck('libelle', 'id'))
                ->required()
                ->columnSpanFull(),

            Select::make('unite_id')
                ->label('Unité')
                ->options(Unite::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                ->searchable()
                ->nullable(),

            TextInput::make('libelle')->label('Libellé')->required()->maxLength(255),
            TextInput::make('libelle_court')->label('Libellé court')->maxLength(50),
            TextInput::make('ordre')->label('Ordre')->numeric()->default(0),
            Toggle::make('actif')->label('Actif')->default(true)
                ->helperText('Décoché = retiré du catalogue (ne plus proposer du tout).'),

            Select::make('statut')
                ->label('Disponibilité')
                ->options(Ressource::STATUTS)
                ->default(Ressource::STATUT_DISPONIBLE)
                ->required()
                ->native(false)
                ->helperText('Permet de signaler un équipement en entretien ou indisponible sans le retirer du catalogue.'),

            Textarea::make('description')->label('Description')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('ordre')
            ->columns([
                TextColumn::make('ordre')->label('#')->sortable(),
                TextColumn::make('categorie.libelle')->label('Catégorie')->sortable()->badge(),
                TextColumn::make('libelle')->label('Ressource')->searchable()->sortable(),
                TextColumn::make('libelle_court')->label('Court'),
                IconColumn::make('actif')->label('Actif')->boolean(),
                TextColumn::make('statut')
                    ->label('Disponibilité')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Ressource::STATUTS[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Ressource::STATUT_DISPONIBLE   => 'success',
                        Ressource::STATUT_ENTRETIEN     => 'warning',
                        Ressource::STATUT_INDISPONIBLE => 'danger',
                        default                         => 'gray',
                    }),
                TextColumn::make('reservations_count')->label('Réservations')
                    ->counts('reservations')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('ressource_categorie_id')
                    ->label('Catégorie')
                    ->options(RessourceCategorie::orderBy('ordre')->pluck('libelle', 'id')),
                SelectFilter::make('statut')
                    ->label('Disponibilité')
                    ->options(Ressource::STATUTS),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRessources::route('/'),
            'create' => CreateRessource::route('/create'),
            'edit'   => EditRessource::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Components/EmailListField.php

```php
<?php

namespace Modules\Resa\Filament\Components;

use Filament\Forms\Components\TagsInput;

/**
 * Champ "liste d'emails" affiché comme des badges (ajout/suppression individuelle),
 * tout en restant stocké en base comme une simple chaîne séparée par des virgules
 * (colonne `email` existante sur Salle/Formation — pas de migration nécessaire).
 */
class EmailListField
{
    public static function make(string $name = 'email'): TagsInput
    {
        return TagsInput::make($name)
            ->label('Email(s) de notification')
            ->helperText('Un email est envoyé à chaque nouvelle réservation. Tapez une adresse puis Entrée pour l\'ajouter ; cliquez sur le × pour en retirer une.')
            ->placeholder('Ajouter une adresse email…')
            ->separator(',')
            ->afterStateHydrated(fn (TagsInput $component, $state) => $component->state(self::toArray($state)))
            ->dehydrateStateUsing(fn ($state) => self::toString($state))
            ->columnSpanFull();
    }

    /**
     * Chaîne séparée par des virgules (valeur brute en base) → liste d'emails nettoyée.
     * Accepte aussi directement un tableau (Filament peut rappeler afterStateHydrated()
     * avec un état déjà transformé lors d'un précédent cycle) : renvoyé tel quel, nettoyé.
     */
    public static function toArray(array|string|null $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value))));
    }

    /** Liste d'emails (état du TagsInput) → chaîne séparée par des virgules pour la base. */
    public static function toString(?array $value): string
    {
        return implode(',', array_filter((array) $value));
    }
}
```

### app/Filament/Widgets/SuiviFormationsWidget.php

```php
<?php

namespace Modules\Resa\Filament\Widgets;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget\Actions\VoirStagiaireAction;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;

class SuiviFormationsWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'resa::filament.widgets.suivi-formations-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    protected static ?int $sort         = 1;

    protected int|string|array $columnSpan = 'full';

    // Filtres
    public ?int    $filtreFormationId = null;
    public ?int    $filtreStatut      = null; // null=tous, 0=en attente, 1=validé
    public string  $filtrePeriode     = 'mois'; // 'semaine' | 'mois' | 'annee' | 'tout'
    public string  $filtreNom         = '';

    // Incrémenté à chaque (in)validation : utilisé dans la clé Livewire du calendrier
    // embarqué pour forcer son remontage complet (données et couleurs à jour), plutôt
    // que de compter sur l'événement JS global "calendar--refresh" (qui dépend du
    // chargement différé — x-load — du calendrier et peut rater la fenêtre).
    public int $calendarVersion = 0;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($user->can('resa::gestionnaire_salle')) return true;

        // Gestionnaire d'au moins une formation
        return Formation::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->exists();
    }

    public function mount(): void
    {
        // Par défaut, le gestionnaire voit toutes ses formations (filtreFormationId
        // reste null = "Toutes") ; il choisit lui-même de filtrer sur un onglet précis.
    }

    public function isAdmin(): bool
    {
        return auth()->user()?->can('resa::gestionnaire_salle') ?? false;
    }

    public function afficherOnglets(): bool
    {
        return ! $this->isAdmin() && $this->getFormationsDisponibles()->count() > 1;
    }

    public function selectionnerFormation(?int $id): void
    {
        $this->filtreFormationId = $id;
    }

    /**
     * Ouvre l'assistant de création de réservation du calendrier transversal embarqué,
     * pour la formation actuellement affichée (onglet actif, ou la première formation
     * gérée à défaut). La salle est déduite automatiquement : en priorité la salle dont
     * le gestionnaire a la charge, sinon la première salle disposant de postes
     * configurés pour cette formation.
     */
    public function ouvrirNouvelleReservation(): void
    {
        $formationId = $this->filtreFormationId ?: $this->getFormationsDisponibles()->first()?->id;

        if (! $formationId) {
            Notification::make()->title('Aucune formation disponible.')->danger()->send();
            return;
        }

        $userId = auth()->id();

        $salle = Salle::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $userId))
            ->whereHas('sallePostes.formations', fn ($q) => $q->where('resa_formations.id', $formationId))
            ->first()
            ?? Salle::whereHas('sallePostes.formations', fn ($q) => $q->where('resa_formations.id', $formationId))->first();

        if (! $salle) {
            Notification::make()
                ->title('Aucune salle n\'a de poste configuré pour cette formation.')
                ->body('Rattachez des postes à cette formation depuis Resa > Postes de salle.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('resa-ouvrir-nouvelle-reservation', salleId: $salle->id, formationId: $formationId);
    }

    public function getFormationsDisponibles(): Collection
    {
        if ($this->isAdmin()) {
            return Formation::orderBy('ordre')->get();
        }

        return Formation::whereHas('gestionnaires', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('ordre')
            ->get();
    }

    /** IDs de formation à inclure dans les requêtes : la formation active, ou toutes celles visibles. */
    private function formationIdsPourQuery(): Collection
    {
        if ($this->filtreFormationId) {
            return collect([$this->filtreFormationId]);
        }

        return $this->getFormationsDisponibles()->pluck('id');
    }

    private function scopeFormations($query, Collection $formationIds): void
    {
        $query->where(function ($q) use ($formationIds) {
            foreach ($formationIds as $id) {
                $q->orWhereJsonContains('formations_utilisees', (int) $id)
                  ->orWhereJsonContains('formations_utilisees', (string) $id);
            }
        });
    }

    /* ── Cartes de statistiques (indépendantes des filtres période/statut/nom) ── */

    public function getStats(): array
    {
        $formationIds = $this->formationIdsPourQuery();

        $base = Reservation::query()->whereJsonLength('formations_utilisees', '>', 0);
        $this->scopeFormations($base, $formationIds);

        $aVenir = (clone $base)->where('debut', '>=', now())->count();
        $enAttente = (clone $base)->where('debut', '>=', now())->where('validation', Reservation::EN_ATTENTE)->count();
        $ceMois = (clone $base)->whereBetween('debut', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return [
            'nb_formations' => $this->getFormationsDisponibles()->count(),
            'a_venir'       => $aVenir,
            'en_attente'    => $enAttente,
            'ce_mois'       => $ceMois,
        ];
    }

    /** IDs transmis au CalendrierWidget (mode transversal) pour filtrer par formation. */
    public function getFormationIdsCalendrier(): array
    {
        return $this->formationIdsPourQuery()->values()->all();
    }

    /* ── Tableau détaillé (avec filtres période/statut/nom) ───────────────── */

    public function getReservations(): Collection
    {
        $formationIds = $this->formationIdsPourQuery();

        $query = Reservation::with(['user', 'sallePoste.salle', 'validatedBy'])
            ->whereJsonLength('formations_utilisees', '>', 0);

        $this->scopeFormations($query, $formationIds);

        // Filtre statut
        if ($this->filtreStatut !== null) {
            $query->where('validation', $this->filtreStatut);
        }

        // Filtre période
        match ($this->filtrePeriode) {
            'semaine' => $query->where('debut', '>=', now()->startOfWeek()),
            'mois'    => $query->where('debut', '>=', now()->startOfMonth()),
            'annee'   => $query->where('debut', '>=', now()->startOfYear()),
            default   => null,
        };

        // Filtre nom stagiaire (cherche dans user principal ET utilisateurs annexes via data->users)
        if ($this->filtreNom !== '') {
            $matchingUserIds = User::where('nom', 'like', '%' . $this->filtreNom . '%')
                ->orWhere('prenom', 'like', '%' . $this->filtreNom . '%')
                ->pluck('id');

            $query->where(function ($q) use ($matchingUserIds) {
                $q->whereIn('user_id', $matchingUserIds);
                foreach ($matchingUserIds as $uid) {
                    $q->orWhereJsonContains('data->users', (int) $uid)
                      ->orWhereJsonContains('data->users', (string) $uid);
                }
            });
        }

        // Pré-charger tous les users nécessaires en une requête
        $reservations = $query->orderBy('debut', 'desc')->get();
        $allUserIds = $reservations->flatMap(function (Reservation $r) {
            $ids = collect($r->data['users'] ?? [])->filter()->values();
            if ($r->user_id) $ids->push($r->user_id);
            if ($r->created_by) $ids->push($r->created_by);
            return $ids;
        })->unique()->filter();

        $usersMap = User::whereIn('id', $allUserIds)->get()->keyBy('id');

        return $reservations->map(function (Reservation $r) use ($usersMap) {
            $formationLabels = Formation::whereIn('id', $r->formations_utilisees ?? [])
                ->orderBy('ordre')
                ->pluck('libelle_court')
                ->join(', ');

            // Tous les stagiaires depuis data.users, fallback sur user_id
            $userIds = collect($r->data['users'] ?? [])->filter()->values();
            if ($userIds->isEmpty() && $r->user_id) {
                $userIds = collect([$r->user_id]);
            }
            $stagiaires = $userIds->map(fn ($id) => $usersMap->get($id))
                ->filter()
                ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                ->filter()
                ->values()
                ->toArray();

            $validatedBy = $r->validated_by ? $usersMap->get($r->validated_by) : null;
            $creePar     = $r->created_by ? $usersMap->get($r->created_by) : null;

            return [
                'id'           => $r->id,
                'stagiaires'   => $stagiaires,
                'cree_par'     => $creePar ? trim("{$creePar->nom} {$creePar->prenom}") : null,
                'formation'    => $formationLabels ?: '—',
                'poste'        => $r->sallePoste?->libelle_court ?? '—',
                'salle'        => $r->sallePoste?->salle?->libelle_court ?? '—',
                'debut'        => $r->debut?->format('d/m/Y H:i'),
                'fin'          => $r->fin?->format('H:i'),
                'valide'       => $r->isValide(),
                'validated_by' => $validatedBy ? trim("{$validatedBy->nom} {$validatedBy->prenom}") : null,
                'validated_at' => $r->validated_at?->format('d/m/Y'),
                'peut_valider' => auth()->user()?->can('valider', $r) ?? false,
                // Pointeuse : le marin pointe normalement lui-même (widget "Mes
                // réservations") ; le gestionnaire ne peut le faire qu'en secours, avec
                // les mêmes droits que pour la validation.
                'presence'       => $r->presence,
                'peut_pointer'   => $r->peutPointer() && (auth()->user()?->can('valider', $r) ?? false),
            ];
        });
    }

    public function toggleValidation(int $reservationId): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (! auth()->user()?->can('valider', $reservation)) {
            Notification::make()->title("Vous n'êtes pas autorisé à valider cette réservation.")->danger()->send();
            return;
        }

        if ($reservation->isValide()) {
            $reservation->invalider();
            Notification::make()->title('Réservation invalidée')->warning()->send();
        } else {
            $reservation->valider(auth()->user());
            Notification::make()->title('Réservation validée')->success()->send();
        }

        // Le calendrier (CalendrierWidget) est un composant Livewire séparé : on force
        // son remontage complet (clé Livewire) pour que la couleur de l'événement
        // (gris/vert) suive immédiatement le changement de statut.
        $this->calendarVersion++;
        $this->dispatch('calendar--refresh');
    }

    public function pointerGestionnaire(int $reservationId, bool $present): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (! auth()->user()?->can('valider', $reservation)) {
            Notification::make()->title("Vous n'êtes pas autorisé à pointer cette réservation.")->danger()->send();
            return;
        }

        if (! $reservation->peutPointer()) {
            Notification::make()->title('Pointage possible uniquement le jour de la séance, une fois validée.')->danger()->send();
            return;
        }

        $reservation->pointer($present, auth()->user());

        Notification::make()
            ->title($present ? 'Présence enregistrée' : 'Absence enregistrée')
            ->success()
            ->send();
    }

    public function voirStagiaireAction(): Action
    {
        return VoirStagiaireAction::make($this);
    }

    public function updatedFiltreFormationId(): void {}
    public function updatedFiltreStatut(): void {}
    public function updatedFiltrePeriode(): void {}
    public function updatedFiltreNom(): void {}
}
```

### app/Filament/Widgets/RessourceGanttWidget.php

```php
<?php

namespace Modules\Resa\Filament\Widgets;

use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\SeConnecterAction;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\VoirResaAction;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\RessourceReservation;

class RessourceGanttWidget extends CalendarWidget
{
    protected string $view = 'resa::filament.widgets.ressource-gantt-widget';

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected CalendarViewType $calendarView = CalendarViewType::ResourceTimelineWeek;

    protected bool $eventClickEnabled   = true;
    protected bool $dateSelectEnabled   = true;
    protected bool $eventDragEnabled    = true;
    protected bool $eventResizeEnabled  = true;
    protected ?string $locale           = 'fr';
    protected bool $useFilamentTimezone = true;

    // Navigation
    public string  $etape           = 'gantt'; // 'lieu' | 'unite' | 'gantt'
    public ?int    $filtreCategorie = null;
    public ?int    $filtreLieu      = null;
    public ?int    $filtreUnite     = null;

    // État admin
    public bool $adminMode = false;

    // État pour voir une réservation cliquée
    public ?int $viewingId = null;

    // État pour créer
    public ?string $newDebut       = null;
    public ?string $newFin         = null;
    public ?int    $newRessourceId = null;

    public function mount(): void
    {
        $this->adminMode = auth()->user()?->can('resa::admin') ?? false;

        if ($this->filtreCategorie && \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id')) {
            $hasUnite = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
                ->where('actif', true)
                ->whereNotNull('unite_id')
                ->exists();
            $this->etape = $hasUnite ? 'lieu' : 'gantt';
        }
    }

    /* ── Navigation lieu/unité ────────────────────────────────────────────── */

    public function getLieux()
    {
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)->whereNotNull('unite_id')->pluck('unite_id');

        return LieuUnite::whereIn('id',
            Unite::whereIn('id', $uniteIds)->pluck('lieu_unite_id')
        )->orderBy('ordre')->get();
    }

    public function getUnites()
    {
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)->whereNotNull('unite_id')->pluck('unite_id');

        return Unite::whereIn('id', $uniteIds)
            ->when($this->filtreLieu, fn ($q) => $q->where('lieu_unite_id', $this->filtreLieu))
            ->orderBy('ordre')->get();
    }

    public function selectLieu(int $id): void
    {
        $this->filtreLieu = $id;
        $this->etape      = 'unite';
    }

    public function selectUnite(int $id): void
    {
        $this->filtreUnite = $id;
        $this->etape       = 'gantt';
    }

    public function retourLieux(): void
    {
        $this->filtreLieu  = null;
        $this->filtreUnite = null;
        $this->etape       = 'lieu';
    }

    public function retourUnites(): void
    {
        $this->filtreUnite = null;
        $this->etape       = 'unite';
    }

    /* ── Ressources Guava (colonnes du timeline) ──────────────────────────── */

    public function getResources(): Collection|array
    {
        if ($this->etape !== 'gantt') return [];

        try {
            $user        = auth()->user();
            $isAdmin     = $user?->can('resa::admin') ?? false;
            $userUniteId = \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id')
                ? $user?->unite_id
                : null;

            $hasUniteCol = \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id');

            return Ressource::query()
                ->where('actif', true)
                ->when($this->filtreCategorie, fn ($q) => $q->where('ressource_categorie_id', $this->filtreCategorie))
                ->when($hasUniteCol && $this->filtreUnite, fn ($q) => $q->where('unite_id', $this->filtreUnite))
                ->when($hasUniteCol && ! $this->filtreUnite && ! $isAdmin && $userUniteId, fn ($q) =>
                    $q->where(fn ($q2) => $q2->whereNull('unite_id')->orWhere('unite_id', $userUniteId))
                )
                ->orderBy('ordre')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function getResourcesJs(): array
    {
        return $this->getResources()
            ->map(function (Ressource $r) {
                $titre = $r->nom;
                $titre .= match ($r->statut) {
                    Ressource::STATUT_ENTRETIEN     => ' ⚠️ En entretien',
                    Ressource::STATUT_INDISPONIBLE => ' ⛔ Indisponible',
                    default                         => '',
                };

                return \Guava\Calendar\ValueObjects\CalendarResource::make($r->id)
                    ->title($titre)
                    ->toCalendarObject();
            })
            ->toArray();
    }

    /* ── Événements ───────────────────────────────────────────────────────── */

    public function getEventsJs(array $info): array
    {
        $events = $this->getEvents(new FetchInfo($info));

        if ($events instanceof Builder) {
            $events = $events->get();
        }

        if (is_array($events)) {
            $events = collect($events);
        }

        $isAdmin   = $this->adminMode;
        $tzOffset  = data_get($info, 'tzOffset');
        $useFilTz  = $this->shouldUseFilamentTimezone();

        return $events
            ->map(function (RessourceReservation $r) use ($isAdmin, $tzOffset, $useFilTz): array {
                $nom   = $isAdmin ? ($r->user?->nom ?? '') : '';
                $titre = implode("\n", array_filter([
                    $r->libelle ?: ($r->user?->nom ?? ''),
                    $nom && $r->libelle ? '👤 ' . $nom : '',
                    $r->debut?->format('H:i') . ' — ' . $r->fin?->format('H:i'),
                ]));

                $couleur = $r->validation === 1 ? '#22c55e' : '#f59e0b';

                return \Guava\Calendar\ValueObjects\CalendarEvent::make($r)
                    ->title($titre)
                    ->start($r->debut?->format('Y-m-d H:i:s'))
                    ->end($r->fin?->format('Y-m-d H:i:s'))
                    ->resourceId($r->ressource_id)
                    ->backgroundColor($couleur)
                    ->editable($r->validation !== 1 && auth()->id() === $r->user_id)
                    ->toCalendarObject($tzOffset, $useFilTz);
            })
            ->all();
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        if ($this->etape !== 'gantt') return [];

        $ressourceIds = $this->getResources()->pluck('id');

        // Catégories dont l'utilisateur est gestionnaire : il voit aussi les réservations
        // en attente du matériel de ces catégories, qu'il n'a pas lui-même créées.
        $user = auth()->user();
        $categorieIdsGerees = ($user && $user->can('resa::gestionnaire_ressource'))
            ? \Modules\Resa\Models\RessourceCategorie::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->pluck('id')
            : collect();

        return RessourceReservation::query()
            ->with(['user', 'ressource'])
            ->whereIn('ressource_id', $ressourceIds)
            ->where('debut', '<', $info->end)
            ->where('fin',   '>', $info->start)
            ->when(! $this->adminMode, fn ($q) =>
                $q->where(function ($q2) use ($categorieIdsGerees) {
                    $q2->where('validation', RessourceReservation::VALIDE)
                        ->orWhere('user_id', auth()->id());

                    if ($categorieIdsGerees->isNotEmpty()) {
                        $q2->orWhereHas('ressource', fn ($q3) => $q3->whereIn('ressource_categorie_id', $categorieIdsGerees));
                    }
                })
            );
    }

    /* ── Options ──────────────────────────────────────────────────────────── */

    public function getOptions(): array
    {
        return [
            'nowIndicator'        => true,
            'height'              => 'auto',
            'selectable'          => true,
            'editable'            => auth()->check(),
            'hiddenDays'          => [0, 6],
            'datesAboveResources' => true,
            'headerToolbar'       => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'resourceTimelineDay,resourceTimelineWeek,resourceTimeGridWeek,dayGridMonth',
            ],
            'buttonText' => [
                'today'                 => "Aujourd'hui",
                'resourceTimelineDay'   => 'Jour',
                'resourceTimelineWeek'  => 'Semaine',
                'resourceTimeGridWeek'  => 'Grille',
                'dayGridMonth'          => 'Mois',
            ],
            'locale'      => 'fr',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '18:00:00',
            'scrollTime'  => '08:00:00',
            'allDaySlot'  => false,
            'views'       => [
                'resourceTimelineDay'  => ['slotDuration' => '01:00:00'],
                'resourceTimelineWeek' => ['slotDuration' => '01:00:00'],
            ],
        ];
    }

    /* ── Heading ──────────────────────────────────────────────────────────── */

    public function getHeading(): ?string
    {
        if ($this->filtreCategorie) {
            $cat = RessourceCategorie::find($this->filtreCategorie);
            return 'Réservations — ' . ($cat?->libelle ?? '');
        }
        return 'Réservations de ressources';
    }

    /* ── DateSelect → ouvrir modal création ──────────────────────────────── */

    protected function onDateSelect(DateSelectInfo $info): void
    {
        if (! auth()->check()) {
            $this->mountAction('seConnecter');
            return;
        }

        $this->newDebut       = $info->start->toDateTimeString();
        $this->newFin         = $info->end->toDateTimeString();
        $this->newRessourceId = null;

        $this->mountAction('nouvelleResa');
    }

    /* ── EventClick → ouvrir modal vue ───────────────────────────────────── */

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        $this->viewingId = $info->record->id;
        $this->mountAction('voirResa');
    }

    /* ── Drag & Resize ────────────────────────────────────────────────────── */

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        $record = $info->record;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;
        $record->update(['debut' => $info->event->getStart(), 'fin' => $info->event->getEnd()]);
        return true;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        $record = $info->record;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;
        $record->update(['fin' => $info->event->getEnd()]);
        return true;
    }

    /* ── Header actions ───────────────────────────────────────────────────── */

    public function getHeaderActions(): array
    {
        if ($this->etape !== 'gantt') return [];

        return [
            NouvelleResaAction::make($this),
            VoirResaAction::make($this),
        ];
    }

    public function seConnecterAction(): \Filament\Actions\Action
    {
        return SeConnecterAction::make($this);
    }

    public function redirectSso(): void
    {
        session()->put('login_redirect_after', url()->current());
        $this->redirect(route('keycloak.login.redirect'));
    }
}
```

### app/Filament/Widgets/ReservationWidget.php

```php
<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Widgets\Widget;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\TypeSalle;

class ReservationWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'resa::filament.widgets.reservation-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    protected static ?int $sort         = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        // Un gestionnaire de formation "pur" (ni admin, ni gestionnaire de salle) a
        // déjà son tableau de bord dédié (SuiviFormationsWidget) : pas besoin de
        // l'assistant générique de configuration de réservation.
        if ($user?->can('resa::gestionnaire_formation')
            && ! $user->can('resa::gestionnaire_salle')
            && ! $user->can('resa::admin')
            && ! $user->can('resa::reservation.view')
        ) {
            return false;
        }

        return true;
    }

    public ?int   $lieuActive  = null;
    public ?int   $uniteActive = null;
    public string $typeResa    = '';

    public function mount(): void
    {
        $this->lieuActive  = session('resa_nav_lieu')  ? (int) session('resa_nav_lieu')  : null;
        $this->uniteActive = session('resa_nav_unite') ? (int) session('resa_nav_unite') : null;
        $this->typeResa    = session('resa_nav_type', '');

        // Un seul lieu ET une seule unité : on les résout tout de suite plutôt que de
        // faire passer l'utilisateur par un choix évident — la page d'accueil va
        // directement au choix du type de réservation (vue "dashboard"). Dès qu'il y a
        // un choix réel à faire (2+ lieux, ou 2+ unités dans le lieu), on repasse par
        // l'ancien parcours (modal "Configurer ma réservation") : uniteActive reste
        // null, cf. @if(! $this->uniteActive) dans la vue.
        if (! $this->lieuActive) {
            $lieux = $this->optionsLieux();
            if (count($lieux) === 1) {
                $this->lieuActive = array_key_first($lieux);
            }
        }

        if ($this->lieuActive && ! $this->uniteActive) {
            $unites = $this->optionsUnites($this->lieuActive);
            if (count($unites) === 1) {
                $this->uniteActive = array_key_first($unites);
            }
        }
    }

    /** Choix direct du type de réservation depuis la page d'accueil (sans modal). */
    public function selectionnerType(string $type): void
    {
        $this->typeResa = $type;

        session([
            'resa_nav_lieu'  => $this->lieuActive,
            'resa_nav_unite' => $this->uniteActive,
            'resa_nav_type'  => $this->typeResa,
        ]);
    }

    /** Revient au choix du type (garde le lieu/unité déjà résolus). */
    public function changerType(): void
    {
        $this->typeResa = '';
        session(['resa_nav_type' => '']);
    }

    // ─── Modal configuration (étape unique, tout réactif) ────────────────────

    public function configurerAction(): Action
    {
        return Action::make('configurer')
            ->label($this->uniteActive ? 'Modifier le choix' : 'Configurer ma réservation')
            ->icon($this->uniteActive ? 'heroicon-o-pencil-square' : 'heroicon-o-adjustments-horizontal')
            ->modalHeading('Configurer ma réservation')
            ->modalWidth('xl')
            ->modalSubmitActionLabel('Valider')
            ->schema([
                // Lieu et unité restent dans le formulaire (ils déterminent les options
                // du type de réservation) mais sont masqués et auto-sélectionnés tant
                // qu'il n'y a qu'un seul choix possible — un seul site actif pour
                // l'instant : pas besoin de faire cliquer sur une évidence, et le type
                // de réservation devient le premier choix visible à l'écran.
                Select::make('lieu_id')
                    ->label('Lieu')
                    ->options(fn () => $this->optionsLieux())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('unite_id', null) & $set('type_resa', null))
                    ->default(fn () => $this->lieuActive ?? (count($this->optionsLieux()) === 1 ? array_key_first($this->optionsLieux()) : null))
                    ->hidden(fn () => count($this->optionsLieux()) <= 1),

                Select::make('unite_id')
                    ->label('Unité')
                    ->options(fn (Get $get) => $this->optionsUnites($get('lieu_id')))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('type_resa', null))
                    ->default(fn (Get $get) => $this->uniteActive ?? (count($this->optionsUnites($get('lieu_id'))) === 1 ? array_key_first($this->optionsUnites($get('lieu_id'))) : null))
                    ->hidden(fn (Get $get) => count($this->optionsUnites($get('lieu_id'))) <= 1),

                ToggleButtons::make('type_resa')
                    ->label('Type de réservation')
                    ->options(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->label->all())
                    ->icons(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->icon->all())
                    ->tooltips(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->tooltip->all())
                    // Pas de ->grouped() : ce rendu-là est en w-max (une seule ligne, sans
                    // retour), ça déborde du modal dès qu'il y a plus de 2-3 types (ici
                    // jusqu'à 4 : Salle classique, Simulateur, Ateliers, Ressources).
                    // ->inline() applique flex-wrap à la place : les boutons repassent à
                    // la ligne proprement.
                    ->inline()
                    ->required()
                    ->default($this->typeResa ?: null)
                    ->visible(fn (Get $get) => (bool) $get('unite_id'))
                    ->live(),
            ])
            ->action(function (array $data): void {
                $this->lieuActive  = (int) $data['lieu_id'];
                $this->uniteActive = (int) $data['unite_id'];
                $this->typeResa    = $data['type_resa'];

                session([
                    'resa_nav_lieu'  => $this->lieuActive,
                    'resa_nav_unite' => $this->uniteActive,
                    'resa_nav_type'  => $this->typeResa,
                ]);
            });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getTypeSalleId(): ?int
    {
        return match ($this->typeResa) {
            'drone' => TypeSalle::where('libelle_court', 'like', '%drone%')
                ->orWhere('libelle_long', 'like', '%drone%')
                ->value('id'),
            default => null,
        };
    }

    public function getLieuLabel(): string
    {
        return $this->lieuActive ? (LieuUnite::find($this->lieuActive)?->libelle_court ?? '') : '';
    }

    public function getUniteLabel(): string
    {
        return $this->uniteActive ? (Unite::find($this->uniteActive)?->libelle_court ?? '') : '';
    }

    public function getTypeLabel(): string
    {
        return match ($this->typeResa) {
            'drone'     => 'Simulateur',
            'ressource' => 'Ressources',
            default     => 'Salle classique',
        };
    }

    /**
     * Ordre d'affichage des types de réservation (cartes du dashboard, ToggleButtons de
     * la modal) : Salle classique, Simulateurs, Ateliers (pas encore un type géré — à
     * ajouter ici le jour où il existe côté données), Ressources. Centralisé ici plutôt
     * que livré par l'ordre d'insertion dans choixTypes() : un seul endroit à modifier
     * pour réordonner, et chaque clé absente de $choix est simplement ignorée.
     */
    private const ORDRE_TYPES = ['salle', 'drone', 'atelier', 'ressource'];

    /**
     * Choix du type de réservation : libellé court (affiché sous l'icône dans la carte),
     * icône SVG (heroicon), et infobulle avec le détail — pour rester lisible en carte
     * tout en gardant l'information complète (ex. liste des formations du simulateur).
     *
     * @return \Illuminate\Support\Collection<string, array{label: string, icon: string, tooltip: ?string}>
     */
    public function choixTypes(?int $uniteId): \Illuminate\Support\Collection
    {
        if (! $uniteId) return collect();

        $choix = collect();

        $salles = Salle::where('unite_id', $uniteId)->with('typeSalle')->get();

        if ($salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_CLASSIQUE)->isNotEmpty()) {
            $choix['salle'] = [
                'label'   => 'Salle classique',
                'icon'    => 'heroicon-o-building-office-2',
                'tooltip' => 'Réunion, formation, cours',
            ];
        }

        $sallesSimulateur = $salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_SIMULATEUR);
        if ($sallesSimulateur->isNotEmpty()) {
            // Le libellé ne doit pas présumer du type de simulateur (drone, tir, etc.) :
            // on affiche les formations réellement disponibles dessus (en infobulle), pour
            // que le gestionnaire/utilisateur reconnaisse sa formation (ex. « 20mm »,
            // « MENTOR ») au lieu de se tromper de case faute d'un libellé générique « drone ».
            $formations = Formation::whereHas(
                'sallePostes',
                fn ($q) => $q->whereIn('salle_id', $sallesSimulateur->pluck('id')),
            )->orderBy('ordre')->pluck('libelle_court');

            $choix['drone'] = [
                'label'   => $sallesSimulateur->first()->typeSalle?->libelle_court ?? 'Simulateur',
                'icon'    => 'heroicon-o-cpu-chip',
                'tooltip' => $formations->isNotEmpty() ? $formations->join(', ') : null,
            ];
        }

        if (Ressource::where('unite_id', $uniteId)->where('actif', true)->exists()) {
            $choix['ressource'] = [
                'label'   => 'Ressources matérielles',
                'icon'    => 'heroicon-o-archive-box',
                'tooltip' => 'Matériels et équipements',
            ];
        }

        return $choix
            ->sortBy(function ($c, $type) {
                // array_search(...) ?: ... serait faux ici : la position 0 ("salle",
                // premier de l'ordre) est falsy et tomberait dans le fallback.
                $position = array_search($type, self::ORDRE_TYPES, true);

                return $position !== false ? $position : count(self::ORDRE_TYPES);
            })
            ->map(fn (array $c) => (object) $c);
    }

    private function optionsLieux(): array
    {
        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();

        return LieuUnite::whereIn('id', $lieuIds)
            ->orderBy('ordre')
            ->pluck('libelle_court', 'id')
            ->toArray();
    }

    private function optionsUnites(?int $lieuId): array
    {
        $query = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->orderBy('ordre');

        if ($lieuId) {
            $query->where('lieu_unite_id', $lieuId);
        }

        return $query->pluck('libelle_court', 'id')->toArray();
    }
}
```

### app/Filament/Widgets/MesReservationsWidget.php

```php
<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\RessourceReservation;

class MesReservationsWidget extends Widget
{
    protected string $view = 'resa::filament.widgets.mes-reservations-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    // Affiché avant le calendrier (ReservationWidget, sort=2) : c'est ici que le
    // marin pointe sa présence, ça ne doit pas être caché en bas de page.
    protected static ?int $sort         = 0;

    protected int|string|array $columnSpan = 'full';

    public ?int $filtreStatut = null; // null=toutes, 0=en attente, 1=validée

    /**
     * Limite d'affichage (dashboard, compact) ; 0 = tout afficher (page dédiée).
     * Entier non-nullable : les propriétés Livewire typées `?int` ne restituent pas
     * fiablement `null` après un aller-retour hydrate/dehydrate (ex. suite à un
     * wire:click d'action) — 0 sert de valeur sentinelle "illimité".
     */
    public int $limite = 5;

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getReservations(): Collection
    {
        $userId = auth()->id();

        // user_id = le stagiaire ; created_by = qui a fait la demande — les deux
        // comptent comme "mes réservations", car on peut réserver pour quelqu'un
        // d'autre (ex. gestionnaire qui réserve pour un marin).
        $salles = Reservation::with(['sallePoste.salle', 'creePar', 'validatedBy'])
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhere('created_by', $userId))
            ->get()
            ->map(function (Reservation $r) use ($userId) {
                $formationLabels = Formation::whereIn('id', $r->formations_utilisees ?? [])
                    ->orderBy('ordre')
                    ->pluck('libelle_court')
                    ->join(', ');

                return [
                    'id'       => $r->id,
                    'type'     => 'salle',
                    'libelle'  => $r->libelle_court ?: ($r->sallePoste?->salle?->libelle_court ?? 'Salle'),
                    'formation' => $formationLabels ?: '—',
                    'poste'    => $r->sallePoste?->libelle_court ?? '—',
                    'detail'   => $r->sallePoste?->salle?->libelle_court ?? '—',
                    'debut'    => $r->debut,
                    'fin'      => $r->fin,
                    'valide'   => $r->isValide(),
                    'peut_modifier' => ! $r->isValide(),
                    'concernes' => $r->participantsNoms(),
                    'cree_par'  => $r->creePar ? trim("{$r->creePar->nom} {$r->creePar->prenom}") : null,
                    'validated_by' => $r->validatedBy ? trim("{$r->validatedBy->nom} {$r->validatedBy->prenom}") : null,
                    'validated_at' => $r->validated_at?->format('d/m/Y'),
                    // La pointeuse concerne le stagiaire lui-même (le jour J) : pas celui qui
                    // a juste créé la réservation pour quelqu'un d'autre.
                    'presence'     => $r->presence,
                    'peut_pointer' => $r->user_id === $userId && $r->peutPointer(),
                ];
            });

        $materiels = RessourceReservation::with(['ressource'])
            ->where('user_id', $userId)
            ->get()
            ->map(fn (RessourceReservation $r) => [
                'id'       => $r->id,
                'type'     => 'materiel',
                'libelle'  => $r->libelle ?: ($r->ressource?->libelle_court ?? 'Matériel'),
                'formation' => '—',
                'poste'    => '—',
                'detail'   => $r->ressource?->libelle_court ?? '—',
                'debut'    => $r->debut,
                'fin'      => $r->fin,
                'valide'   => $r->validation === RessourceReservation::VALIDE,
                'peut_modifier' => $r->validation !== RessourceReservation::VALIDE,
                'concernes' => [],
                'cree_par'  => null,
                'validated_by' => null,
                'validated_at' => null,
                // Pointeuse non prévue pour le matériel (hors périmètre actuel).
                'presence'     => null,
                'peut_pointer' => false,
            ]);

        $combinees = $salles->concat($materiels);

        // À venir (dont aujourd'hui) triées par date la plus proche d'abord — sinon une
        // réservation du jour (pointeuse à faire) finit enterrée sous des réservations
        // plus lointaines dans la vue compacte (dashboard, $limite tronqué) et le
        // bouton Présent/Absent n'est jamais visible sans aller sur "Voir tout".
        // Passées ensuite, plus récente d'abord.
        $avenir  = $combinees->filter(fn (array $r) => ! $r['fin']?->isPast())->sortBy('debut');
        $passees = $combinees->filter(fn (array $r) => $r['fin']?->isPast())->sortByDesc('debut');
        $toutes  = $avenir->concat($passees)->values();

        if ($this->filtreStatut !== null) {
            $toutes = $toutes->where('valide', (bool) $this->filtreStatut)->values();
        }

        return $toutes->map(fn (array $r) => [
            ...$r,
            'debut_fmt'  => $r['debut']?->format('d/m/Y H:i'),
            'fin_fmt'    => $r['fin']?->format('H:i'),
            'passee'     => $r['fin']?->isPast() ?? false,
        ]);
    }

    /** Version tronquée pour l'affichage compact (dashboard) ; cf. $limite. */
    public function getReservationsAffichees(): Collection
    {
        $toutes = $this->getReservations();

        return $this->limite > 0 ? $toutes->take($this->limite) : $toutes;
    }

    public function getStats(): array
    {
        $all = $this->getReservations();

        return [
            'total'      => $all->count(),
            'a_venir'    => $all->where('passee', false)->count(),
            'en_attente' => $all->where('valide', false)->where('passee', false)->count(),
        ];
    }

    public function supprimer(string $type, int $id): void
    {
        $userId = auth()->id();

        if ($type === 'materiel') {
            $reservation = RessourceReservation::where('id', $id)->where('user_id', $userId)->first();
        } else {
            $reservation = Reservation::where('id', $id)
                ->where(fn ($q) => $q->where('user_id', $userId)->orWhere('created_by', $userId))
                ->first();
        }

        if (! $reservation) {
            Notification::make()->title('Réservation introuvable.')->danger()->send();
            return;
        }

        $dejaValidee = $type === 'materiel'
            ? $reservation->validation === RessourceReservation::VALIDE
            : $reservation->isValide();

        if ($dejaValidee) {
            Notification::make()->title('Une réservation déjà validée ne peut plus être supprimée.')->danger()->send();
            return;
        }

        $reservation->delete();
        Notification::make()->title('Réservation supprimée')->success()->send();
    }

    public function pointer(int $id, bool $present): void
    {
        $reservation = Reservation::where('id', $id)->where('user_id', auth()->id())->first();

        if (! $reservation || ! $reservation->peutPointer()) {
            Notification::make()->title('Pointage impossible (hors créneau du jour, ou non validée).')->danger()->send();
            return;
        }

        $reservation->pointer($present, auth()->user());

        Notification::make()
            ->title($present ? 'Présence enregistrée' : 'Absence enregistrée')
            ->success()
            ->send();
    }

    public function updatedFiltreStatut(): void {}
}
```

### app/Filament/Widgets/CalendrierWidget.php

```php
<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Livewire\Attributes\On;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarResource;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\FiltresAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaClassiqueAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\VoirReservationAction;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeSalle;

class CalendrierWidget extends CalendarWidget
{
    protected string $view = 'resa::filament.widgets.calendrier-widget';

    protected static bool $isDiscovered = false;

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected bool $eventClickEnabled  = true;
    protected bool $eventDragEnabled   = true;
    protected bool $eventResizeEnabled = true;
    protected bool $dateClickEnabled   = true;
    protected bool $dateSelectEnabled  = true;
    protected ?string $locale          = 'fr';
    protected bool $useFilamentTimezone = true;

    // Navigation
    public string  $etapeNavigation  = 'lieu'; // lieu | unite | type_salle | salle | calendrier
    public ?int    $lieuActive       = null;
    public ?int    $uniteActive      = null;
    public ?int    $typeSalleActive  = null;
    public ?int    $salleActive      = null;
    public bool    $skipTypeSalleNav = false;
    public ?string $modeForce        = null;  // 'classique' | 'simulateur' — forcé depuis le parent

    // Mode "formation" : calendrier transversal (toutes salles) filtré par formation,
    // utilisé par le dashboard du gestionnaire de formation (SuiviFormationsWidget).
    public bool $modeFormationGlobale = false;

    // Etat modal
    public bool    $adminMode      = false;
    public ?int    $recordEnCours  = null;
    public ?string $recordTitre    = null;

    // Etapes Wizard simulateur
    public ?string $etapeDebut        = null;
    public ?string $etapeFin          = null;
    public array   $etapeFormations    = [];
    public ?int    $etapePoste        = null;
    public array   $postesDisponibles = [];

    // Filtres calendrier
    public array $filtrePostes    = [];
    public array $filtreFormations = [];

    // Cache interne (non persisté en Livewire)
    private ?string $_modeActifCache    = null;
    private ?array  $_weekendDatesCache = null;

    /* ── Initialisation ───────────────────────────────────────────────────── */

    public function mount(): void
    {
        if ($this->modeFormationGlobale) {
            // Vue transversale toutes salles, filtrée par formation(s) via $filtreFormations :
            // pas de navigation lieu/unité/salle ; les colonnes (postes) sont résolues dans
            // getResources() à partir des salles qui ont des réservations pour ces formations.
            $this->adminMode  = true;
            $this->etapeNavigation = 'calendrier';

            return;
        }

        $user = auth()->user();
        $this->adminMode = $user?->can('resa::reservation.view') ?? false;

        // Un gestionnaire de salle a aussi accès au mode admin pour sa salle
        if (!$this->adminMode && $user?->can('resa::gestionnaire_salle')) {
            $this->adminMode = true;
        }

        // Un gestionnaire de formation (type de capacité) doit voir les réservations
        // en attente qui utilisent sa formation, pour pouvoir les valider.
        if (!$this->adminMode && $user?->can('resa::gestionnaire_formation')) {
            $this->adminMode = true;
        }

        // Si salleActive est deja defini (injection de tests ou lien direct)
        // on ne resout pas la navigation automatiquement
        if ($this->salleActive !== null) {
            $this->etapeNavigation = 'calendrier';
            $salle = Salle::find($this->salleActive);
            if ($this->typeSalleActive === null) {
                $this->typeSalleActive = $salle?->type_salle_id;
            }
            if ($this->uniteActive === null) {
                $this->uniteActive = $salle?->unite_id;
            }
            if ($this->lieuActive === null && $this->uniteActive) {
                $this->lieuActive = Unite::find($this->uniteActive)?->lieu_unite_id;
            }
            return;
        }

        // Si uniteActive est injecté depuis une page parente, sauter la navigation lieu/unité
        if ($this->uniteActive !== null) {
            if ($this->lieuActive === null) {
                $this->lieuActive = Unite::find($this->uniteActive)?->lieu_unite_id;
            }
            $this->resoudreDepuisUnite();
            return;
        }

        $this->resoudreNavigation();
    }

    /* ── Resolution automatique de la navigation ──────────────────────────── */

    protected function resoudreNavigation(): void
    {
        // Si aucune salle n'a de unite_id → ancien comportement sans lieu/unite
        if (! Salle::whereNotNull('unite_id')->exists()) {
            $this->resoudreDepuisUnite();
            return;
        }

        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();
        $lieux = LieuUnite::whereIn('id', $lieuIds)->orderBy('ordre')->get();

        if ($lieux->count() > 1) {
            $this->etapeNavigation = 'lieu';
            return;
        }

        if ($lieux->count() === 1) {
            $this->lieuActive = $lieux->first()->id;
        }

        $this->resoudreDepuisLieu();
    }

    private function resoudreDepuisLieu(): void
    {
        $sallesAvecUnite = Salle::whereNotNull('unite_id')->pluck('unite_id');
        $query = Unite::whereIn('id', $sallesAvecUnite)->orderBy('ordre');

        if ($this->lieuActive) {
            $query->where('lieu_unite_id', $this->lieuActive);
        }

        $unites = $query->get();

        if ($unites->count() > 1) {
            $this->etapeNavigation = 'unite';
            return;
        }

        if ($unites->count() === 1) {
            $this->uniteActive = $unites->first()->id;
        }

        $this->resoudreDepuisUnite();
    }

    protected function resoudreDepuisUnite(): void
    {
        $salles = $this->getSallesScope()->with('typeSalle')->orderBy('ordre')->get();

        if ($salles->count() === 1) {
            $this->salleActive     = $salles->first()->id;
            $this->typeSalleActive = $salles->first()->type_salle_id;
            $this->etapeNavigation = 'calendrier';
            return;
        }

        // Si typeSalleActive est déjà injecté OU qu'on demande à sauter la navigation
        if ($this->typeSalleActive !== null || $this->skipTypeSalleNav) {
            $sallesFiltrees = $this->typeSalleActive
                ? $salles->where('type_salle_id', $this->typeSalleActive)
                : $salles;

            if ($sallesFiltrees->count() === 1) {
                $this->salleActive     = $sallesFiltrees->first()->id;
                $this->typeSalleActive = $sallesFiltrees->first()->type_salle_id;
            }
            // Toujours aller direct au calendrier (salleActive peut rester null → toutes les salles)
            $this->etapeNavigation = 'calendrier';
            return;
        }

        $typeSalles = TypeSalle::whereIn('id', $salles->pluck('type_salle_id')->unique())->orderBy('ordre')->get();

        if ($typeSalles->count() === 1) {
            $this->typeSalleActive = $typeSalles->first()->id;
            $this->etapeNavigation = 'salle';
            return;
        }

        $this->etapeNavigation = 'type_salle';
    }

    public function getSallesScopePublic(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->getSallesScope();
    }

    private function getSallesScope(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Salle::query();

        if ($this->uniteActive) {
            $query->where('unite_id', $this->uniteActive);
        } elseif ($this->lieuActive) {
            $uniteIds = Unite::where('lieu_unite_id', $this->lieuActive)->pluck('id');
            $query->whereIn('unite_id', $uniteIds);
        }

        // modeForce prend toujours la priorité (contrainte externe du parent)
        if ($this->modeForce !== null) {
            $typeIds = TypeSalle::where('mode', $this->modeForce)->pluck('id');
            $query->whereIn('type_salle_id', $typeIds);
        } elseif ($this->typeSalleActive !== null) {
            $query->where('type_salle_id', $this->typeSalleActive);
        }

        return $query;
    }

    /* ── Sélection lieu ──────────────────────────────────────────────────── */

    public function selectLieu(int $id): void
    {
        $this->lieuActive  = $id;
        $this->uniteActive = null;
        $this->resoudreDepuisLieu();
    }

    /* ── Sélection unité ─────────────────────────────────────────────────── */

    public function selectUnite(int $id): void
    {
        $this->uniteActive = $id;
        $this->resoudreDepuisUnite();
    }

    /* ── Sélection type salle ─────────────────────────────────────────────── */

    public function selectTypeSalle(int $id): void
    {
        $this->_modeActifCache = null;
        $this->typeSalleActive = $id;

        $salles = $this->getSallesScope()->where('type_salle_id', $id)->get();
        if ($salles->count() === 1) {
            $this->salleActive     = $salles->first()->id;
            $this->etapeNavigation = 'calendrier';
        } else {
            $this->etapeNavigation = 'salle';
        }
    }

    /* ── Sélection de salle ───────────────────────────────────────────────── */

    public function selectSalle(int $id): void
    {
        $this->_modeActifCache = null;
        $this->salleActive     = $id;
        $this->typeSalleActive = Salle::find($id)?->type_salle_id ?? $this->typeSalleActive;
        $this->etapeNavigation = 'calendrier';
        $this->filtrePostes    = [];
        $this->filtreFormations = [];
    }

    /* ── Retours ──────────────────────────────────────────────────────────── */

    public function retourSalles(): void
    {
        $this->salleActive     = null;
        $this->etapeNavigation = 'salle';
    }

    public function retourTypeSalles(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->etapeNavigation = 'type_salle';
    }

    public function retourUnites(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->etapeNavigation = 'unite';
    }

    public function retourLieux(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->uniteActive     = null;
        $this->etapeNavigation = 'lieu';
    }

    /* ── Mode de la salle active ──────────────────────────────────────────── */

    public function getModeActif(): string
    {
        if ($this->_modeActifCache !== null) return $this->_modeActifCache;
        if ($this->modeForce !== null) return $this->_modeActifCache = $this->modeForce;
        if (! $this->typeSalleActive) return $this->_modeActifCache = TypeSalle::MODE_SIMULATEUR;
        return $this->_modeActifCache = TypeSalle::find($this->typeSalleActive)?->mode ?? TypeSalle::MODE_SIMULATEUR;
    }

    public function isModeClassique(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_CLASSIQUE;
    }

    public function isModeSimulateur(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_SIMULATEUR;
    }

    public function getCalendarView(): CalendarViewType
    {
        return $this->isModeSimulateur()
            ? CalendarViewType::ResourceTimeGridWeek
            : CalendarViewType::TimeGridWeek;
    }

    public function getHeading(): ?string
    {
        if ($this->salleActive) {
            return 'Calendrier - ' . (Salle::find($this->salleActive)?->libelle_court ?? '');
        }
        return 'Reservations';
    }

    /* ── Droits ───────────────────────────────────────────────────────────── */

    private function canUserReserve(): bool
    {
        return auth()->check();
    }

    /**
     * Vrai administrateur (voit et modifie tout), à distinguer de $adminMode qui vaut
     * aussi true pour un simple gestionnaire de salle/formation — celui-ci voit toutes
     * les réservations (cf. getEvents()) mais ne doit pouvoir modifier/supprimer que
     * les siennes, cf. VoirReservationAction::footerActions().
     */
    public function estAdminComplet(): bool
    {
        $user = auth()->user();

        return ($user?->can('resa::admin') ?? false) || ($user?->can('resa::reservation.view') ?? false);
    }

    /* ── Helpers publics ──────────────────────────────────────────────────── */

    public function validerCreneauPublic(string $debut, string $fin, ?int $excludeId = null): ?string
    {
        $d = \Carbon\Carbon::parse($debut);
        $f = \Carbon\Carbon::parse($fin);

        if ($d->isWeekend()) return 'Reservation impossible le week-end.';
        if ($d->lt(now())) return 'Impossible de reserver un creneau deja passe.';
        if ($f->lte($d)) return 'La date de fin doit etre superieure a la date de debut.';

        $debutMin = $d->hour * 60 + $d->minute;
        $finMin   = $f->hour * 60 + $f->minute;

        if ($debutMin < 8 * 60 || $debutMin >= 18 * 60) return "L'heure de debut doit etre entre 08h00 et 18h00.";
        if ($finMin <= 8 * 60 || $finMin > 18 * 60)     return "L'heure de fin doit etre entre 08h00 et 18h00.";

        return null;
    }

    public function getWeekendDatesPublic(): array
    {
        if ($this->_weekendDatesCache !== null) return $this->_weekendDatesCache;

        $dates = [];
        $day   = now()->startOfDay()->copy();
        $end   = now()->addMonths(6);

        while ($day <= $end) {
            if ($day->isWeekend()) {
                $dates[] = $day->format('Y-m-d');
            }
            $day->addDay();
        }

        return $this->_weekendDatesCache = $dates;
    }

    /* ── Ressources Guava ─────────────────────────────────────────────────── */

    public function getResources(): Collection|array
    {
        if ($this->modeFormationGlobale) {
            return $this->getResourcesFormationGlobale();
        }

        // Mode classique : pas de colonnes
        if ($this->isModeClassique()) return [];

        if (! $this->salleActive) return [];

        return SallePoste::query()
            ->where('salle_id', $this->salleActive)
            ->where('libelle_court', '!=', 'Salle entiere') // exclut le poste fictif
            ->orderBy('ordre')
            ->get();
    }

    /**
     * Colonnes du calendrier transversal "formation" : un poste par poste configuré
     * pour au moins une des formations filtrées (relation SallePoste::formations),
     * avec la salle dans le libellé pour lever l'ambiguïté quand plusieurs salles
     * sont concernées. Basé sur la configuration des postes, pas sur l'historique des
     * réservations : sinon une formation sans réservation n'affiche aucune colonne
     * même si des postes lui sont bien rattachés (voir SallePosteForm — CheckboxList
     * « Formations disponibles sur ce poste »).
     */
    /** IDs des salles dont au moins un poste est configuré pour une des formations filtrées. */
    private function sallesFormationGlobale(): \Illuminate\Support\Collection
    {
        if (empty($this->filtreFormations)) return collect();

        return SallePoste::query()
            ->whereHas('formations', fn ($q) => $q->whereIn('resa_formations.id', $this->filtreFormations))
            ->pluck('salle_id')
            ->unique()
            ->values();
    }

    private function getResourcesFormationGlobale(): Collection
    {
        if (empty($this->filtreFormations)) return collect();

        // Tous les postes des salles concernées, pas seulement ceux configurés pour la
        // formation filtrée : le gestionnaire doit voir l'occupation complète de la
        // salle (autres formations y compris), même s'il ne peut valider/modifier que
        // ce qui relève de la sienne (cf. getEvents() et ReservationPolicy::valider()).
        $postes = SallePoste::query()
            ->where('libelle_court', '!=', 'Salle entiere') // exclut le poste fictif
            ->whereIn('salle_id', $this->sallesFormationGlobale())
            ->with('salle')
            ->orderBy('salle_id')
            ->orderBy('ordre')
            ->get();

        $plusieursSalles = $postes->pluck('salle_id')->unique()->count() > 1;

        return $postes->map(function (SallePoste $poste) use ($plusieursSalles) {
            $titre = $plusieursSalles
                ? trim(($poste->salle?->libelle_court ?? '') . ' — ' . $poste->libelle_court)
                : $poste->libelle_court;

            return \Guava\Calendar\ValueObjects\CalendarResource::make($poste->id)->title($titre ?? '');
        });
    }

    /* ── Evenements ───────────────────────────────────────────────────────── */

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $query = Reservation::query()
            ->with(['sallePoste', 'sallePoste.salle', 'user'])
            ->where('debut', '<=', $info->end)
            ->where('fin',   '>=', $info->start);

        if ($this->adminMode) {
            // tout voir
        } elseif (auth()->check()) {
            $userId = auth()->id();
            $query->where(function ($q) use ($userId) {
                $q->where('validation', Reservation::VALIDE)
                  ->orWhere('user_id', $userId);
            });
        } else {
            $query->where('validation', Reservation::VALIDE);
        }

        if ($this->modeFormationGlobale) {
            // Le gestionnaire voit toute l'occupation des salles concernées par ses
            // formations — y compris les réservations d'autres formations — mais ne
            // peut valider/modifier que ce qui relève de la sienne (ReservationPolicy,
            // VoirReservationAction::footerActions()). Donc pas de filtre JSON par
            // formation ici (cf. plus bas), seulement un scope par salle.
            $query->whereHas('sallePoste', fn ($q) =>
                $q->whereIn('salle_id', $this->sallesFormationGlobale())
            );
        } elseif ($this->salleActive) {
            $query->whereHas('sallePoste', fn ($q) =>
                $q->where('salle_id', $this->salleActive)
            );
        } elseif ($this->uniteActive) {
            $sallesQuery = Salle::where('unite_id', $this->uniteActive);
            if ($this->modeForce !== null) {
                $typeIds = TypeSalle::where('mode', $this->modeForce)->pluck('id');
                $sallesQuery->whereIn('type_salle_id', $typeIds);
            } elseif ($this->typeSalleActive !== null) {
                $sallesQuery->where('type_salle_id', $this->typeSalleActive);
            }
            $salleIds = $sallesQuery->pluck('id');
            $query->whereHas('sallePoste', fn ($q) =>
                $q->whereIn('salle_id', $salleIds)
            );
        }

        if (! empty($this->filtrePostes)) {
            $query->whereIn('salle_poste_id', $this->filtrePostes);
        }

        if (! empty($this->filtreFormations) && ! $this->modeFormationGlobale) {
            $query->where(function ($q) {
                foreach ($this->filtreFormations as $id) {
                    $q->orWhereJsonContains('formations_utilisees', (int) $id)
                      ->orWhereJsonContains('formations_utilisees', (string) $id);
                }
            });
        }

        return $query;
    }

    /* ── Options FullCalendar ─────────────────────────────────────────────── */

    public function getOptions(): array
    {
        $simulateur = $this->isModeSimulateur();

        $dayView      = $simulateur ? 'resourceTimeGridDay'      : 'timeGridDay';
        $weekView     = $simulateur ? 'resourceTimeGridWeek'     : 'timeGridWeek';
        $timelineView = $simulateur ? 'resourceTimelineWeek'     : null;

        $endButtons = $simulateur
            ? "{$dayView},{$weekView},{$timelineView},dayGridMonth,listWeek,listMonth"
            : "{$dayView},{$weekView},dayGridMonth,listWeek,listMonth";

        $options = [
            'nowIndicator' => true,
            'slotDuration' => '00:30:00',
            //lotHeight'   => 60,
            'height'       => 'auto',
            'selectable'   => true,
            'editable'     => auth()->check(),
            'hiddenDays'          => [0, 6],
            'datesAboveResources' => true,
            'headerToolbar' => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => $endButtons,
            ],
            'buttonText' => [
                'today'                   => "Aujourd'hui",
                'timeGridDay'             => 'Jour',
                'timeGridWeek'            => 'Semaine',
                'resourceTimeGridDay'     => 'Jour',
                'resourceTimeGridWeek'    => 'Grille',
                'resourceTimelineWeek'    => 'Timeline',
                'dayGridMonth'            => 'Mois',
                'listWeek'                => 'Liste sem.',
                'listMonth'               => 'Liste mois',
            ],
            'locale'      => 'fr',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '18:00:00',
            'allDaySlot'  => false,
            'scrollTime'  => '08:00:00',
        ];

        if ($simulateur) {
            $options['views'] = [
                'resourceTimelineWeek' => [
                    'slotDuration'      => '01:00:00',
                    'slotMinTime'       => '08:00:00',
                    'slotMaxTime'       => '18:00:00',
                    'scrollTime'        => '08:00:00',
                ],
            ];
        }

        return $options;
    }

    /* ── Header actions ───────────────────────────────────────────────────── */

    public function getHeaderActions(): array
    {
        $actions = [];

        if ($this->etapeNavigation === 'calendrier') {
            $nbSalles    = $this->getSallesScope()->where('type_salle_id', $this->typeSalleActive)->count();
            $nbTypes     = TypeSalle::whereIn('id', $this->getSallesScope()->pluck('type_salle_id')->unique())->count();
            $nbUnites    = $this->uniteActive ? 0 : ($this->lieuActive
                ? Unite::where('lieu_unite_id', $this->lieuActive)->whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->count()
                : 0);
            $nbLieux     = LieuUnite::whereIn('id',
                Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->pluck('lieu_unite_id')->unique()
            )->count();

            if ($nbSalles > 1) {
                // $actions[] = Action::make('retour_salle')
                //     ->label('Changer de salle')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourSalles());
            } elseif ($nbTypes > 1) {
                // $actions[] = Action::make('retour_type_salle')
                //     ->label('Changer de type')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourTypeSalles());
            } elseif ($nbUnites > 1) {
                // $actions[] = Action::make('retour_unite')
                //     ->label('Changer d\'unité')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourUnites());
            } elseif ($nbLieux > 1) {
                // $actions[] = Action::make('retour_lieu')
                //     ->label('Changer de lieu')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourLieux());
            }
        }

        if (! auth()->check()) {
            // Pas de boutons de création — l'utilisateur peut voir mais pas réserver
            // VoirReservationAction reste pour l'eventClick sur les réservations existantes
            $actions[] = VoirReservationAction::make($this);
            return $actions;
        }

        if ($this->etapeNavigation === 'calendrier') {
            // Filtres uniquement en mode simulateur, hors vue transversale : son filtre
            // "Postes" dépend d'une salle active unique (absente ici, plusieurs salles
            // mélangées) et son filtre "Formations" est volontairement ignoré dans ce
            // mode (cf. getEvents()) pour montrer aussi les autres formations — inutile
            // d'afficher un bouton de filtre qui ne filtre rien.
            if ($this->isModeSimulateur() && ! $this->modeFormationGlobale) {
                $actions[] = FiltresAction::make($this);
            }

            // Wizard selon le mode. En vue transversale (dashboard gestionnaire de
            // formation), il n'y a pas de salle active unique au niveau du bouton
            // d'en-tête : on ne l'ajoute donc pas ici (pas de bouton dans la barre
            // d'outils). L'action reste toutefois mountable via nouvelle_resaAction()
            // (cf. plus bas) — ne PAS utiliser ->hidden() ici : une action masquée est
            // aussi considérée comme désactivée par Filament (isDisabled() s'appuie sur
            // isHidden()), ce qui empêche mountAction() de l'ouvrir, y compris depuis un
            // clic sur une colonne du calendrier ou le bouton dédié du dashboard.
            if (! $this->modeFormationGlobale) {
                $actions[] = $this->isModeClassique()
                    ? NouvelleResaClassiqueAction::make($this)
                    : NouvelleResaAction::make($this);
            }

            // Action cachee voir reservation
            $actions[] = VoirReservationAction::make($this);
        }

        return $actions;
    }

    /**
     * Permet à mountAction('nouvelle_resa') de résoudre l'action même quand elle n'est
     * pas enregistrée dans getHeaderActions() (vue transversale modeFormationGlobale,
     * où il n'y a pas de bouton dans la barre d'outils) : Filament retombe sur une
     * méthode {nom}Action() quand le nom n'est pas trouvé dans les actions mises en
     * cache. Hors vue transversale, l'action est déjà en cache via getHeaderActions()
     * donc cette méthode n'est utilisée que comme filet de sécurité.
     */
    public function nouvelle_resaAction(): Action
    {
        return $this->isModeClassique()
            ? NouvelleResaClassiqueAction::make($this)
            : NouvelleResaAction::make($this);
    }

    /* ── DateClick ────────────────────────────────────────────────────────── */

    protected function onDateClick(DateClickInfo $info): void
    {
        if ($this->modeFormationGlobale && ! $this->resoudreSalleDepuisResource($info->resource)) return;
        if (! $this->canUserReserve()) {
            $this->dispatch('openLoginModal');
            return;
        }
        if ($info->date->isWeekend()) return;

        $debut = $info->date->copy()->setTime(max(8, $info->date->hour), 0);
        $fin   = $debut->copy()->addHour();

        $this->etapeDebut        = $debut->toDateTimeString();
        $this->etapeFin          = $fin->toDateTimeString();
        $this->etapeFormations    = [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /**
     * Vue transversale (modeFormationGlobale) : il n'y a pas de salle active unique,
     * chaque colonne du calendrier est un poste rattaché à une salle différente. On
     * déduit donc la salle à utiliser pour la création du poste/colonne cliqué(e) ;
     * sans colonne identifiable (ex. clic hors zone resource), impossible de créer.
     */
    private function resoudreSalleDepuisResource(?CalendarResource $resource): bool
    {
        if (! $resource) return false;

        $poste = SallePoste::find($resource->getId());
        if (! $poste || ! $poste->salle_id) return false;

        $this->salleActive     = $poste->salle_id;
        $this->typeSalleActive = $poste->salle?->type_salle_id;
        $this->_modeActifCache = null;

        return true;
    }

    /**
     * Déclenché par SuiviFormationsWidget::ouvrirNouvelleReservation() : bouton explicite
     * côté gestionnaire pour créer une réservation sans dépendre d'un clic sur une case
     * du calendrier transversal (qui n'est pas toujours évident à trouver/cliquer).
     */
    #[On('resa-ouvrir-nouvelle-reservation')]
    public function ouvrirNouvelleReservationDepuisBouton(int $salleId, ?int $formationId = null): void
    {
        if (! $this->modeFormationGlobale) return;

        $salle = Salle::find($salleId);
        if (! $salle) return;

        $this->salleActive     = $salle->id;
        $this->typeSalleActive = $salle->type_salle_id;
        $this->_modeActifCache = null;

        $debut = now()->addDay()->setTime(9, 0);
        if ($debut->isWeekend()) $debut = $debut->next(\Carbon\Carbon::MONDAY)->setTime(9, 0);

        $this->etapeDebut        = $debut->toDateTimeString();
        $this->etapeFin          = $debut->copy()->addHour()->toDateTimeString();
        $this->etapeFormations    = $formationId ? [$formationId] : [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /* ── DateSelect ───────────────────────────────────────────────────────── */

    protected function onDateSelect(DateSelectInfo $info): void
    {
        if ($this->modeFormationGlobale && ! $this->resoudreSalleDepuisResource($info->resource)) return;
        if (! $this->canUserReserve()) {
            $this->dispatch('openLoginModal');
            return;
        }
        if ($info->start->isWeekend() || $info->end->isWeekend()) return;

        $this->etapeDebut        = $info->start->toDateTimeString();
        $this->etapeFin          = $info->end->toDateTimeString();
        $this->etapeFormations    = [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /* ── EventClick ───────────────────────────────────────────────────────── */

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        $this->recordEnCours = $info->record->id;
        $this->recordTitre   = $info->record->libelle_court;
        $this->mountAction('voirReservation');
    }

    /* ── Drag & Drop ──────────────────────────────────────────────────────── */

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! auth()->check()) return false;

        $record = $info->record;

        if ($record->isValide() && ! $this->adminMode) return false;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;

        $debut = $info->event->getStart();
        $fin   = $info->event->getEnd();

        if ($this->validerCreneauPublic($debut, $fin, $record->id)) return false;

        $record->update(['debut' => $debut, 'fin' => $fin]);
        return true;
    }

    /* ── Resize ───────────────────────────────────────────────────────────── */

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! auth()->check()) return false;

        $record = $info->record;

        if ($record->isValide() && ! $this->adminMode) return false;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;

        $fin = $info->event->getEnd();

        if ($this->validerCreneauPublic($record->debut->toDateTimeString(), $fin, $record->id)) return false;

        $record->update(['fin' => $fin]);
        return true;
    }
}
```

### app/Filament/Resources/TypePostes/TypePosteResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypePostes;

use Modules\Resa\Models\TypePoste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypePostes\Pages\CreateTypePoste;
use Modules\Resa\Filament\Resources\TypePostes\Pages\EditTypePoste;
use Modules\Resa\Filament\Resources\TypePostes\Pages\ListTypePostes;
use Modules\Resa\Filament\Resources\TypePostes\Schemas\TypePosteForm;
use Modules\Resa\Filament\Resources\TypePostes\Tables\TypePostesTable;

class TypePosteResource extends Resource
{
    protected static ?string $model = TypePoste::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 6;

    //protected static ?string $recordTitleAttribute = 'TypePoste';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypePosteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypePostesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypePostes::route('/'),
            'create' => CreateTypePoste::route('/create'),
            'edit' => EditTypePoste::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/TypeSalles/TypeSalleResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypeSalles;

use Modules\Resa\Models\TypeSalle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\CreateTypeSalle;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\EditTypeSalle;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\ListTypeSalles;
use Modules\Resa\Filament\Resources\TypeSalles\Schemas\TypeSalleForm;
use Modules\Resa\Filament\Resources\TypeSalles\Tables\TypeSallesTable;

class TypeSalleResource extends Resource
{
    protected static ?string $model = TypeSalle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 4;

    //protected static ?string $recordTitleAttribute = 'TypeSalle';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypeSalleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeSallesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypeSalles::route('/'),
            'create' => CreateTypeSalle::route('/create'),
            'edit' => EditTypeSalle::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/TypeCapacites/TypeCapaciteResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites;

use  Modules\Resa\Models\Formation;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\CreateTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\EditTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\ListTypeCapacites;
use Modules\Resa\Filament\Resources\TypeCapacites\Schemas\TypeCapaciteForm;
use Modules\Resa\Filament\Resources\TypeCapacites\Tables\TypeCapacitesTable;

class TypeCapaciteResource extends Resource
{
    protected static ?string $model = Formation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 7;

    //protected static ?string $recordTitleAttribute = 'TypeCapacite';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return TypeCapaciteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeCapacitesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user?->can('resa::admin') || $user?->can('resa::formation.index')) {
            return $query;
        }

        // Gestionnaire de formation : uniquement les formations qu'il gère.
        return $query->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user?->id));
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTypeCapacites::route('/'),
            'create' => CreateTypeCapacite::route('/create'),
            'edit' => EditTypeCapacite::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/SallePostes/SallePosteResource.php

```php
<?php

namespace Modules\Resa\Filament\Resources\SallePostes;

use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Resa\Filament\Resources\SallePostes\Pages\CreateSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Pages\EditSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Pages\ListSallePostes;
use Modules\Resa\Filament\Resources\SallePostes\Pages\ViewSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Schemas\SallePosteForm;
use Modules\Resa\Filament\Resources\SallePostes\Schemas\SallePosteInfolist;
use Modules\Resa\Filament\Resources\SallePostes\Tables\SallePostesTable;

class SallePosteResource extends Resource
{
    protected static ?string $model = SallePoste::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?int                    $navigationSort  = 5;

  //    protected static ?string $recordTitleAttribute = 'SallePoste';
    // protected static ?string $recordTitleAttribute = 'libelle_court';

    public static function form(Schema $schema): Schema
    {
        return SallePosteForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SallePosteInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SallePostesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->can('resa::admin')
            || $user?->can('resa::gestionnaire_salle');
    }

    public static function getEloquentQuery(): Builder
    {
        $user  = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user?->can('resa::admin')) {
            return $query;
        }

        // Gestionnaire : uniquement les postes de ses salles (directement, ou via les
        // formations qu'il gère pour les salles simulateur — cf. Salle::idsGereesPar).
        $salleIds = Salle::idsGereesPar($user);

        return $query->whereIn('salle_id', $salleIds);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSallePostes::route('/'),
            'create' => CreateSallePoste::route('/create'),
            'view' => ViewSallePoste::route('/{record}'),
            'edit' => EditSallePoste::route('/{record}/edit'),
        ];
    }
}
```

### app/Filament/Resources/RessourceCategorieResource/Pages/CreateRessourceCategorie.php

```php
<?php

namespace Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\RessourceCategorieResource;
use Spatie\Permission\Models\Permission;

class CreateRessourceCategorie extends CreateRecord
{
    protected static string $resource = RessourceCategorieResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_ressource', 'guard_name' => 'web']
        );

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
```

### app/Filament/Resources/RessourceCategorieResource/Pages/EditRessourceCategorie.php

```php
<?php

namespace Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\RessourceCategorieResource;
use Modules\Resa\Models\RessourceCategorie;
use Spatie\Permission\Models\Permission;

class EditRessourceCategorie extends EditRecord
{
    protected static string $resource = RessourceCategorieResource::class;

    /** Gestionnaires de la catégorie avant sauvegarde, pour détecter qui a été retiré. */
    private array $anciensGestionnairesIds = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->anciensGestionnairesIds = $this->record->gestionnaires()->pluck('user_id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_ressource', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneCategorie = RessourceCategorie::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneCategorie) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
```

### app/Filament/Resources/TypeCapacites/Pages/CreateTypeCapacite.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;
use Spatie\Permission\Models\Permission;

class CreateTypeCapacite extends CreateRecord
{
    protected static string $resource = TypeCapaciteResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_formation', 'guard_name' => 'web']
        );

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
```

### app/Filament/Resources/TypeCapacites/Pages/EditTypeCapacite.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Pages;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\TypeCapacites\TypeCapaciteResource;
use Modules\Resa\Models\Formation;
use Spatie\Permission\Models\Permission;

class EditTypeCapacite extends EditRecord
{
    protected static string $resource = TypeCapaciteResource::class;

    /** Gestionnaires de la formation avant sauvegarde, pour détecter qui a été retiré. */
    private array $anciensGestionnairesIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->anciensGestionnairesIds = $this->record->gestionnaires()->pluck('user_id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_formation', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneFormation = Formation::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneFormation) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
```

### app/Filament/Resources/TypeCapacites/Schemas/TypeCapaciteForm.php

```php
<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Resa\Filament\Components\EmailListField;

class TypeCapaciteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations générales')
                ->description('Le nom et les réglages de base de la formation.')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    TextInput::make('libelle_court')
                        ->label('Nom de la formation')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Textarea::make('libelle_long')
                        ->label('Description')
                        ->rows(2)
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        TextInput::make('ordre')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('nb_users_par_poste')
                            ->label('Stagiaires par poste')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('Nombre max partageant un même poste'),

                        Select::make('couleur')
                            ->label('Couleur')
                            ->options([
                                'primary'   => 'Primary',
                                'secondary' => 'Secondary',
                                'success'   => 'Success',
                                'danger'    => 'Danger',
                                'warning'   => 'Warning',
                                'info'      => 'Info',
                                'gray'      => 'Gray',
                            ])
                            ->native(false),
                    ]),
                ]),

            Section::make('Gestion')
                ->description('Qui gère cette formation, et qui est prévenu par email.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Select::make('gestionnaires')
                        ->label('Gestionnaires')
                        ->helperText('Ces utilisateurs peuvent gérer cette formation et valider ses réservations.')
                        ->multiple()
                        ->relationship('gestionnaires', 'display_name')
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),

                    EmailListField::make('email'),
                ]),

            Section::make('Validation des réservations')
                ->description('Par défaut, une réservation reste en attente jusqu\'à ce qu\'un gestionnaire la valide. Vous pouvez activer la validation automatique — globalement, ou différemment selon des sous-catégories (ex. « Entraînement » auto-validé, « Examen » validé par un instructeur).')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Repeater::make('sousCategories')
                        ->relationship('sousCategories')
                        ->label('Sous-catégories')
                        ->helperText('Optionnel : laissez vide pour un réglage unique ci-dessous. Avec 2 sous-catégories ou plus, le réservataire devra en choisir une au moment de la réservation.')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('libelle_court')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Toggle::make('validation_auto')
                                    ->label('Validation automatique')
                                    ->live()
                                    ->columnSpan(2)
                                    ->inline(false),
                            ]),

                            Textarea::make('notice')
                                ->label('Consignes envoyées dans l\'email de confirmation')
                                ->helperText('Ex. où récupérer les clés, où se trouve la salle…')
                                ->rows(2)
                                ->visible(fn ($get) => (bool) $get('validation_auto'))
                                ->columnSpanFull(),
                        ])
                        ->orderColumn('ordre')
                        // Filament pré-remplit tout Repeater avec 1 ligne vide par défaut
                        // (Repeater::setUp() appelle defaultItems(1)) — sans ce ->defaultItems(0),
                        // une formation "simple" (0 sous-catégorie, cas normal) partait déjà
                        // avec une ligne invalide ("Nom" vide, required), bloquant toute
                        // création à la validation alors que l'utilisateur n'a jamais touché
                        // au Repeater.
                        ->defaultItems(0)
                        ->addActionLabel('Ajouter une sous-catégorie')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['libelle_court'] ?? null)
                        ->columnSpanFull(),

                    Grid::make(1)
                        ->schema([
                            Toggle::make('validation_auto')
                                ->label('Validation automatique')
                                ->helperText('Toute réservation sur cette formation est validée dès sa création.')
                                ->live(),

                            Textarea::make('notice')
                                ->label('Consignes envoyées dans l\'email de confirmation')
                                ->rows(3)
                                ->visible(fn ($get) => (bool) $get('validation_auto')),
                        ])
                        // N'a de sens que sans sous-catégorie (ou une seule) : au-delà, le
                        // réglage de chaque sous-catégorie prend le dessus (cf.
                        // Formation::validationAuto()) et afficher les deux prêterait à
                        // confusion.
                        ->visible(fn ($get) => count(array_filter($get('sousCategories') ?? [])) < 2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
```

### app/Filament/Resources/ReservationResource/Pages/CreateReservation.php

```php
<?php

namespace Modules\Resa\Filament\Resources\ReservationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\ReservationResource;
use Modules\Resa\Models\Reservation;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Résoudre participants → user_id + data.users
        $participants = collect($data['participants'] ?? [])
            ->pluck('user_id')->filter()->unique()->values()->toArray();

        if (! empty($participants)) {
            $data['user_id'] = $participants[0];
            $data['data']    = array_merge($data['data'] ?? [], ['users' => $participants]);
        } elseif (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        unset($data['participants']);

        $data['created_by'] = auth()->id();

        $user = auth()->user();

        $valideDirectement = $user?->can('resa::admin');

        // Gestionnaire : valide directement si la salle lui est assignée (directement,
        // ou via la formation pour les salles simulateur — cf. Salle::isGestionnaire()).
        if (
            !$valideDirectement
            && ($user?->can('resa::gestionnaire_salle') || $user?->can('resa::gestionnaire_formation'))
            && isset($data['salle_poste_id'])
        ) {
            $salle = \Modules\Resa\Models\SallePoste::find($data['salle_poste_id'])?->salle;
            $valideDirectement = $salle && $salle->isGestionnaire($user);
        }

        if ($valideDirectement) {
            $data['validation']   = Reservation::VALIDE;
            $data['validated_by'] = auth()->id();
            $data['validated_at'] = now();
        } else {
            $data['validation'] = Reservation::EN_ATTENTE;
        }

        return $data;
    }
}
```

### app/Filament/Resources/SalleResource/Pages/EditSalle.php

```php
<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Resa\Filament\Resources\SalleResource;
use Modules\Resa\Models\Salle;
use Spatie\Permission\Models\Permission;

class EditSalle extends EditRecord
{
    protected static string $resource = SalleResource::class;

    /** Gestionnaires de la salle avant sauvegarde, pour détecter qui a été retiré. */
    private array $anciensGestionnairesIds = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->anciensGestionnairesIds = $this->record->gestionnaires()->pluck('user_id')->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']
        );

        $nouveauxIds = $this->record->gestionnaires()->pluck('user_id')->all();

        User::whereIn('id', $nouveauxIds)->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );

        $retiresIds = array_diff($this->anciensGestionnairesIds, $nouveauxIds);
        foreach (User::whereIn('id', $retiresIds)->get() as $user) {
            $gereEncoreUneSalle = Salle::whereHas(
                'gestionnaires',
                fn ($q) => $q->where('user_id', $user->id)
            )->exists();

            if (!$gereEncoreUneSalle) {
                $user->revokePermissionTo($permission);
            }
        }
    }
}
```

### app/Filament/Resources/SalleResource/Pages/CreateSalle.php

```php
<?php

namespace Modules\Resa\Filament\Resources\SalleResource\Pages;

use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\SalleResource;
use Spatie\Permission\Models\Permission;

class CreateSalle extends CreateRecord
{
    protected static string $resource = SalleResource::class;

    protected function afterCreate(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']
        );

        User::whereIn('id', $this->record->gestionnaires()->pluck('user_id'))->get()->each(
            fn ($user) => $user->givePermissionTo($permission)
        );
    }
}
```

### app/Filament/Widgets/CalendrierWidget/Actions/NouvelleResaAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use App\Events\UnUtilisateurLocalDoitEtreCreeEvent;
use App\Models\AnnuaireUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Validation\ValidationException;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\FormationSousCategorie;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Services\PosteDisponibiliteService;

class NouvelleResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('nouvelle_resa')
            ->label('Nouvelle reservation')
            ->color('primary')
            ->icon('heroicon-o-plus')
            ->mountUsing(function ($form) use ($widget) {
                $form->fill([
                    'debut'             => $widget->etapeDebut ?? now()->setTime(8, 0)->toDateTimeString(),
                    'fin'               => $widget->etapeFin   ?? now()->setTime(9, 0)->toDateTimeString(),
                    'formation_id'  => $widget->etapeFormations[0] ?? null,
                    'postes_attribution' => [],
                ]);
            })
            ->schema([
                Wizard::make([
                    self::etape1($widget),
                    self::etape2($widget),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString(
                    '<button type="submit" class="fi-btn fi-color-custom fi-btn-color-custom fi-btn-size-md fi-btn-primary px-4 py-2 rounded-lg bg-primary-600 text-white font-semibold shadow-sm hover:bg-primary-500">'
                    . 'Confirmer la réservation'
                    . '</button>'
                ))
                ->columnSpanFull(),
            ])
            ->modalHeading('Nouvelle reservation')
            ->modalSubmitAction(false)
            ->action(function (array $data) use ($widget): void {
                $postes = $data['postes_attribution'] ?? [];
                $formationId = $widget->etapeFormations[0] ?? null;
                $formation = $formationId ? Formation::find($formationId) : null;

                // 0 ou 1 sous-catégorie : la formation se comporte "comme avant" (ses
                // propres validation_auto/notice). 2+ : celle choisie en étape 1 prend
                // le dessus — ex. "FPV" > "Entraînement" (auto-validé) vs "Examen"
                // (validation_auto false, reste en attente d'un gestionnaire).
                $sousCategories = $formation?->sousCategories ?? collect();
                $sousCategorie = match (true) {
                    $sousCategories->count() === 1 => $sousCategories->first(),
                    $sousCategories->count() >= 2  => FormationSousCategorie::find($data['sous_categorie_id'] ?? null),
                    default                         => null,
                };

                $postesSelectionnes = array_filter($postes, fn ($l) => ! empty($l['selectionne']) && ! empty($l['user_1_id']));

                if (empty($postesSelectionnes)) {
                    Notification::make()
                        ->title('Veuillez sélectionner au moins un poste et attribuer un stagiaire.')
                        ->danger()
                        ->send();
                    return;
                }

                if (! $widget->adminMode && \Carbon\Carbon::parse($widget->etapeDebut)->lt(now())) {
                    Notification::make()->title('Impossible de reserver un creneau deja passe.')->danger()->send();
                    return;
                }

                $valideDirectement = $widget->adminMode || (bool) $formation?->validationAuto($sousCategorie);

                foreach ($postesSelectionnes as $ligne) {

                    Reservation::create([
                        'user_id'             => $ligne['user_1_id'],
                        'created_by'          => auth()->id(),
                        'salle_poste_id'      => $ligne['poste_id'],
                        'libelle_court'       => $formation?->libelle_court ?? '',
                        'debut'               => $widget->etapeDebut,
                        'fin'                 => $widget->etapeFin,
                        'formations_utilisees' => $formationId ? [(int) $formationId] : [],
                        'sous_categorie_id'   => $sousCategorie?->id,
                        'validation'          => $valideDirectement ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                        'validated_by'        => $valideDirectement ? auth()->id() : null,
                        'validated_at'        => $valideDirectement ? now()         : null,
                        'data'                => [
                            'users'            => array_values(array_filter([$ligne['user_1_id'], $ligne['user_2_id'] ?? null])),
                            'formation_id' => $formationId,
                        ],
                    ]);
                }

                $widget->etapeDebut        = null;
                $widget->etapeFin          = null;
                $widget->etapeFormations    = [];
                $widget->postesDisponibles = [];

                $widget->refreshRecords();

                Notification::make()
                    ->title('Demande envoyee')
                    ->body($widget->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                    ->success()
                    ->send();
            });
    }

    private static function etape1(object $widget): Step
    {
        return Step::make('Creneau & Formation')
            ->icon('heroicon-o-calendar')
            ->schema([
                Grid::make(2)->schema([
                    DateTimePicker::make('debut')
                        ->label('Date debut')
                        ->required()
                        ->seconds(false)
                        ->minDate(now()->startOfDay())
                        ->hoursStep(1)
                        ->minutesStep(15)
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),

                    DateTimePicker::make('fin')
                        ->label('Date fin')
                        ->required()
                        ->seconds(false)
                        ->after('debut')
                        ->minDate(now()->startOfDay())
                        ->hoursStep(1)
                        ->minutesStep(15)
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),
                ]),

                Select::make('formation_id')
                    ->label('Type de formation')
                    ->required()
                    ->options(Formation::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('sous_categorie_id', null))
                    ->columnSpanFull(),

                // Affiché seulement s'il y a un vrai choix à faire (2+ sous-catégories) :
                // avec 0 ou 1, la formation garde son propre réglage de validation.
                Select::make('sous_categorie_id')
                    ->label('Sous-catégorie')
                    ->required()
                    ->options(fn ($get) => Formation::find($get('formation_id'))?->sousCategories->pluck('libelle_court', 'id') ?? [])
                    ->visible(fn ($get) => (Formation::find($get('formation_id'))?->sousCategories->count() ?? 0) >= 2)
                    ->columnSpanFull(),
            ])
            ->afterValidation(function ($get, $set) use ($widget) {
                $erreur = $widget->validerCreneauPublic($get('debut'), $get('fin'));
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    throw ValidationException::withMessages(['debut' => $erreur]);
                }

                $formationId = $get('formation_id');

                $service = app(PosteDisponibiliteService::class);
                $postes = $service->postesDisponibles(
                    salleId:      $widget->salleActive,
                    debut:        new \DateTime($get('debut')),
                    fin:          new \DateTime($get('fin')),
                    formationIds: [$formationId],
                );

                $widget->etapeDebut      = $get('debut');
                $widget->etapeFin        = $get('fin');
                $widget->etapeFormations = [$formationId];

                $widget->postesDisponibles = $postes->mapWithKeys(function ($item) {
                    return [$item['poste']->id => $item['poste']->libelle_court];
                })->toArray();

                $data = $postes->map(function ($item) {
                    return [
                        'poste_id'       => $item['poste']->id,
                        'poste_libelle'  => $item['poste']->libelle_court,
                        'selectionne'    => false,
                        'ajouter_user_2' => false,
                        'user_1_id'      => null,
                        'user_2_id'      => null,
                    ];
                })->values()->toArray();

                $set('postes_attribution', $data);
            });
    }

    private static function etape2(object $widget): Step
    {
        return Step::make('Attribution des postes')
            ->icon('heroicon-o-computer-desktop')
            ->schema(function () use ($widget): array {
                $formation       = isset($widget->etapeFormations[0]) ? Formation::find($widget->etapeFormations[0]) : null;
                $nbUsersParPoste = $formation?->nb_users_par_poste ?? 1;
                $isIntradef      = config('skeletor.reseau_de_deploiement') === 'intradef';
                $showAnnuaire    = $isIntradef || config('skeletor.reseau_de_deploiement') === 'sic21';

                return [
                    TextEntry::make('recap_creneau')
                        ->label('Creneau')
                        ->getStateUsing(function () use ($widget) {
                            if (! $widget->etapeDebut) return '—';
                            return \Carbon\Carbon::parse($widget->etapeDebut)->format('d/m/Y H:i')
                                . ' — '
                                . \Carbon\Carbon::parse($widget->etapeFin)->format('H:i');
                        }),

                    Repeater::make('postes_attribution')
                        ->label('Postes disponibles')
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->schema([
                            Hidden::make('poste_id'),

                            Toggle::make('selectionne')
                                ->label(fn ($get) => $get('poste_libelle') ?? 'Poste')
                                ->live()
                                ->default(false)
                                ->columnSpanFull(),

                            // ── Stagiaire 1 ───────────────────────────────
                            Select::make('user_1_id')
                                ->label('Stagiaire 1')
                                ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                                ->searchable()
                                ->nullable()
                                ->visible(fn ($get) => (bool) $get('selectionne'))
                                ->columnSpanFull(),

                            // ── Bouton annuaire pour user 1 ───────────────
                            ...($showAnnuaire ? [
                                \Filament\Schemas\Components\Actions::make([
                                    self::annuaireAction('user_1_id'),
                                ])->visible(fn ($get) => (bool) $get('selectionne')),
                            ] : []),

                            // ── Toggle + Stagiaire 2 ──────────────────────
                            Toggle::make('ajouter_user_2')
                                ->label('Ajouter un 2ème stagiaire')
                                ->live()
                                ->default(false)
                                ->visible(fn ($get) => (bool) $get('selectionne') && $nbUsersParPoste >= 2)
                                ->columnSpanFull(),

                            Select::make('user_2_id')
                                ->label('Stagiaire 2')
                                ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                                ->searchable()
                                ->nullable()
                                ->visible(fn ($get) => (bool) $get('selectionne') && (bool) $get('ajouter_user_2') && $nbUsersParPoste >= 2)
                                ->columnSpanFull(),

                            ...($showAnnuaire ? [
                                \Filament\Schemas\Components\Actions::make([
                                    self::annuaireAction('user_2_id'),
                                ])->visible(fn ($get) => (bool) $get('selectionne') && (bool) $get('ajouter_user_2') && $nbUsersParPoste >= 2),
                            ] : []),
                        ])
                        ->columnSpanFull(),
                ];
            });
    }

    private static function annuaireAction(string $targetField): Action
    {
        return Action::make('annuaire_' . $targetField)
            ->label('Rechercher dans l\'annuaire')
            ->icon('heroicon-o-magnifying-glass')
            ->color('gray')
            ->modalHeading('Recherche annuaire')
            ->schema([
                Wizard::make([
                    Step::make('Critères')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('ann_nom')->label('Nom'),
                                TextInput::make('ann_prenom')->label('Prénom'),
                                TextInput::make('ann_email')->label('Email'),
                                TextInput::make('ann_unite')->label('Unité'),
                            ]),
                        ])
                        ->afterValidation(function ($get, $set) {
                            $ok = AnnuaireUser::setQuery([
                                'nom'    => $get('ann_nom')    ?? '',
                                'prenom' => $get('ann_prenom') ?? '',
                                'email'  => $get('ann_email')  ?? '',
                                'unite'  => $get('ann_unite')  ?? '',
                            ]);

                            if (! $ok) {
                                Notification::make()
                                    ->title('Annuaire inaccessible')
                                    ->body('La connexion à l\'annuaire a expiré. Réessayez dans quelques instants.')
                                    ->danger()
                                    ->send();
                                throw ValidationException::withMessages(['ann_nom' => 'Annuaire inaccessible.']);
                            }

                            $options = AnnuaireUser::query()->get()
                                ->mapWithKeys(fn ($u) => [
                                    $u->nid => trim("{$u->nom} {$u->prenom}") . ($u->unites ? " ({$u->unites})" : ''),
                                ])
                                ->toArray();

                            $set('ann_resultats', $options);
                            $set('ann_nid', null);
                            $set('ann_data', null);
                        }),

                    Step::make('Résultats')
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Select::make('ann_nid')
                                ->label('Sélectionner un utilisateur')
                                ->options(fn ($get) => $get('ann_resultats') ?? [])
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (! $state) return;
                                    $user = AnnuaireUser::query()->get()->firstWhere('nid', $state);
                                    if ($user) {
                                        $set('ann_data', json_encode([
                                            'nom'    => $user->nom,
                                            'prenom' => $user->prenom ?? $user->prenomusuel ?? '',
                                            'email'  => $user->email,
                                        ]));
                                    }
                                }),
                            Hidden::make('ann_resultats'),
                            Hidden::make('ann_data'),
                        ]),
                ])->columnSpanFull(),
            ])
            ->action(function (array $data) {
                $raw = $data['ann_data'] ?? null;
                if (! $raw) {
                    Notification::make()->title('Aucun utilisateur sélectionné.')->warning()->send();
                    return;
                }

                $info = json_decode($raw, true);

                $existing = User::where('email', $info['email'])->first();
                if ($existing) {
                    Notification::make()
                        ->title('Utilisateur déjà dans la liste')
                        ->body(trim("{$info['nom']} {$info['prenom']}") . ' est déjà disponible dans le sélecteur.')
                        ->info()->send();
                    return;
                }

                UnUtilisateurLocalDoitEtreCreeEvent::dispatch($info, []);

                Notification::make()
                    ->title('Utilisateur ajouté')
                    ->body(trim("{$info['nom']} {$info['prenom']}") . ' a été créé. Sélectionnez-le dans la liste.')
                    ->success()->send();
            });
    }
}
```

### app/Filament/Widgets/CalendrierWidget/Actions/NouvelleResaClassiqueAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use App\Models\AnnuaireUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Validation\ValidationException;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;

class NouvelleResaClassiqueAction
{
    public static function make(object $widget): Action
    {
        return Action::make('nouvelle_resa')
            ->label('Nouvelle reservation')
            ->color('primary')
            ->icon('heroicon-o-plus')
            ->mountUsing(function ($form) use ($widget) {
                $form->fill([
                    'debut'         => $widget->etapeDebut ?? now()->setTime(8, 0)->toDateTimeString(),
                    'fin'           => $widget->etapeFin   ?? now()->setTime(9, 0)->toDateTimeString(),
                    'participants'  => [['user_id' => auth()->id()]],
                    'libelle_court' => null,
                    'libelle_long'  => null,
                ]);
            })
            ->schema([
                Wizard::make([
                    self::etape1($widget),
                    self::etape2($widget),
                ])->columnSpanFull(),
            ])
            ->modalHeading('Nouvelle reservation')
            ->modalSubmitActionLabel('Confirmer la reservation')
            ->action(function (array $data) use ($widget): void {
                $debut = $data['debut'];
                $fin   = $data['fin'];

                $erreur = $widget->validerCreneauPublic($debut, $fin);
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    return;
                }

                // Recupere le poste fictif "Salle entiere" de la salle active
                $poste = SallePoste::where('salle_id', $widget->salleActive)
                    ->where('libelle_court', 'Salle entiere')
                    ->first();

                if (! $poste) {
                    Notification::make()->title('Configuration incorrecte. Poste fictif introuvable.')->danger()->send();
                    return;
                }

                // Verifie que la salle est libre sur ce creneau
                if (! $poste->isDisponible(new \DateTime($debut), new \DateTime($fin))) {
                    Notification::make()
                        ->title('Salle indisponible')
                        ->body('La salle est deja reservee sur ce creneau.')
                        ->danger()->send();
                    return;
                }

                $participants = collect($data['participants'] ?? [])
                    ->pluck('user_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                $userId = $participants[0] ?? auth()->id();

                Reservation::create([
                    'user_id'             => $userId,
                    'created_by'          => auth()->id(),
                    'salle_poste_id'      => $poste->id,
                    'libelle_court'       => $data['libelle_court'],
                    'libelle_long'        => $data['libelle_long'] ?? null,
                    'debut'               => $debut,
                    'fin'                 => $fin,
                    'formations_utilisees' => [],
                    'validation'          => $widget->adminMode ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                    'validated_by'        => $widget->adminMode ? auth()->id() : null,
                    'validated_at'        => $widget->adminMode ? now()         : null,
                    'data'                => ['users' => $participants],
                ]);

                $widget->etapeDebut = null;
                $widget->etapeFin   = null;
                $widget->refreshRecords();

                Notification::make()
                    ->title('Demande envoyee')
                    ->body($widget->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                    ->success()->send();
            });
    }

    private static function etape1(object $widget): Step
    {
        return Step::make('Creneau')
            ->icon('heroicon-o-calendar')
            ->schema([
                Grid::make(2)->schema([
                    DateTimePicker::make('debut')
                        ->label('Date debut')
                        ->required()
                        ->seconds(false)
                        ->minDate(now()->startOfDay())
                        ->hoursStep(1)
                        ->minutesStep(15)
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),

                    DateTimePicker::make('fin')
                        ->label('Date fin')
                        ->required()
                        ->seconds(false)
                        ->after('debut')
                        ->minDate(now()->startOfDay())
                        ->hoursStep(1)
                        ->minutesStep(15)
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),
                ]),

                // Info capacite de la salle
                TextEntry::make('capacite_info')
                    ->label('Capacite de la salle')
                    ->getStateUsing(function () use ($widget) {
                        $salle = Salle::find($widget->salleActive);
                        return $salle?->capacite ? $salle->capacite . ' personnes maximum' : '—';
                    })
                    ->columnSpanFull(),
            ])
            ->afterValidation(function ($get) use ($widget) {
                $erreur = $widget->validerCreneauPublic($get('debut'), $get('fin'));
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'debut' => $erreur,
                    ]);
                }

                // Verifie disponibilite salle
                $poste = SallePoste::where('salle_id', $widget->salleActive)
                    ->where('libelle_court', 'Salle entiere')
                    ->first();

                if ($poste && ! $poste->isDisponible(new \DateTime($get('debut')), new \DateTime($get('fin')))) {
                    $msg = 'La salle est deja reservee sur ce creneau.';
                    Notification::make()->title($msg)->danger()->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'debut' => $msg,
                    ]);
                }

                $widget->etapeDebut = $get('debut');
                $widget->etapeFin   = $get('fin');
            });
    }

    private static function etape2(object $widget): Step
    {
        $isIntradef  = config('skeletor.reseau_de_deploiement') === 'intradef';
        $capacite    = Salle::find($widget->salleActive)?->capacite ?? 1;
        $capacite    = max(1, (int) $capacite);

        return Step::make('Informations')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                TextEntry::make('recap_creneau')
                    ->label('Créneau')
                    ->getStateUsing(function () use ($widget) {
                        if (! $widget->etapeDebut) return '—';
                        return \Carbon\Carbon::parse($widget->etapeDebut)->format('d/m/Y H:i')
                            . ' - '
                            . \Carbon\Carbon::parse($widget->etapeFin)->format('H:i');
                    }),

                TextEntry::make('capacite_label')
                    ->label('Capacité')
                    ->getStateUsing(fn () => $capacite . ' personne(s) maximum')
                    ->columnSpanFull(),

                Repeater::make('participants')
                    ->label('Participants')
                    ->addActionLabel('Ajouter un participant')
                    ->minItems(1)
                    ->maxItems($capacite)
                    ->reorderable(false)
                    ->schema([
                        Select::make('user_id')
                            ->label('Personne')
                            ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                            ->searchable()
                            ->nullable()
                            ->columnSpanFull(),

                        ...($isIntradef ? [
                            Actions::make([self::annuaireAction()])->columnSpanFull(),
                        ] : []),
                    ])
                    ->columnSpanFull(),

                TextInput::make('libelle_court')
                    ->label('Intitulé de la réservation')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Textarea::make('libelle_long')
                    ->label('Informations complémentaires')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    private static function annuaireAction(): Action
    {
        return Action::make('annuaire_user')
            ->label('Rechercher dans l\'annuaire')
            ->icon('heroicon-o-magnifying-glass')
            ->color('gray')
            ->modalHeading('Recherche annuaire')
            ->schema([
                Wizard::make([
                    Step::make('Critères')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('ann_nom')->label('Nom'),
                                TextInput::make('ann_prenom')->label('Prénom'),
                                TextInput::make('ann_email')->label('Email'),
                                TextInput::make('ann_unite')->label('Unité'),
                            ]),
                        ])
                        ->afterValidation(function ($get, $set) {
                            $ok = AnnuaireUser::setQuery([
                                'nom'    => $get('ann_nom')    ?? '',
                                'prenom' => $get('ann_prenom') ?? '',
                                'email'  => $get('ann_email')  ?? '',
                                'unite'  => $get('ann_unite')  ?? '',
                            ]);
                            if (! $ok) {
                                Notification::make()->title('Annuaire inaccessible')->danger()->send();
                                throw ValidationException::withMessages(['ann_nom' => 'Annuaire inaccessible.']);
                            }
                            $set('ann_resultats', AnnuaireUser::query()->get()
                                ->mapWithKeys(fn ($u) => [$u->nid => trim("{$u->nom} {$u->prenom}") . ($u->unites ? " ({$u->unites})" : '')])
                                ->toArray());
                            $set('ann_nid', null);
                            $set('ann_data', null);
                        }),

                    Step::make('Résultats')
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Select::make('ann_nid')
                                ->label('Sélectionner un utilisateur')
                                ->options(fn ($get) => $get('ann_resultats') ?? [])
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (! $state) return;
                                    $user = AnnuaireUser::query()->get()->firstWhere('nid', $state);
                                    if ($user) {
                                        $set('ann_data', json_encode([
                                            'nom'    => $user->nom,
                                            'prenom' => $user->prenom ?? $user->prenomusuel ?? '',
                                            'email'  => $user->email,
                                        ]));
                                    }
                                }),
                            \Filament\Forms\Components\Hidden::make('ann_resultats'),
                            \Filament\Forms\Components\Hidden::make('ann_data'),
                        ]),
                ])->columnSpanFull(),
            ])
            ->action(function (array $data, $set) {
                $raw = $data['ann_data'] ?? null;
                if (! $raw) {
                    Notification::make()->title('Aucun utilisateur sélectionné.')->warning()->send();
                    return;
                }
                $info = json_decode($raw, true);
                $existing = User::where('email', $info['email'])->first();
                if ($existing) {
                    $set('user_id', $existing->id);
                    return;
                }
                \App\Events\UnUtilisateurLocalDoitEtreCreeEvent::dispatch($info, []);
                Notification::make()
                    ->title('Utilisateur ajouté')
                    ->body(trim("{$info['nom']} {$info['prenom']}") . ' a été créé. Sélectionnez-le dans la liste.')
                    ->success()->send();
            });
    }
}
```

### app/Filament/Widgets/CalendrierWidget/Actions/VoirReservationAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Formation;

class VoirReservationAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirReservation')
            ->label('')
            ->extraAttributes(['style' => 'display:none'])
            ->schema(fn (): array => self::schema($widget))
            ->modalHeading(fn () => $widget->recordTitre ?? 'Reservation')
            ->modalSubmitAction(false)
            ->extraModalFooterActions(fn (): array => self::footerActions($widget))
            ->action(fn () => null);
    }

    private static function schema(object $widget): array
    {
        $r = fn () => $widget->recordEnCours ? Reservation::find($widget->recordEnCours) : null;

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Debut')
                    ->getStateUsing(fn () => $r()?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $r()?->fin?->format('d/m/Y H:i') ?? '—'),
            ]),

            TextEntry::make('poste_ph')
                ->label('Poste')
                ->getStateUsing(fn () => $r()?->sallePoste?->libelle_court ?? '—'),

            TextEntry::make('salle_ph')
                ->label('Salle')
                ->getStateUsing(fn () => $r()?->sallePoste?->salle?->libelle_court ?? '—'),

            TextEntry::make('user_ph')
                ->label('Stagiaire 1')
                ->getStateUsing(fn () => $r()?->user?->nom ?? '—'),

            TextEntry::make('user2_ph')
                ->label('Stagiaire 2')
                ->visible(function () use ($r) {
                    $record = $r();
                    return $record && ! empty($record->data['users'][1]);
                })
                ->getStateUsing(function () use ($r) {
                    $record = $r();
                    $user2Id = $record?->data['users'][1] ?? null;
                    if (! $user2Id) return '—';
                    return \App\Models\User::find($user2Id)?->nom ?? '—';
                }),

            TextEntry::make('capacites_ph')
                ->label('Capacites utilisees')
                ->getStateUsing(function () use ($r) {
                    $record = $r();
                    if (! $record || empty($record->formations_utilisees)) return '—';
                    return Formation::whereIn('id', $record->formations_utilisees)
                        ->pluck('libelle_court')->join(', ');
                }),

            TextEntry::make('libelle_long_ph')
                ->label('Informations')
                ->getStateUsing(fn () => $r()?->libelle_long ?? '—'),

            TextEntry::make('statut_ph')
                ->label('Statut')
                ->getStateUsing(fn () => $r()?->isValide() ? 'Validee' : 'En attente de validation')
                ->badge()
                ->color(fn ($state) => $state === 'Validee' ? 'success' : 'warning'),

            TextEntry::make('valide_par_ph')
                ->label('Validee par')
                ->visible(fn () => $widget->recordEnCours && $r()?->isValide())
                ->getStateUsing(fn () => ($r()?->validatedBy?->nom ?? '') . ' - ' . ($r()?->validated_at?->format('d/m/Y H:i') ?? '')),
        ];
    }

    private static function footerActions(object $widget): array
    {
        $record = $widget->recordEnCours ? Reservation::find($widget->recordEnCours) : null;
        if (! $record) return [];

        $actions = [];

        $canModify = $record->isEnAttente()
            && ($widget->adminMode || auth()->id() === $record->user_id);

        if ($canModify) {
            $actions[] = Action::make('go_modifier_horaires')
                ->label('Modifier horaires')
                ->color('gray')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    DateTimePicker::make('debut')
                        ->label('Date debut')
                        ->required()
                        ->seconds(false)
                        ->default($record->debut),

                    DateTimePicker::make('fin')
                        ->label('Date fin')
                        ->required()
                        ->seconds(false)
                        ->after('debut')
                        ->default($record->fin),
                ])
                ->action(function (array $data) use ($record, $widget): void {
                    $erreur = $widget->validerCreneauPublic($data['debut'], $data['fin'], $record->id);
                    if ($erreur) {
                        Notification::make()->title($erreur)->danger()->send();
                        return;
                    }

                    $record->update([
                        'debut' => $data['debut'],
                        'fin'   => $data['fin'],
                    ]);

                    $widget->refreshRecords();
                    Notification::make()->title('Horaires modifies')->success()->send();
                });

            $actions[] = Action::make('go_supprimer')
                ->label('Supprimer')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () use ($record, $widget): void {
                    $id = $record->id;
                    $widget->recordEnCours = null;
                    $widget->recordTitre   = null;
                    $widget->eventRecord   = null;
                    Reservation::destroy($id);
                    $widget->refreshRecords();
                    Notification::make()->title('Reservation supprimee')->success()->send();
                });
        }

        $peutValider = $widget->adminMode && auth()->user()?->can('valider', $record);

        if ($peutValider) {
            $actions[] = Action::make('toggle_validation')
                ->label($record->isValide() ? 'Invalider' : 'Valider')
                ->color($record->isValide() ? 'warning' : 'success')
                ->icon($record->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function () use ($record, $widget): void {
                    $record->isValide() ? $record->invalider() : $record->valider(auth()->user());
                    $widget->refreshRecords();
                    Notification::make()
                        ->title($record->fresh()->isValide() ? 'Reservation validee' : 'Reservation invalidee')
                        ->success()->send();
                });
        }

        return $actions;
    }
}
```

### app/Filament/Widgets/RessourceGanttWidget/Actions/NouvelleResaAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceReservation;

class NouvelleResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('nouvelleResa')
            ->label('Nouvelle réservation')
            ->icon('heroicon-o-plus')
            ->color('primary')
            ->visible(fn () => auth()->check())
            ->schema(self::schema($widget))
            ->action(fn (array $data) => self::creerReservation($widget, $data))
            ->fillForm(fn () => [
                'ressource_id' => $widget->newRessourceId,
                'debut'        => $widget->newDebut,
                'fin'          => $widget->newFin,
            ]);
    }

    private static function schema(object $widget): array
    {
        return [
            Select::make('ressource_id')
                ->label('Ressource')
                ->options(fn () => $widget->getResources()->pluck('nom', 'id'))
                ->required()
                ->searchable(),

            DateTimePicker::make('debut')
                ->label('Début')
                ->required()
                ->seconds(false),

            DateTimePicker::make('fin')
                ->label('Fin')
                ->required()
                ->seconds(false)
                ->after('debut'),

            Textarea::make('libelle')
                ->label('Objet')
                ->rows(2),

            TextInput::make('batiment')
                ->label('Bâtiment')
                ->required()
                ->maxLength(255),

            TextInput::make('service')
                ->label('Service concerné')
                ->required()
                ->maxLength(255),

            Select::make('emprunteur_user_id')
                ->label('Emprunteur')
                ->helperText('Le grade est repris automatiquement depuis son dossier RH.')
                ->options(fn () => User::orderBy('nom')->get()
                    ->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                ->searchable()
                ->required()
                ->default(auth()->id()),

            Select::make('officier_responsable_user_id')
                ->label('Officier responsable')
                ->helperText('Le grade est repris automatiquement depuis son dossier RH.')
                ->options(fn () => User::orderBy('nom')->get()
                    ->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                ->searchable()
                ->required(),
        ];
    }

    private static function creerReservation(object $widget, array $data): void
    {
        if (! auth()->check()) return;

        $ressource = Ressource::find($data['ressource_id']);
        if (! $ressource?->isDisponible()) {
            Notification::make()->title('Ressource indisponible')
                ->body('Cette ressource est actuellement ' . ($ressource?->statut_label ?? 'indisponible') . ' et ne peut pas être réservée.')
                ->danger()->send();
            return;
        }

        $conflit = RessourceReservation::where('ressource_id', $data['ressource_id'])
            ->where('debut', '<', $data['fin'])
            ->where('fin',   '>', $data['debut'])
            ->exists();

        if ($conflit) {
            Notification::make()->title('Conflit de réservation')
                ->body('Cette ressource est déjà réservée sur ce créneau.')
                ->danger()->send();
            return;
        }

        $gestionnaireCategorie = auth()->user()->can('resa::gestionnaire_ressource')
            && $ressource->categorie?->isGestionnaire(auth()->user());
        $valideDirectement = $widget->adminMode || $gestionnaireCategorie;

        RessourceReservation::create([
            'user_id'                      => auth()->id(),
            'ressource_id'                 => $data['ressource_id'],
            'libelle'                      => $data['libelle'] ?? null,
            'debut'                        => $data['debut'],
            'fin'                          => $data['fin'],
            'batiment'                     => $data['batiment'] ?? null,
            'service'                      => $data['service'] ?? null,
            'emprunteur_user_id'           => $data['emprunteur_user_id'] ?? null,
            'officier_responsable_user_id' => $data['officier_responsable_user_id'] ?? null,
            'validation'                   => $valideDirectement ? RessourceReservation::VALIDE : RessourceReservation::EN_ATTENTE,
            'validated_by'                 => $valideDirectement ? auth()->id() : null,
            'validated_at'                 => $valideDirectement ? now() : null,
        ]);

        $widget->refreshRecords();
        $widget->newDebut       = null;
        $widget->newFin         = null;
        $widget->newRessourceId = null;
    }
}
```

### app/Filament/Widgets/RessourceGanttWidget/Actions/VoirResaAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Modules\Resa\Models\RessourceReservation;

class VoirResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirResa')
            ->label('Réservation')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->extraAttributes(['style' => 'display:none'])
            ->modalHeading(fn () => $widget->viewingId
                ? (RessourceReservation::find($widget->viewingId)?->ressource?->nom ?? 'Réservation')
                : 'Réservation'
            )
            ->modalSubmitAction(false)
            ->schema(fn (): array => self::schema($widget))
            ->extraModalFooterActions(fn (): array => self::footerActions($widget))
            ->action(fn () => null);
    }

    private static function schema(object $widget): array
    {
        $r = fn () => $widget->viewingId
            ? RessourceReservation::with(['ressource', 'user', 'validatedBy', 'emprunteur', 'officierResponsable'])->find($widget->viewingId)
            : null;

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Début')
                    ->getStateUsing(fn () => $r()?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $r()?->fin?->format('d/m/Y H:i') ?? '—'),
            ]),

            TextEntry::make('ressource_ph')
                ->label('Ressource')
                ->getStateUsing(fn () => $r()?->ressource?->nom ?? '—'),

            TextEntry::make('libelle_ph')
                ->label('Objet')
                ->getStateUsing(fn () => $r()?->libelle ?: '(sans objet)'),

            TextEntry::make('user_ph')
                ->label('Réservé par')
                ->getStateUsing(fn () => $r()?->user?->nom ?? '—'),

            Grid::make(2)->schema([
                TextEntry::make('batiment_ph')
                    ->label('Bâtiment')
                    ->getStateUsing(fn () => $r()?->batiment ?? '—'),

                TextEntry::make('service_ph')
                    ->label('Service')
                    ->getStateUsing(fn () => $r()?->service ?? '—'),
            ]),

            Grid::make(2)->schema([
                TextEntry::make('emprunteur_ph')
                    ->label('Emprunteur')
                    ->getStateUsing(fn () => $r()?->emprunteur
                        ? trim($r()->emprunteur->nom . ' ' . $r()->emprunteur->prenom) . ' (' . $r()->emprunteur_grade . ')'
                        : '—'),

                TextEntry::make('officier_ph')
                    ->label('Officier responsable')
                    ->getStateUsing(fn () => $r()?->officierResponsable
                        ? trim($r()->officierResponsable->nom . ' ' . $r()->officierResponsable->prenom) . ' (' . $r()->officier_responsable_grade . ')'
                        : '—'),
            ]),

            TextEntry::make('statut_ph')
                ->label('Statut')
                ->getStateUsing(fn () => $r()?->isValide() ? 'Validée' : 'En attente')
                ->badge()
                ->color(fn ($state) => $state === 'Validée' ? 'success' : 'warning'),

            TextEntry::make('valide_par_ph')
                ->label('Validée par')
                ->visible(fn () => $widget->viewingId && $r()?->isValide())
                ->getStateUsing(fn () => ($r()?->validatedBy?->nom ?? '') . ' — ' . ($r()?->validated_at?->format('d/m/Y H:i') ?? '')),
        ];
    }

    private static function footerActions(object $widget): array
    {
        if (! $widget->viewingId) return [];

        $r = RessourceReservation::find($widget->viewingId);
        if (! $r) return [];

        $actions = [];

        $canModify = (! $r->isValide()) && ($widget->adminMode || auth()->id() === $r->user_id);

        if ($canModify) {
            $actions[] = Action::make('supprimer_resa')
                ->label('Supprimer')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () use ($r, $widget): void {
                    $id = $r->id;
                    $widget->viewingId = null;
                    RessourceReservation::destroy($id);
                    $widget->refreshRecords();
                    Notification::make()->title('Réservation supprimée')->success()->send();
                });
        }

        if (auth()->user()?->can('valider', $r)) {
            $actions[] = Action::make('toggle_validation')
                ->label($r->isValide() ? 'Invalider' : 'Valider')
                ->color($r->isValide() ? 'warning' : 'success')
                ->icon($r->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function () use ($r, $widget): void {
                    $r->isValide() ? $r->invalider() : $r->valider(auth()->user());
                    $widget->refreshRecords();
                    Notification::make()->title($r->fresh()->isValide() ? 'Validée' : 'Invalidée')->success()->send();
                });
        }

        return $actions;
    }
}
```

### app/Filament/Widgets/RessourceGanttWidget/Actions/SeConnecterAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Illuminate\Support\HtmlString;

class SeConnecterAction
{
    public static function make(object $widget): Action
    {
        $ssoName = match (config('skeletor.reseau_de_deploiement')) {
            'sic21'  => 'POLARIS Online',
            default  => 'Mindef Connect',
        };

        $pageUrl = url()->current();

        return Action::make('seConnecter')
            ->label('Connexion')
            ->modalHeading('Connexion')
            ->modalSubmitActionLabel('Connexion locale')
            ->modalWidth('sm')
            ->schema([
                Placeholder::make('sso_block')
                    ->label('')
                    ->content(new HtmlString(
                        '<button type="button" wire:click="redirectSso" class="inline-flex items-center justify-center gap-2 w-full rounded-lg border border-primary-600 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-50 dark:border-primary-400 dark:text-primary-400 dark:hover:bg-primary-950">'
                        . '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>'
                        . 'Se connecter via ' . e($ssoName)
                        . '</button>'
                        . '<div class="my-4 flex items-center gap-3"><div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div><span class="text-xs font-medium text-gray-400">OU</span><div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div></div>'
                    )),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->autofocus(),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->action(function (array $data) use ($pageUrl, $widget): void {
                if (LaravelAuth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                    session()->regenerate();
                    $widget->redirect($pageUrl);
                } else {
                    Notification::make()
                        ->title('Identifiants incorrects')
                        ->body('Email ou mot de passe invalide.')
                        ->danger()
                        ->send();
                }
            });
    }
}
```

### app/Filament/Widgets/SuiviFormationsWidget/Actions/VoirStagiaireAction.php

```php
<?php

namespace Modules\Resa\Filament\Widgets\SuiviFormationsWidget\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\RH\Models\Marin;

class VoirStagiaireAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirStagiaire')
            ->label('')
            ->extraAttributes(['style' => 'display:none'])
            ->modalHeading('Détail de la réservation')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer')
            ->schema(fn (array $arguments): array => self::schema($arguments['reservationId'] ?? null))
            ->action(fn () => null);
    }

    private static function schema(?int $reservationId): array
    {
        $reservation = $reservationId ? Reservation::find($reservationId) : null;

        $userIds = collect($reservation?->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $reservation?->user_id) {
            $userIds = collect([$reservation->user_id]);
        }

        $marins = User::whereIn('id', $userIds)->get()
            ->map(fn (User $u) => Marin::fromUser($u) ?? $u)
            ->values();

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Début')
                    ->getStateUsing(fn () => $reservation?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $reservation?->fin?->format('H:i') ?? '—'),
            ]),

            TextEntry::make('salle_poste_ph')
                ->label('Salle / Poste')
                ->getStateUsing(fn () => trim(
                    ($reservation?->sallePoste?->salle?->libelle_court ?? '')
                    . ' — ' . ($reservation?->sallePoste?->libelle_court ?? '')
                )),

            TextEntry::make('capacites_ph')
                ->label('Formation')
                ->getStateUsing(fn () => empty($reservation?->formations_utilisees)
                    ? '—'
                    : Formation::whereIn('id', $reservation->formations_utilisees)->pluck('libelle_court')->join(', ')),

            TextEntry::make('libelle_ph')
                ->label('Objet')
                ->getStateUsing(fn () => $reservation?->libelle_court ?: ($reservation?->libelle_long ?? '—')),

            ...self::schemaStagiaires($marins),
        ];
    }

    /** Une section par stagiaire, avec ses infos RH quand disponibles (grade, spécialité, unité...). */
    private static function schemaStagiaires($marins): array
    {
        if ($marins->isEmpty()) {
            return [
                TextEntry::make('aucun_stagiaire_ph')->label('Stagiaire')->getStateUsing(fn () => '—'),
            ];
        }

        return $marins->flatMap(function ($marin, $index) {
            $estMarin = $marin instanceof Marin;
            $nom = $estMarin
                ? trim("{$marin->nom} {$marin->prenom}")
                : trim("{$marin->nom} {$marin->prenom}");

            return [
                Grid::make(2)->schema([
                    TextEntry::make("stagiaire_{$index}_nom")
                        ->label('Stagiaire ' . ($index + 1))
                        ->getStateUsing(fn () => $nom ?: '—'),

                    TextEntry::make("stagiaire_{$index}_matricule")
                        ->label('Matricule')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->matricule ?? '—'),

                    TextEntry::make("stagiaire_{$index}_grade")
                        ->label('Grade')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->grade?->libelle_court ?? '—'),

                    TextEntry::make("stagiaire_{$index}_specialite")
                        ->label('Spécialité')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->specialite?->libelle_court ?? '—'),

                    TextEntry::make("stagiaire_{$index}_unite")
                        ->label('Unité')
                        ->getStateUsing(fn () => $estMarin
                            ? ($marin->getUnite(explore_user: true)?->libelle_court ?? '—')
                            : '—'),

                    TextEntry::make("stagiaire_{$index}_email")
                        ->label('Email')
                        ->getStateUsing(fn () => $marin->email ?? '—'),
                ]),
            ];
        })->values()->all();
    }
}
```

### resources/docs/guide-utilisateur.md

````markdown
# Guide utilisateur — Module Réservation

## Accès au module

Le module Resa est accessible depuis le panneau **Resa** de l'application. L'URL dépend de la configuration du serveur.

Le calendrier est visible par tous les visiteurs, même non connectés. La connexion est nécessaire pour réserver.

---

## Créer un compte Admin

Un admin a accès complet au module : toutes les salles, toutes les réservations, validation sans restriction.

**Étapes :**

1. Créer l'utilisateur via le panneau d'administration principal (ou `User::factory()->create()` en dev)
2. Attribuer les permissions via l'interface Spatie ou en console :

```php
$user = User::find($id);
$user->givePermissionTo('resa::admin');
$user->givePermissionTo('resa::reservation.view');
```

Ou via le rôle complet qui regroupe toutes les permissions :

```php
$user->assignRole('resa::admin');
```

**Lancer le seeder de permissions** (première installation) :

```bash
php artisan db:seed --class="Modules\Resa\Database\Seeders\PermissionsSeeder"
php artisan db:seed --class="Modules\Resa\Database\Seeders\RolesSeeder"
```

---

## Créer un compte Gestionnaire de salle

Un gestionnaire peut gérer uniquement les salles qui lui sont assignées : modifier la salle, créer des postes et capacités, valider les réservations entrantes.

**Depuis l'interface (recommandé) :**

1. Se connecter avec un compte admin
2. Aller dans **Resa > Gestion > Salles**
3. Ouvrir la salle concernée → **Modifier**
4. Champ **Gestionnaires** → sélectionner l'utilisateur
5. Sauvegarder

La permission `resa::gestionnaire_salle` est accordée automatiquement à l'utilisateur lors de la sauvegarde.

**Ou en console :**

```php
use Spatie\Permission\Models\Permission;

$salle = \Modules\Resa\Models\Salle::find($salleId);
$salle->gestionnaires()->attach($userId);

// Accorder la permission manuellement si bypass de l'interface
$user->givePermissionTo(Permission::firstOrCreate(['name' => 'resa::gestionnaire_salle', 'guard_name' => 'web']));
```

Un gestionnaire peut être assigné à plusieurs salles. Une salle peut avoir plusieurs gestionnaires.

---

## Configurer les emails de notification (salle, formation)

Quand une réservation est créée (salle classique, ou formation/type de capacité), un email de notification est envoyé automatiquement à chaque adresse renseignée.

1. Aller dans **Resa > Gestion > Salles** (ou **Gestion > Type de capacité**) → **Modifier**
2. Champ **Email(s) de notification** : tapez une adresse puis **Entrée** pour l'ajouter à la liste — chaque adresse apparaît comme une étiquette
3. Pour retirer une adresse : cliquer sur le **×** sur son étiquette
4. Répéter pour ajouter plusieurs destinataires

Si la liste est vide, aucun email n'est envoyé.

---

## Réserver une salle (utilisateur)

### Mode Simulateur

1. Ouvrir le calendrier de la salle
2. Cliquer sur un créneau libre ou sélectionner une plage horaire
3. **Étape 1** — choisir les capacités nécessaires (FPV, 20mm...) et valider les horaires
4. **Étape 2** — choisir un poste parmi ceux disponibles (triés par score de compatibilité)
5. **Étape 3** — renseigner un libellé (objet de la réservation)
6. Confirmer → réservation créée **en attente de validation**

### Mode Classique

1. Ouvrir le calendrier de la salle
2. Cliquer sur un créneau libre
3. **Étape 1** — vérifier les horaires (la salle est libre ou bloquée)
4. **Étape 2** — renseigner un libellé
5. Confirmer → réservation créée **en attente de validation**

**Contraintes horaires :** du lundi au vendredi, entre 08h00 et 17h00. Pas de réservation dans le passé. Un utilisateur ne peut pas avoir deux créneaux qui se chevauchent.

---

## Valider une réservation (admin ou gestionnaire)

1. Ouvrir le calendrier — les réservations en attente apparaissent en gris
2. Cliquer sur la réservation
3. Dans le modal → bouton **Valider**
4. La réservation passe en vert

Un admin voit et peut valider toutes les réservations de toutes les salles.
Un gestionnaire voit uniquement les réservations des salles qui lui sont assignées.

---

## Invalider une réservation

Même procédure que la validation — le bouton devient **Invalider** si la réservation est déjà validée. La réservation repasse en attente.

---

## Modifier une réservation

**Via le calendrier :**
- Glisser-déposer pour changer le créneau (admin et gestionnaire uniquement)
- Redimensionner pour modifier la durée

**Via la liste :**
1. Aller dans **Resa > Réservations**
2. Cliquer sur **Modifier** sur la ligne concernée

Un utilisateur standard peut modifier uniquement ses propres réservations en attente. Une réservation validée ne peut plus être modifiée par l'utilisateur (seulement par admin/gestionnaire).

---

## Supprimer une réservation

- **Utilisateur** : peut supprimer ses réservations en attente uniquement
- **Admin / Gestionnaire** : peut supprimer n'importe quelle réservation de leurs salles

Dans le calendrier : cliquer sur la réservation → bouton **Supprimer** dans le modal.

---

## Réserver pour un tiers (admin uniquement)

1. Ouvrir le calendrier
2. Sélectionner un créneau → lancer le Wizard
3. **Étape 3** — champ **Utilisateur** → sélectionner le bénéficiaire
4. Confirmer → réservation directement **validée** avec tracabilité (`validated_by`, `validated_at`)

---

## Configurer une salle (admin ou gestionnaire)

### Créer une salle

Accessible uniquement aux admins via **Resa > Gestion > Salles > Créer**.

| Champ | Description |
|-------|-------------|
| Type de salle | Simulateur ou Classique |
| Lieu / Unité | Rattachement géographique (module RH) |
| Code | Identifiant court |
| Titre | Nom affiché dans le calendrier |
| Capacité | Nombre de personnes (indicatif) |
| Image | Photo de la salle |
| Email(s) | Destinataires des notifications |
| Gestionnaires | Utilisateurs autorisés à gérer cette salle |

### Créer un poste

1. **Resa > Gestion > Salles** → ouvrir la salle → onglet **Postes**
2. **Créer un poste** → renseigner le libellé et l'ordre
3. Assigner des capacités depuis l'onglet **Capacités du poste**

### Ajouter des capacités

1. **Resa > Gestion > Types de capacité** → créer les types (FPV, 20mm...)
2. Revenir sur le poste → onglet **Capacités** → lier les types

---

## Navigation géographique

Si les salles sont rattachées à des lieux et unités (via le module RH), le calendrier affiche automatiquement les étapes de sélection :

- **Plusieurs lieux** → choix du lieu
- **1 seul lieu** → saute directement aux unités
- **1 seule unité** → saute directement aux salles
- **1 seule salle** → ouvre directement le calendrier

Cette navigation est transparente pour l'utilisateur — les étapes inutiles sont automatiquement sautées.

---

## Ressources (matériel réservable)

En plus des salles, le module permet de réserver du **matériel** (ressources) indépendamment d'une salle : casques VR, équipements mobiles, etc. Organisées en **catégories** (ex. "Casques VR", "Équipement audio").

### Consulter les ressources (mode utilisateur)

1. Aller dans **Resa > Ressources** (menu de navigation, groupe **Ressources**)
2. La page affiche un planning (Gantt) de toutes les ressources, avec leurs réservations en cours
3. Filtrer par catégorie via le paramètre de la page (`?categorie=ID`) si proposé dans le menu
4. Cliquer sur un créneau libre pour réserver une ressource — accessible à tout utilisateur connecté (`RessourcesPage::canAccess()` ne demande qu'une authentification)

### Informations obligatoires pour réserver du matériel

En plus de l'horaire et de l'objet, toute réservation de matériel doit renseigner :

| Champ | Description |
|-------|-------------|
| Bâtiment | Lieu physique où se trouve/sera utilisé le matériel |
| Service concerné | Service demandeur |
| Emprunteur | Utilisateur sélectionné dans la liste — son grade est repris automatiquement depuis son dossier RH |
| Officier responsable | Utilisateur sélectionné dans la liste — grade également repris du dossier RH |

Ces informations apparaissent ensuite dans le détail de la réservation (bouton **Réservation** dans le planning).

### Gérer les catégories et ressources (admin uniquement)

**Catégories de ressources** — **Resa > Gestion > Catégories de ressources** (réservé à `resa::admin`) :

1. **Créer** → renseigner libellé, libellé court, ordre, icône, couleur
2. Chaque catégorie regroupe plusieurs ressources (compteur affiché dans la liste)
3. Champ **Gestionnaires** → les utilisateurs choisis peuvent **valider/invalider** les réservations du matériel de cette catégorie, et leurs propres réservations sur cette catégorie sont **validées directement** (comme un gestionnaire de salle ou de formation)

**Ressources** — **Resa > Gestion > Ressources** (réservé à `resa::admin`) :

| Champ | Description |
|-------|-------------|
| Catégorie | Catégorie de rattachement (obligatoire) |
| Unité | Rattachement géographique optionnel (module RH) |
| Libellé / Libellé court | Nom affiché |
| Ordre | Ordre d'affichage |
| Actif | Désactive la ressource (non réservable) si décoché |
| Statut | Disponibilité : *Disponible* / *En entretien* / *Indisponible* |
| Description | Texte libre |

### Signaler un matériel en entretien ou indisponible

Contrairement au champ **Actif** (qui retire définitivement la ressource du catalogue), le champ **Statut** permet de signaler temporairement qu'un équipement n'est pas utilisable, sans le supprimer :

1. **Resa > Gestion > Ressources** → modifier la ressource concernée
2. Champ **Disponibilité** → choisir *En entretien* ou *Indisponible*
3. Enregistrer

Effets :

- La ressource reste **visible** dans le planning (Gantt), avec son libellé suffixé **⚠️ En entretien** ou **⛔ Indisponible**
- Toute tentative de réservation sur cette ressource est **bloquée**, avec un message d'erreur explicite
- Repasser le statut sur *Disponible* rend immédiatement la ressource réservable à nouveau

### Valider les réservations de matériel (admin ou gestionnaire habilité)

**Resa > Gestion > Réservations matériels** (accessible avec `resa::reservation.index` ou `resa::admin`) :

1. La liste affiche toutes les réservations de ressources avec statut **En attente** / **Validée**
2. Bouton **Valider / Invalider** sur chaque ligne (réservé à `resa::admin`)
3. Actions groupées : **Valider la sélection** / **Invalider la sélection** sur plusieurs réservations à la fois
4. Un utilisateur standard peut supprimer uniquement ses propres réservations de matériel ; l'admin peut tout supprimer

---

## Résumé des 3 modes d'accès

### Mode Utilisateur (connecté, sans permission particulière)

- Consulte le calendrier des salles et le planning des ressources
- Réserve une salle (Simulateur ou Classique) → en attente de validation
- Réserve une ressource (matériel) → en attente de validation
- Modifie / supprime uniquement ses propres réservations en attente

### Mode Gestion (gestionnaire de salle — `resa::gestionnaire_salle`)

- Voit et valide/invalide les réservations des **salles qui lui sont assignées** uniquement
- Peut glisser-déposer / redimensionner les réservations de ses salles
- Ne gère pas les catégories de ressources ni les ressources elles-mêmes (réservé au super admin)

### Mode Super Admin (`resa::admin`)

- Accès complet à toutes les salles, tous les postes, toutes les réservations (salles et matériel)
- Seul profil pouvant créer/modifier des salles, types de salle, types de poste, types de capacité, catégories de ressources et ressources
- Réserve pour un tiers (réservation directement validée, avec traçabilité `validated_by` / `validated_at`)
- Valide/invalide en masse les réservations de matériel

---

## Permissions disponibles

| Permission | Description |
|-----------|-------------|
| `resa::admin` | Accès complet — toutes les salles, ressources et réservations |
| `resa::reservation.view` | Voir toutes les réservations dans la liste Resource |
| `resa::reservation.index` | Accéder à la liste des réservations de matériel |
| `resa::gestionnaire_salle` | Gérer et valider uniquement les salles assignées |
````

### resources/docs/cahier-des-charges-gestion-formation-materiel.md

```markdown
# Cahier des charges — Gestion (salles/formations) & Matériel

Document de suivi des évolutions apportées au module Resa en octobre 2026, et des demandes
restantes à traiter. À mettre à jour à chaque nouvelle évolution.

---

## 1. Ce qui a été fait

### 1.1 Correctif calendrier — décalage horaire

- **Problème** : une réservation créée 12h–13h s'affichait 14h–15h dans le calendrier.
- **Cause** : `app.timezone = UTC` alors que les heures saisies sont des heures murales
  locales (Europe/Paris) ; le calendrier (Guava/EventCalendar) reconvertissait ensuite côté
  navigateur, décalant l'affichage.
- **Correctif** : `Reservation::toCalendarEvent()` retague explicitement `debut`/`fin` en
  `Europe/Paris` avant de les transmettre au `CalendarEvent`.

### 1.2 Gestionnaire de salle

- Champ **Gestionnaires** (multi-select) ajouté au formulaire `SalleResource` (salles
  classiques et simulateur).
- `CreateSalle`/`EditSalle` : attribution automatique de la permission
  `resa::gestionnaire_salle` aux gestionnaires choisis, **révocation symétrique** quand un
  gestionnaire est retiré et ne gère plus aucune autre salle.
- `CalendrierWidget` : un gestionnaire de salle (non admin) ne voit que les salles qu'il gère.

### 1.3 Gestionnaire de formation (type de capacité)

- Le champ "Gestionnaires" existait déjà sur `TypeCapaciteForm` (Formation), mais
  **aucune permission n'était réellement posée** : ajouté dans `CreateTypeCapacite` /
  `EditTypeCapacite`, même logique d'attribution/révocation symétrique que pour les salles.
- `Formation::isGestionnaire()` ajouté (miroir de `Salle::isGestionnaire()`).
- `ReservationPolicy::valider()` autorise désormais : admin **ou** gestionnaire de la salle
  concernée **ou** gestionnaire d'une des formations utilisées par la réservation.
- `CalendrierWidget` : nouveau mode **`modeFormationGlobale`** — calendrier transversal
  (toutes salles), filtré par formation via `filtreFormations`, sans navigation
  lieu/unité/salle. Les colonnes (postes) sont calculées dynamiquement à partir des salles
  ayant une réservation pour la formation filtrée, avec préfixe "Salle — Poste" si plusieurs
  salles sont concernées.

### 1.4 Dashboard du gestionnaire de formation

- **`SuiviFormationsWidget`** (existait mais était désactivé dans le panel — réactivé) :
  - Cartes stats (formations gérées, réservations à venir, en attente, ce mois).
  - Onglets par formation si le gestionnaire en gère plusieurs.
  - Calendrier `CalendrierWidget` (mode transversal) embarqué.
  - Tableau détaillé avec filtres (période/statut/stagiaire) et bouton **Valider/Invalider**.
  - Bouton **Voir** (`VoirStagiaireAction`) : modale avec détail de la réservation + infos
    RH du/des stagiaire(s) (grade, spécialité, unité, matricule) via `Marin::fromUser()`.
  - Après (in)validation : `calendarVersion` incrémenté pour forcer le remontage complet du
    calendrier embarqué (plus fiable que l'évènement JS `calendar--refresh` seul, qui dépend
    du chargement différé `x-load`).
- **`ReservationWidget`** : l'assistant générique "Bienvenue sur Resa, configurez votre
  réservation" est masqué pour un gestionnaire de formation **pur** (ni admin, ni
  gestionnaire de salle) — il atterrit directement sur son dashboard dédié.
- **`MesReservationsWidget`** (nouveau) : visible pour tout utilisateur connecté, liste
  **toutes ses réservations** (salles + matériel confondus), avec stats, filtres et
  suppression (uniquement sur ses réservations en attente).

### 1.5 Cohérence du menu "Gestion"

- `SalleResource` : colonne **Type de salle** + filtre ajoutés dans le tableau.
- Réordonnancement des `navigationSort` (les valeurs se chevauchaient) :
  Réservations(1) → Réservations matériels(2) → Salles(3) → Types de salle(4) → Postes(5)
  → Types de poste(6) → Type de capacité(7).

### 1.6 Email de notification — Salle

- Champ **Email(s) de notification** ajouté à `SalleResource`, **visible uniquement en mode
  salle classique** (masqué pour les salles simulateur). Le mécanisme d'envoi existait déjà
  côté backend (`ReservationObserver`), seul le champ UI manquait.

### 1.7 Disponibilité du matériel (point 3 de la demande ci-dessous)

- Nouvelle colonne `statut` sur `Ressource` (`disponible` par défaut / `entretien` /
  `indisponible`), distincte de `actif` (actif = toujours au catalogue).
- `RessourceResource` : champ + colonne + filtre "Disponibilité".
- `RessourceGanttWidget` : le matériel en entretien/indisponible reste **visible** dans le
  planning (libellé de colonne suffixé ⚠️/⛔) mais toute tentative de réservation est
  **bloquée** avec message explicite.

### 1.8 Outillage et infra

- `Modules/Resa/tests/Feature/GestionFormationTest.php` : 54 tests Pest couvrant l'ensemble
  des points 1.2 à 1.7 (non régressé par rapport aux 71 échecs pré-existants côté
  `ResourcesFormTest.php`/`ResourcesAdminTest.php`, dûs à un bug d'infrastructure
  `Livewire::test()->fillForm()` indépendant de ces changements).
- `Modules/Resa/tools/video/` : scripts Playwright pour générer des vidéos de présentation
  (utilisateur / gestion / super-admin) — non exécutés, à vérifier/ajuster en live.
- Mirroring GitHub → GitLab (CI/CD) mis en place pour `skeletor` + les 5 modules
  (`.gitlab-ci.yml` / `.gitlabju-ci.yml` pour Skeletor spécifiquement, car son
  `.gitlab-ci.yml` d'origine est réservé à l'instance GitLab interne/SonarQube).

---

## 2. Demande du 22/10/2026 — Matériel (4 points)

Retours reçus pour le module Resa, concernant la réservation de matériel.

### Point 1 — Type de réservation "Matériel" visible dans les options
> Cibles SEMA, drones aériens (MP Chauvin à valider).

**Statut : largement déjà supporté techniquement** — le sélecteur de réservation propose
déjà "📦 Ressources matérielles" dès qu'une `Ressource` existe pour l'unité (via
`RessourceGanttWidget`, `Ressource` + `RessourceCategorie`). **Reste à faire : configuration**
(créer les catégories "Cibles SEMA", "Drones aériens" et les ressources correspondantes),
sauf si une UX différente est souhaitée (onglet dédié plutôt que noyé dans le sélecteur
générique — à clarifier).

### Point 2 — Champs obligatoires à la réservation de matériel
> Nom du bâtiment, service concerné, nom+grade de l'emprunteur, nom+grade de l'officier
> responsable.

**Statut : fait.**
- Migration `RessourceReservation` : `batiment`, `service` (texte libre), `emprunteur_user_id`
  et `officier_responsable_user_id` (FK `users`, nullable en base pour ne pas casser les
  réservations existantes).
- Les grades ne sont **pas stockés en dur** : résolus à l'affichage via
  `RessourceReservation::emprunteur_grade` / `officier_responsable_grade`
  (accessors qui appellent `Marin::fromUser()`), pour rester synchronisés si le grade change
  en base RH après la réservation.
- `RessourceGanttWidget::getNouvelleResaForm()` : 4 champs requis (bâtiment en texte libre,
  service en texte libre, emprunteur et officier en `Select` recherchable sur les
  utilisateurs). Obligatoires côté formulaire uniquement (pas de contrainte `NOT NULL` en
  base, pour ne pas bloquer d'éventuelles réservations créées par ailleurs).
- `getVoirResaForm()` : affiche bâtiment/service/emprunteur+grade/officier+grade dans le
  détail de la réservation.
- 3 tests Pest dédiés.

### Point 3 — Gestion de la disponibilité du matériel
**Statut : fait** (voir section 1.7 ci-dessus).

### Point 4 — Validation des réservations et droits "semi-admin"
> Validation/refus autonome. Semi-admin : modifier des noms, ajouter/retirer du matériel.

**Statut : fait pour le cas (a)** — validation/refus autonome par catégorie de matériel.
- `RessourceCategorie::gestionnaires()` + `isGestionnaire()` (même pattern que
  `Salle`/`Formation`).
- Permission `resa::gestionnaire_ressource`, attribuée/révoquée depuis
  `CreateRessourceCategorie`/`EditRessourceCategorie` (révocation symétrique : seulement si
  l'utilisateur ne gère plus aucune autre catégorie).
- Nouvelle **`RessourceReservationPolicy::valider()`** (admin, ou gestionnaire de la
  catégorie de la ressource réservée) — enregistrée dans `ResaServiceProvider`.
- `RessourceGanttWidget` : le bouton Valider/Invalider utilise désormais cette policy (plus
  seulement `adminMode`/admin complet) ; les réservations en attente du matériel d'une
  catégorie gérée sont visibles même si elles ne sont pas de l'utilisateur ; une réservation
  créée par un gestionnaire sur sa propre catégorie est **validée directement** (comme pour
  salle/formation).
- 8 tests Pest dédiés.

**Reste en attente de clarification — cas (b)** : le rôle "semi-admin" qui "modifie les
noms des **utilisateurs**" évoqué dans la demande initiale irait au-delà de la gestion du
matériel (toucherait aux fiches utilisateurs elles-mêmes, hors périmètre Resa, module
RH/comptes). **Non traité** — à valider avec le demandeur si ce besoin est toujours
d'actualité en plus du cas (a) déjà couvert.

---

## 3. Prochaines étapes proposées

1. Clarifier le point 4 cas (b) (droit d'édition sur les fiches utilisateurs — toujours
   demandé en plus du cas (a) déjà couvert ?).
2. Configurer les catégories/ressources SEMA et drones (point 1), une fois le périmètre UX
   confirmé.
3. Tester en conditions réelles l'outillage vidéo (`tools/video`) et publier les vidéos de
   présentation.
```

### resources/docs/README.md

```markdown
# Module Resa

## But du module

Resa gère la **réservation de salles et de matériels** au sein de l'application. Il fournit un calendrier de disponibilité, un circuit de demande/validation, et une gestion des ressources associées à chaque salle (postes de travail, capacités techniques, matériels).

## À quoi ça sert

- Réserver un créneau sur une salle (classique ou simulateur) via un calendrier interactif.
- Réserver du matériel indépendamment d'une salle (ex : matériel embarquable, équipement partagé), classé par catégories.
- Faire valider les réservations par un responsable avant qu'elles ne soient effectives.
- Rattacher les salles à la structure géographique/organisationnelle du module RH (lieu, unité), avec une navigation qui saute automatiquement les étapes inutiles quand il n'y a qu'un seul choix possible.
- Notifier par email le(s) responsable(s) d'une salle à chaque nouvelle réservation.

## Pour qui

| Profil | Ce qu'il peut faire |
|---|---|
| **Utilisateur standard** | Consulte le calendrier (public, même sans connexion), réserve une salle ou du matériel pour lui-même, modifie/supprime ses propres réservations tant qu'elles sont en attente. |
| **Gestionnaire de salle** (`resa::gestionnaire_salle`) | Gère uniquement les salles qui lui sont assignées : modifie la salle, ses postes et capacités, valide/invalide les réservations entrantes sur ses salles. |
| **Admin** (`resa::admin`) | Accès complet : toutes les salles, tout le matériel, toutes les réservations. Peut réserver pour le compte d'un tiers (réservation directement validée, avec traçabilité). |

Voir [`resources/docs/guide-utilisateur.md`](resources/docs/guide-utilisateur.md) pour le pas-à-pas détaillé (création de comptes, permissions, procédures de réservation/validation).

## Salles

Une **Salle** (`Salle`) représente un lieu réservable, de deux types (`TypeSalle`) :

- **Classique** : réservation d'un créneau sur la salle entière (pas de sélection de poste).
- **Simulateur** : réservation via un assistant en plusieurs étapes — choix des capacités nécessaires (ex : FPV, 20mm...), sélection d'un **poste** (`SallePoste`) parmi ceux compatibles et disponibles (triés par score de compatibilité avec les besoins exprimés), puis saisie de l'objet de la réservation.

Chaque salle peut être rattachée à une unité (module RH), avoir une image, une couleur, un ou plusieurs emails de notification, et un ou plusieurs gestionnaires.

Le rattachement suit la hiérarchie géographique du module RH : un **Lieu** (`LieuUnite`, ex : Toulon, Brest) regroupe plusieurs **Unités** (`Unite`). Le lieu et les unités doivent donc être créés dans le module RH *avant* de pouvoir les sélectionner comme rattachement d'une salle dans Resa — c'est l'unité (pas le lieu directement) qui est renseignée sur la salle.

## Matériels

Les **Ressources** (`Ressource`) représentent du matériel réservable indépendamment des salles, organisé en **catégories** (`RessourceCategorie`, ex : couleur/icône pour l'affichage). Chaque ressource peut être rattachée à une unité et activée/désactivée (`actif`). Les réservations de matériel (`RessourceReservation`) suivent le même principe de calendrier que les réservations de salle.

## Contraintes générales

- Réservations possibles du lundi au vendredi, 08h00–17h00, jamais dans le passé.
- Un utilisateur ne peut pas avoir deux créneaux qui se chevauchent.
- Toute réservation créée par un utilisateur standard est **en attente de validation** ; seul un admin réservant pour un tiers crée une réservation directement validée.
```

### resources/views/mail/reservation-validee.blade.php

```php
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #333; padding: 20px;">

<h2>Réservation validée</h2>

<p>La réservation suivante a été validée.</p>

<table style="border-collapse: collapse; margin-top: 16px;">
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Salle</td>
        <td>{{ $reservation->sallePoste?->salle?->libelle_court ?? $reservation->sallePoste?->libelle_court ?? '—' }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Du</td>
        <td>{{ $reservation->debut?->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Au</td>
        <td>{{ $reservation->fin?->format('d/m/Y H:i') }}</td>
    </tr>
    @if($reservation->libelle_court)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Objet</td>
        <td>{{ $reservation->libelle_court }}</td>
    </tr>
    @endif
    @if($reservation->user)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Réservé par</td>
        <td>{{ $reservation->user->display_name ?? $reservation->user->email }}</td>
    </tr>
    @endif
    @php($participants = $reservation->participantsNoms())
    @if($participants)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Stagiaires</td>
        <td>{{ implode(', ', $participants) }}</td>
    </tr>
    @endif
    @if($reservation->validatedBy)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Validée par</td>
        <td>{{ $reservation->validatedBy->display_name ?? $reservation->validatedBy->email }}</td>
    </tr>
    @endif
</table>

@foreach($consignes ?? [] as $formation => $notice)
<div style="margin-top: 20px; padding: 12px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px;">
    <p style="margin: 0 0 6px; font-weight: bold;">Consignes — {{ $formation }}</p>
    <p style="margin: 0; white-space: pre-line;">{{ $notice }}</p>
</div>
@endforeach

</body>
</html>
```

### resources/views/mail/reservation-supprimee.blade.php

```php
<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; color: #333; padding: 20px;">

<h2>Réservation annulée</h2>

<p>La réservation suivante a été supprimée.</p>

<table style="border-collapse: collapse; margin-top: 16px;">
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Salle</td>
        <td>{{ $reservation->sallePoste?->salle?->libelle_court ?? $reservation->sallePoste?->libelle_court ?? '—' }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Du</td>
        <td>{{ $reservation->debut?->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Au</td>
        <td>{{ $reservation->fin?->format('d/m/Y H:i') }}</td>
    </tr>
    @if($reservation->libelle_court)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Objet</td>
        <td>{{ $reservation->libelle_court }}</td>
    </tr>
    @endif
    @if($reservation->user)
    <tr>
        <td style="padding: 6px 16px 6px 0; font-weight: bold;">Réservé par</td>
        <td>{{ $reservation->user->display_name ?? $reservation->user->email }}</td>
    </tr>
    @endif
</table>

</body>
</html>
```

### resources/views/filament/widgets/reservation-widget.blade.php

```php
<x-filament-widgets::widget>

{{-- ─── PLUSIEURS SITES/UNITÉS : pas encore résolu, on passe par le modal ─────── --}}
@if(! $this->uniteActive)
<style>
.rw-empty{padding:2.5rem 1.5rem;display:flex;flex-direction:column;align-items:center;gap:0;}
.rw-empty-hero{width:100%;max-width:480px;margin:0 auto 2rem;display:flex;flex-direction:column;align-items:center;gap:1.5rem;}
.rw-empty-icon-wrap{width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(99,102,241,.35);}
.rw-empty-icon-wrap svg{width:40px;height:40px;color:#fff;}
.rw-empty-title{font-size:1.5rem;font-weight:700;color:#111827;margin:0;letter-spacing:-.02em;}
.dark .rw-empty-title{color:#f9fafb;}
.rw-empty-sub{font-size:.9375rem;color:#6b7280;margin:0;max-width:340px;line-height:1.6;}
.dark .rw-empty-sub{color:#9ca3af;}
.rw-steps{display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;justify-content:center;}
.rw-step{display:flex;align-items:flex-start;gap:.75rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:.875rem 1rem;width:140px;flex-direction:column;}
.dark .rw-step{background:#1e293b;border-color:#334155;}
.rw-step-num{width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);color:#fff;font-size:.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rw-step-label{font-size:.8125rem;font-weight:600;color:#374151;margin:0;}
.dark .rw-step-label{color:#e2e8f0;}
.rw-step-desc{font-size:.75rem;color:#9ca3af;margin:0;}
</style>

    <div class="rw-empty">
        <div class="rw-empty-hero">
            <div class="rw-empty-icon-wrap">
                <x-heroicon-o-calendar-days style="width:40px;height:40px;color:#fff;"/>
            </div>
            <div style="text-align:center;">
                <p class="rw-empty-title">Bienvenue sur Resa</p>
                <p class="rw-empty-sub" style="margin-top:.5rem;">Configurez votre espace de réservation en 2 étapes pour accéder au calendrier.</p>
            </div>
        </div>

        <div class="rw-steps">
            <div class="rw-step">
                <div class="rw-step-num">1</div>
                <p class="rw-step-label">Lieu & Unité</p>
                <p class="rw-step-desc">Choisissez votre site et unité</p>
            </div>
            <div class="rw-step">
                <div class="rw-step-num">2</div>
                <p class="rw-step-label">Type</p>
                <p class="rw-step-desc">Salle, drone ou ressource</p>
            </div>
        </div>

        <x-filament::button
            wire:click="mountAction('configurer')"
            icon="heroicon-o-adjustments-horizontal"
            size="lg"
            style="background:linear-gradient(135deg,#3b82f6,#6366f1);border:none;padding:.75rem 2rem;font-size:1rem;box-shadow:0 4px 14px rgba(99,102,241,.4);">
            Commencer la configuration
        </x-filament::button>
    </div>

{{-- ─── DASHBOARD : choix direct du type de réservation ───────────────────────── --}}
@elseif(! $this->typeResa)
<style>
.rw-dash{padding:2rem 1.5rem;}
.rw-dash-title{font-size:1.25rem;font-weight:700;color:#111827;margin:0 0 .25rem;}
.dark .rw-dash-title{color:#f9fafb;}
.rw-dash-sub{font-size:.875rem;color:#6b7280;margin:0 0 1.5rem;}
.dark .rw-dash-sub{color:#9ca3af;}
.rw-dash-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;}
.rw-dash-card{display:flex;flex-direction:column;align-items:flex-start;gap:.75rem;text-align:left;background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;cursor:pointer;transition:border-color .15s,box-shadow .15s;}
.rw-dash-card:hover{border-color:#6366f1;box-shadow:0 4px 14px rgba(99,102,241,.15);}
.dark .rw-dash-card{background:#1e293b;border-color:#334155;}
.rw-dash-icon{width:44px;height:44px;border-radius:10px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;}
.rw-dash-icon svg{width:22px;height:22px;color:#fff;}
.rw-dash-label{font-size:.9375rem;font-weight:600;color:#111827;margin:0;}
.dark .rw-dash-label{color:#f3f4f6;}
.rw-dash-tooltip{font-size:.75rem;color:#6b7280;margin:0;line-height:1.4;}
.dark .rw-dash-tooltip{color:#9ca3af;}
</style>

    <div class="rw-dash">
        <p class="rw-dash-title">Que voulez-vous réserver ?</p>
        <p class="rw-dash-sub">{{ $this->getUniteLabel() }}</p>

        <div class="rw-dash-grid">
            @foreach($this->choixTypes($this->uniteActive) as $type => $choix)
                <button type="button" wire:click="selectionnerType('{{ $type }}')" class="rw-dash-card">
                    <div class="rw-dash-icon">
                        <x-dynamic-component :component="$choix->icon" style="width:22px;height:22px;color:#fff;"/>
                    </div>
                    <p class="rw-dash-label">{{ $choix->label }}</p>
                    @if($choix->tooltip)
                        <p class="rw-dash-tooltip">{{ $choix->tooltip }}</p>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

{{-- ─── SÉLECTION FAITE ─────────────────────────────────────────────────────── --}}
@else

    {{-- Barre résumé : lieu/unité en stand-by (un seul site pour l'instant), seul le
         type de réservation reste un choix affiché/modifiable ici. --}}
    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2 mb-3 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center gap-4 text-sm text-gray-700 dark:text-gray-200">
            <span>
                <span class="block text-[0.65rem] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Type</span>
                <strong>{{ $this->getTypeLabel() }}</strong>
            </span>
        </div>
        <x-filament::button wire:click="changerType" color="gray" icon="heroicon-o-pencil-square" size="sm">
            Changer
        </x-filament::button>
    </div>

    {{-- Calendrier ou Gantt ressources --}}
    @if($this->typeResa === 'ressource')
        @livewire(\Modules\Resa\Filament\Widgets\RessourceGanttWidget::class,
            ['filtreUnite' => $this->uniteActive],
            key('rw-gantt-' . $this->uniteActive . '-' . $this->typeResa)
        )
    @else
        @livewire(\Modules\Resa\Filament\Widgets\CalendrierWidget::class,
            [
                'uniteActive'      => $this->uniteActive,
                'lieuActive'       => $this->lieuActive,
                'typeSalleActive'  => $this->getTypeSalleId(),
                'skipTypeSalleNav' => true,
                'modeForce'        => $this->typeResa === 'drone' ? 'simulateur' : 'classique',
            ],
            key('rw-cal-' . $this->uniteActive . '-' . $this->typeResa)
        )
    @endif

@endif

<x-filament-actions::modals/>

</x-filament-widgets::widget>
```

### resources/views/filament/widgets/suivi-formations-widget.blade.php

```php
<x-filament-widgets::widget>
<style>
.sfwi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.sfwi-title{display:flex;align-items:center;gap:.5rem;font-size:.9375rem;font-weight:600;color:var(--gray-900,#111827);}
.dark .sfwi-title{color:#fff;}
.sfwi-subtitle{font-size:.75rem;color:var(--gray-400,#9ca3af);}

.sfwi-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:.75rem;margin-bottom:1.25rem;}
.sfwi-stat{border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);background:#fff;padding:.875rem 1rem;}
.dark .sfwi-stat{border-color:#374151;background:#111827;}
.sfwi-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.sfwi-stat-value{font-size:1.5rem;font-weight:700;color:var(--gray-900,#111827);margin-top:.25rem;}
.dark .sfwi-stat-value{color:#fff;}
.sfwi-stat-warn .sfwi-stat-value{color:#b45309;}
.dark .sfwi-stat-warn .sfwi-stat-value{color:#fbbf24;}

.sfwi-tabs{display:flex;gap:.375rem;margin-bottom:1rem;flex-wrap:wrap;}
.sfwi-tab{border-radius:9999px;padding:.375rem 1rem;font-size:.8125rem;font-weight:600;border:1px solid var(--gray-300,#d1d5db);background:#fff;color:var(--gray-600,#4b5563);cursor:pointer;}
.dark .sfwi-tab{border-color:#4b5563;background:#1f2937;color:#d1d5db;}
.sfwi-tab-actif{background:#6366f1;border-color:#6366f1;color:#fff;}

.sfwi-filters{display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;}
.sfwi-filter{display:flex;flex-direction:column;gap:.25rem;}
.sfwi-label{font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.sfwi-select,.sfwi-input{border:1px solid var(--gray-300,#d1d5db);border-radius:.5rem;background:#fff;padding:.375rem .75rem;font-size:.875rem;color:var(--gray-700,#374151);box-shadow:0 1px 2px 0 rgba(0,0,0,.05);}
.dark .sfwi-select,.dark .sfwi-input{border-color:#4b5563;background:#1f2937;color:#e5e7eb;}
.sfwi-input{width:11rem;}
.sfwi-empty{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:2.5rem 0;color:#9ca3af;}
.sfwi-empty svg{width:2rem;height:2rem;}
.sfwi-empty p{font-size:.875rem;}
.sfwi-table-wrap{overflow-x:auto;border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);}
.dark .sfwi-table-wrap{border-color:#374151;}
.sfwi-table{width:100%;font-size:.8125rem;border-collapse:collapse;}
.sfwi-table thead tr{background:var(--gray-50,#f9fafb);}
.dark .sfwi-table thead tr{background:#1f2937;}
.sfwi-table th{padding:.625rem 1rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);text-align:left;white-space:nowrap;}
.sfwi-table tbody tr{border-top:1px solid var(--gray-100,#f3f4f6);background:#fff;}
.dark .sfwi-table tbody tr{border-color:#374151;background:#111827;}
.sfwi-table tbody tr:hover{background:var(--gray-50,#f9fafb);}
.dark .sfwi-table tbody tr:hover{background:#1f2937;}
.sfwi-table td{padding:.625rem 1rem;color:var(--gray-600,#4b5563);}
.dark .sfwi-table td{color:#d1d5db;}
.sfwi-name{font-weight:500;color:var(--gray-900,#111827);}
.dark .sfwi-name{color:#fff;}
.sfwi-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:9999px;padding:.125rem .625rem;font-size:.7rem;font-weight:600;}
.sfwi-badge-ok{background:#dcfce7;color:#15803d;}
.dark .sfwi-badge-ok{background:rgba(21,128,61,.2);color:#4ade80;}
.sfwi-badge-wait{background:#fef3c7;color:#b45309;}
.dark .sfwi-badge-wait{background:rgba(180,83,9,.2);color:#fbbf24;}
.sfwi-valby{font-size:.7rem;color:var(--gray-500,#6b7280);}
.sfwi-count{margin-top:.5rem;font-size:.7rem;color:var(--gray-400,#9ca3af);text-align:right;}

.sfwi-cal-wrap{margin-bottom:1.5rem;}
</style>

    {{-- Titre --}}
    <div class="sfwi-header">
        <div class="sfwi-title">
            <x-heroicon-o-academic-cap style="width:1.25rem;height:1.25rem;color:#6366f1;flex-shrink:0;"/>
            @if($this->isAdmin())
                Suivi des formations
            @else
                Mes formations
                <span class="sfwi-subtitle">— réservations à valider</span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ \Modules\Resa\Filament\Pages\ReservationsFormationPage::getUrl() }}" class="text-sm font-semibold text-primary-600 hover:underline">
                Voir tout →
            </a>
            <button type="button" wire:click="ouvrirNouvelleReservation" class="fi-btn fi-size-md fi-color-primary fi-btn-color-primary">
                <x-heroicon-o-plus style="width:1rem;height:1rem;"/>
                Nouvelle réservation
            </button>
        </div>
    </div>

    {{-- Onglets (uniquement si plusieurs formations gérées, hors admin) --}}
    @if($this->afficherOnglets())
        <div class="sfwi-tabs">
            <button type="button"
                    wire:click="selectionnerFormation(null)"
                    class="sfwi-tab {{ $this->filtreFormationId === null ? 'sfwi-tab-actif' : '' }}">
                Toutes
            </button>
            @foreach($this->getFormationsDisponibles() as $f)
                <button type="button"
                        wire:click="selectionnerFormation({{ $f->id }})"
                        class="sfwi-tab {{ $this->filtreFormationId === $f->id ? 'sfwi-tab-actif' : '' }}">
                    {{ $f->libelle_court }}
                </button>
            @endforeach
        </div>
    @endif

    {{-- Cartes statistiques --}}
    @php $stats = $this->getStats(); @endphp
    <div class="sfwi-stats">
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Formations gérées</div>
            <div class="sfwi-stat-value">{{ $stats['nb_formations'] }}</div>
        </div>
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Réservations à venir</div>
            <div class="sfwi-stat-value">{{ $stats['a_venir'] }}</div>
        </div>
        <div class="sfwi-stat sfwi-stat-warn">
            <div class="sfwi-stat-label">En attente de validation</div>
            <div class="sfwi-stat-value">{{ $stats['en_attente'] }}</div>
        </div>
        <div class="sfwi-stat">
            <div class="sfwi-stat-label">Réservations ce mois</div>
            <div class="sfwi-stat-value">{{ $stats['ce_mois'] }}</div>
        </div>
    </div>

    {{-- Calendrier (CalendrierWidget Guava, mode transversal filtré par formation) --}}
    <div class="sfwi-cal-wrap">
        @livewire(\Modules\Resa\Filament\Widgets\CalendrierWidget::class,
            [
                'modeFormationGlobale' => true,
                'filtreFormations'     => $this->getFormationIdsCalendrier(),
            ],
            key('sfwi-cal-' . implode('-', $this->getFormationIdsCalendrier()) . '-' . $this->calendarVersion)
        )
    </div>

    {{-- Filtres --}}
    <div class="sfwi-filters">

        @if($this->isAdmin())
            <div class="sfwi-filter">
                <label class="sfwi-label">Formation</label>
                <select wire:model.live="filtreFormationId" class="sfwi-select">
                    <option value="">Toutes</option>
                    @foreach($this->getFormationsDisponibles() as $f)
                        <option value="{{ $f->id }}">{{ $f->libelle_court }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="sfwi-filter">
            <label class="sfwi-label">Période</label>
            <select wire:model.live="filtrePeriode" class="sfwi-select">
                <option value="semaine">Cette semaine</option>
                <option value="mois">Ce mois</option>
                <option value="annee">Cette année</option>
                <option value="tout">Tout</option>
            </select>
        </div>

        <div class="sfwi-filter">
            <label class="sfwi-label">Statut</label>
            <select wire:model.live="filtreStatut" class="sfwi-select">
                <option value="">Tous</option>
                <option value="1">Validé</option>
                <option value="0">En attente</option>
            </select>
        </div>

        <div class="sfwi-filter">
            <label class="sfwi-label">Stagiaire</label>
            <input wire:model.live.debounce.300ms="filtreNom"
                   type="text"
                   placeholder="Nom ou prénom…"
                   class="sfwi-input"/>
        </div>

    </div>

    {{-- Tableau --}}
    @php $rows = $this->getReservations(); @endphp

    @if($rows->isEmpty())
        <div class="sfwi-empty">
            <x-heroicon-o-inbox style="width:2rem;height:2rem;"/>
            <p>Aucune réservation de formation trouvée.</p>
        </div>
    @else
        <div class="sfwi-table-wrap">
            <table class="sfwi-table">
                <thead>
                    <tr>
                        <th>Concerné(s)</th>
                        <th>Créé par</th>
                        <th>Formation</th>
                        <th>Poste</th>
                        <th>Salle</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                        <th>Présence</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr>
                            <td class="sfwi-name">
                                @forelse($r['stagiaires'] as $s)
                                    <div>{{ $s }}</div>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>{{ $r['cree_par'] ?? '—' }}</td>
                            <td>{{ $r['formation'] }}</td>
                            <td>{{ $r['poste'] }}</td>
                            <td>{{ $r['salle'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['debut'] }} – {{ $r['fin'] }}</td>
                            <td>
                                @if($r['valide'])
                                    <span class="sfwi-badge sfwi-badge-ok">Validé</span>
                                @else
                                    <span class="sfwi-badge sfwi-badge-wait">En attente</span>
                                @endif
                            </td>
                            <td class="sfwi-valby">
                                @if($r['validated_by'])
                                    {{ $r['validated_by'] }}<br>
                                    <span style="color:var(--gray-400,#9ca3af);">{{ $r['validated_at'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($r['peut_pointer'] && $r['presence'] === null)
                                    <div class="flex gap-1">
                                        <x-filament::button size="xs" color="success" wire:click="pointerGestionnaire({{ $r['id'] }}, true)">
                                            Présent
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:click="pointerGestionnaire({{ $r['id'] }}, false)">
                                            Absent
                                        </x-filament::button>
                                    </div>
                                @elseif($r['presence'] === true)
                                    <span class="sfwi-badge sfwi-badge-ok">Présent</span>
                                @elseif($r['presence'] === false)
                                    <span class="sfwi-badge" style="background:#fee2e2;color:#b91c1c;">Absent</span>
                                @else
                                    <span style="color:var(--gray-400,#9ca3af);">—</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <x-filament::button
                                    size="xs"
                                    color="gray"
                                    wire:click="mountAction('voirStagiaire', { reservationId: {{ $r['id'] }} })">
                                    Voir
                                </x-filament::button>
                                @if($r['peut_valider'])
                                    <x-filament::button
                                        size="xs"
                                        :color="$r['valide'] ? 'warning' : 'success'"
                                        wire:click="toggleValidation({{ $r['id'] }})"
                                        wire:confirm="{{ $r['valide'] ? 'Invalider cette réservation ?' : 'Valider cette réservation ?' }}">
                                        {{ $r['valide'] ? 'Invalider' : 'Valider' }}
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="sfwi-count">{{ $rows->count() }} résultat(s)</p>
    @endif

    <x-filament-actions::modals/>
</x-filament-widgets::widget>
```

### resources/views/filament/widgets/mes-reservations-widget.blade.php

```php
<x-filament-widgets::widget>
<style>
.mrwi-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.mrwi-title{display:flex;align-items:center;gap:.5rem;font-size:.9375rem;font-weight:600;color:var(--gray-900,#111827);}
.dark .mrwi-title{color:#fff;}

.mrwi-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(9rem,1fr));gap:.75rem;margin-bottom:1.25rem;}
.mrwi-stat{border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);background:#fff;padding:.875rem 1rem;}
.dark .mrwi-stat{border-color:#374151;background:#111827;}
.mrwi-stat-label{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);}
.mrwi-stat-value{font-size:1.5rem;font-weight:700;color:var(--gray-900,#111827);margin-top:.25rem;}
.dark .mrwi-stat-value{color:#fff;}
.mrwi-stat-warn .mrwi-stat-value{color:#b45309;}
.dark .mrwi-stat-warn .mrwi-stat-value{color:#fbbf24;}

.mrwi-filters{display:flex;gap:.5rem;margin-bottom:1rem;}
.mrwi-filter-btn{border-radius:9999px;padding:.3rem .875rem;font-size:.8125rem;font-weight:600;border:1px solid var(--gray-300,#d1d5db);background:#fff;color:var(--gray-600,#4b5563);cursor:pointer;}
.dark .mrwi-filter-btn{border-color:#4b5563;background:#1f2937;color:#d1d5db;}
.mrwi-filter-btn-actif{background:#6366f1;border-color:#6366f1;color:#fff;}

.mrwi-empty{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:2.5rem 0;color:#9ca3af;}
.mrwi-empty svg{width:2rem;height:2rem;}
.mrwi-empty p{font-size:.875rem;}

.mrwi-table-wrap{overflow-x:auto;border-radius:.75rem;border:1px solid var(--gray-200,#e5e7eb);}
.dark .mrwi-table-wrap{border-color:#374151;}
.mrwi-table{width:100%;font-size:.8125rem;border-collapse:collapse;}
.mrwi-table thead tr{background:var(--gray-50,#f9fafb);}
.dark .mrwi-table thead tr{background:#1f2937;}
.mrwi-table th{padding:.625rem 1rem;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--gray-500,#6b7280);text-align:left;white-space:nowrap;}
.mrwi-table tbody tr{border-top:1px solid var(--gray-100,#f3f4f6);background:#fff;}
.dark .mrwi-table tbody tr{border-color:#374151;background:#111827;}
.mrwi-table tbody tr:hover{background:var(--gray-50,#f9fafb);}
.dark .mrwi-table tbody tr:hover{background:#1f2937;}
.mrwi-table tbody tr.mrwi-passee{opacity:.55;}
.mrwi-table td{padding:.625rem 1rem;color:var(--gray-600,#4b5563);}
.dark .mrwi-table td{color:#d1d5db;}
.mrwi-name{font-weight:500;color:var(--gray-900,#111827);}
.dark .mrwi-name{color:#fff;}
.mrwi-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:9999px;padding:.125rem .625rem;font-size:.7rem;font-weight:600;}
.mrwi-badge-salle{background:#e0e7ff;color:#4338ca;}
.dark .mrwi-badge-salle{background:rgba(67,56,202,.25);color:#a5b4fc;}
.mrwi-badge-materiel{background:#fae8ff;color:#a21caf;}
.dark .mrwi-badge-materiel{background:rgba(162,28,175,.25);color:#f0abfc;}
.mrwi-badge-ok{background:#dcfce7;color:#15803d;}
.dark .mrwi-badge-ok{background:rgba(21,128,61,.2);color:#4ade80;}
.mrwi-badge-wait{background:#fef3c7;color:#b45309;}
.dark .mrwi-badge-wait{background:rgba(180,83,9,.2);color:#fbbf24;}
.mrwi-count{margin-top:.5rem;font-size:.7rem;color:var(--gray-400,#9ca3af);text-align:right;}
.mrwi-valby{font-size:.7rem;color:var(--gray-500,#6b7280);}
</style>

    <div class="mrwi-header">
        <div class="mrwi-title">
            <x-heroicon-o-calendar-days style="width:1.25rem;height:1.25rem;color:#6366f1;flex-shrink:0;"/>
            Mes réservations
        </div>
        @if($limite)
            <a href="{{ \Modules\Resa\Filament\Pages\MesReservationsPage::getUrl() }}" class="text-sm font-semibold text-primary-600 hover:underline">
                Voir tout →
            </a>
        @endif
    </div>

    @php $stats = $this->getStats(); @endphp
    <div class="mrwi-stats">
        <div class="mrwi-stat">
            <div class="mrwi-stat-label">Total</div>
            <div class="mrwi-stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="mrwi-stat">
            <div class="mrwi-stat-label">À venir</div>
            <div class="mrwi-stat-value">{{ $stats['a_venir'] }}</div>
        </div>
        <div class="mrwi-stat mrwi-stat-warn">
            <div class="mrwi-stat-label">En attente</div>
            <div class="mrwi-stat-value">{{ $stats['en_attente'] }}</div>
        </div>
    </div>

    <div class="mrwi-filters">
        <button type="button" wire:click="$set('filtreStatut', null)" class="mrwi-filter-btn {{ $filtreStatut === null ? 'mrwi-filter-btn-actif' : '' }}">Toutes</button>
        <button type="button" wire:click="$set('filtreStatut', 1)" class="mrwi-filter-btn {{ $filtreStatut === 1 ? 'mrwi-filter-btn-actif' : '' }}">Validées</button>
        <button type="button" wire:click="$set('filtreStatut', 0)" class="mrwi-filter-btn {{ $filtreStatut === 0 ? 'mrwi-filter-btn-actif' : '' }}">En attente</button>
    </div>

    @php $rows = $this->getReservationsAffichees(); @endphp

    @if($rows->isEmpty())
        <div class="mrwi-empty">
            <x-heroicon-o-inbox style="width:2rem;height:2rem;"/>
            <p>Vous n'avez aucune réservation pour le moment.</p>
        </div>
    @else
        <div class="mrwi-table-wrap">
            <table class="mrwi-table">
                <thead>
                    <tr>
                        <th>Concerné(s)</th>
                        <th>Créé par</th>
                        <th>Type</th>
                        <th>Formation</th>
                        <th>Poste</th>
                        <th>Salle / Matériel</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Validé par</th>
                        <th>Présence</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        <tr class="{{ $r['passee'] ? 'mrwi-passee' : '' }}">
                            <td class="mrwi-name">
                                @forelse($r['concernes'] as $c)
                                    <div>{{ $c }}</div>
                                @empty
                                    {{ $r['libelle'] }}
                                @endforelse
                            </td>
                            <td>{{ $r['cree_par'] ?? '—' }}</td>
                            <td>
                                @if($r['type'] === 'salle')
                                    <span class="mrwi-badge mrwi-badge-salle">Salle</span>
                                @else
                                    <span class="mrwi-badge mrwi-badge-materiel">Matériel</span>
                                @endif
                            </td>
                            <td>{{ $r['formation'] }}</td>
                            <td>{{ $r['poste'] }}</td>
                            <td>{{ $r['detail'] }}</td>
                            <td style="white-space:nowrap;">{{ $r['debut_fmt'] }} – {{ $r['fin_fmt'] }}</td>
                            <td>
                                @if($r['valide'])
                                    <span class="mrwi-badge mrwi-badge-ok">Validée</span>
                                @else
                                    <span class="mrwi-badge mrwi-badge-wait">En attente</span>
                                @endif
                            </td>
                            <td class="mrwi-valby">
                                @if($r['validated_by'])
                                    {{ $r['validated_by'] }}<br>
                                    <span style="color:var(--gray-400,#9ca3af);">{{ $r['validated_at'] }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($r['peut_pointer'] && $r['presence'] === null)
                                    <div class="flex gap-1">
                                        <x-filament::button size="xs" color="success" wire:click="pointer({{ $r['id'] }}, true)">
                                            Présent
                                        </x-filament::button>
                                        <x-filament::button size="xs" color="danger" wire:click="pointer({{ $r['id'] }}, false)">
                                            Absent
                                        </x-filament::button>
                                    </div>
                                @elseif($r['presence'] === true)
                                    <span class="mrwi-badge mrwi-badge-ok">Présent</span>
                                @elseif($r['presence'] === false)
                                    <span class="mrwi-badge mrwi-badge-wait" style="background:#fee2e2;color:#b91c1c;">Absent</span>
                                @else
                                    <span style="color:#9ca3af;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($r['peut_modifier'])
                                    <x-filament::button
                                        size="xs"
                                        color="danger"
                                        wire:click="supprimer('{{ $r['type'] }}', {{ $r['id'] }})"
                                        wire:confirm="Supprimer cette réservation ?">
                                        Supprimer
                                    </x-filament::button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mrwi-count">
            @if($limite && $stats['total'] > $rows->count())
                {{ $rows->count() }} sur {{ $stats['total'] }} résultat(s)
            @else
                {{ $rows->count() }} résultat(s)
            @endif
        </p>
    @endif

</x-filament-widgets::widget>
```

### resources/views/filament/pages/mes-reservations-page.blade.php

```php
<x-filament-panels::page>

</x-filament-panels::page>
```

### resources/views/filament/pages/reservations-formation-page.blade.php

```php
<x-filament-panels::page>

</x-filament-panels::page>
```

### tests/Feature/RessourcesEtGestionnairesTest.php

```php
<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\NouvelleResaAction;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\RessourceReservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Resa');

/* ══════════════════════════════════════════════════════════════════════════════
 * SETUP COMMUN
 * ══════════════════════════════════════════════════════════════════════════════ */

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('resa'));

    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::gestionnaire_salle', 'web');

    $this->user  = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::admin');
    $this->admin->givePermissionTo('resa::gestionnaire_salle');

    $this->seed(ResaDatabaseSeeder::class);

    $this->lieu  = LieuUnite::create(['libelle_court' => 'Toulon', 'libelle_long' => 'Toulon', 'ordre' => 1]);
    $this->unite = Unite::create(['libelle_court' => 'ERM', 'libelle_long' => 'ERM', 'ordre' => 1, 'lieu_unite_id' => $this->lieu->id]);

    $typeSalleClassique = TypeSalle::where('mode', TypeSalle::MODE_CLASSIQUE)->firstOrFail();

    $this->salle = Salle::create([
        'libelle_court' => 'Salle Test',
        'type_salle_id' => $typeSalleClassique->id,
        'unite_id'      => $this->unite->id,
        'capacite'      => 10,
        'ordre'         => 1,
    ]);

    $categorie = RessourceCategorie::create([
        'libelle'       => 'Matériel',
        'libelle_court' => 'MAT',
        'ordre'         => 1,
    ]);

    $this->ressource = Ressource::create([
        'ressource_categorie_id' => $categorie->id,
        'libelle'                => 'Ordinateur portable',
        'libelle_court'          => 'PC',
        'ordre'                  => 1,
        'actif'                  => true,
    ]);

    $this->debut = now()->addDay()->setTime(9, 0)->toDateTimeString();
    $this->fin   = now()->addDay()->setTime(11, 0)->toDateTimeString();
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RESSOURCES MATÉRIELLES — Modèle RessourceReservation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceReservation : modèle', function () {

    it('est créée en attente par défaut', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        expect($resa->isEnAttente())->toBeTrue();
        expect($resa->isValide())->toBeFalse();
        expect($resa->validation)->toBe(RessourceReservation::EN_ATTENTE);
    });

    it('valider() passe la réservation à VALIDE et enregistre le validateur', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $resa->valider($this->admin);
        $resa->refresh();

        expect($resa->isValide())->toBeTrue();
        expect($resa->validated_by)->toBe($this->admin->id);
        expect($resa->validated_at)->not->toBeNull();
    });

    it('invalider() repasse la réservation EN_ATTENTE et efface le validateur', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::VALIDE,
            'validated_by' => $this->admin->id,
            'validated_at' => now(),
        ]);

        $resa->invalider();
        $resa->refresh();

        expect($resa->isEnAttente())->toBeTrue();
        expect($resa->validated_by)->toBeNull();
        expect($resa->validated_at)->toBeNull();
    });

    it('couleur() retourne vert si validée, orange sinon', function () {
        $enAttente = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $valide = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'fin'          => now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'validation'   => RessourceReservation::VALIDE,
        ]);

        expect($enAttente->couleur())->toBe('#f59e0b');
        expect($valide->couleur())->toBe('#22c55e');
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RESSOURCES MATÉRIELLES — Widget RessourceGanttWidget
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceGanttWidget : protection auth', function () {

    it('creerReservation() ne crée rien si non connecté', function () {
        $widget = new RessourceGanttWidget();

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Test non auth',
        ]);

        expect(RessourceReservation::count())->toBe(0);
    });

    it('creerReservation() crée la réservation EN_ATTENTE pour un utilisateur normal', function () {
        $this->actingAs($this->user);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Réservation test',
        ]);

        $resa = RessourceReservation::first();
        expect($resa)->not->toBeNull();
        expect($resa->user_id)->toBe($this->user->id);
        expect($resa->validation)->toBe(RessourceReservation::EN_ATTENTE);
    });

    it('creerReservation() crée la réservation VALIDE directement pour un admin', function () {
        $this->actingAs($this->admin);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = true;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Admin resa',
        ]);

        $resa = RessourceReservation::first();
        expect($resa->validation)->toBe(RessourceReservation::VALIDE);
        expect($resa->validated_by)->toBe($this->admin->id);
    });

    it('creerReservation() bloque si conflit détecté', function () {
        $this->actingAs($this->user);

        // Créer une réservation existante sur le même créneau
        RessourceReservation::create([
            'user_id'      => $this->user2->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::VALIDE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Conflit',
        ]);

        // Il ne doit y avoir que la réservation initiale, pas de doublon
        expect(RessourceReservation::count())->toBe(1);
    });
});

describe('RessourceGanttWidget : adminMode', function () {

    it('adminMode est true pour un utilisateur ayant resa::admin', function () {
        $this->actingAs($this->admin);

        $widget = new RessourceGanttWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est false pour un utilisateur sans permission', function () {
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeFalse();
    });

    it('un non-connecté ne peut pas déplacer une réservation (logique adminMode + user_id)', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        // Sans authentification : adminMode = false, auth()->id() = null
        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        // Reproduire la condition de onEventDrop sans invoquer la méthode typée
        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeFalse();
    });

    it('un admin peut déplacer la réservation d\'un autre utilisateur', function () {
        $this->actingAs($this->admin);

        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,   // réservation de $user, pas $admin
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = true;

        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeTrue();
    });

    it('un utilisateur normal peut déplacer sa propre réservation (non validée)', function () {
        $this->actingAs($this->user);

        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — Modèle Salle
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Salle : isGestionnaire()', function () {

    it('retourne false si l\'utilisateur n\'est pas gestionnaire', function () {
        expect($this->salle->isGestionnaire($this->user))->toBeFalse();
    });

    it('retourne true après avoir ajouté l\'utilisateur comme gestionnaire', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        expect($this->salle->isGestionnaire($this->user))->toBeTrue();
    });

    it('retourne false pour un autre utilisateur non gestionnaire', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        expect($this->salle->isGestionnaire($this->user2))->toBeFalse();
    });

    it('plusieurs gestionnaires peuvent être attachés', function () {
        $this->salle->gestionnaires()->attach([$this->user->id, $this->user2->id]);

        expect($this->salle->isGestionnaire($this->user))->toBeTrue();
        expect($this->salle->isGestionnaire($this->user2))->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — CalendrierWidget adminMode
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CalendrierWidget : adminMode pour gestionnaire', function () {

    it('adminMode est true si utilisateur a resa::gestionnaire_salle', function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est true si utilisateur a resa::reservation.view', function () {
        $this->user->givePermissionTo('resa::reservation.view');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est false si utilisateur n\'a aucune permission admin', function () {
        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeFalse();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — CreateReservation auto-validation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CreateReservation : auto-validation gestionnaire de salle', function () {

    beforeEach(function () {
        $typePoste = TypePoste::firstOrFail();

        $this->sallePoste = SallePoste::create([
            'salle_id'      => $this->salle->id,
            'libelle_court' => 'Poste principal',
            'capacite'      => 5,
            'ordre'         => 1,
            'type_poste_id' => $typePoste->id,
        ]);

        $this->baseData = [
            'participants'         => [['user_id' => $this->user->id]],
            'salle_poste_id'       => $this->sallePoste->id,
            'libelle_court'        => 'Réunion test',
            'debut'                => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'fin'                  => now()->addDay()->setTime(11, 0)->toDateTimeString(),
            'formations_utilisees' => [],
            'data'                 => [],
        ];
    });

    it('un utilisateur normal ne reçoit pas la validation automatique', function () {
        $this->actingAs($this->user);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'] ?? Reservation::EN_ATTENTE)->toBe(Reservation::EN_ATTENTE);
    });

    it('un gestionnaire de salle reçoit la validation automatique', function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->salle->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'])->toBe(Reservation::VALIDE);
        expect($result['validated_by'])->toBe($this->user->id);
    });

    it('un admin reçoit la validation automatique', function () {
        $this->actingAs($this->admin);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'])->toBe(Reservation::VALIDE);
    });

    it('un gestionnaire d\'une autre salle ne reçoit pas la validation', function () {
        $autreSalle = Salle::create([
            'libelle_court' => 'Autre salle',
            'type_salle_id' => $this->salle->type_salle_id,
            'unite_id'      => $this->unite->id,
            'capacite'      => 5,
            'ordre'         => 2,
        ]);

        // user est gestionnaire de autreSalle seulement
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $autreSalle->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);

        // La réservation est pour $this->salle (via $this->sallePoste)
        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'] ?? Reservation::EN_ATTENTE)->toBe(Reservation::EN_ATTENTE);
    });
});
```

### tests/Feature/ResaTest.php

```php
<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\NouvelleResaAction;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\RessourceReservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Resa');

/* ══════════════════════════════════════════════════════════════════════════════
 * SETUP COMMUN
 * ══════════════════════════════════════════════════════════════════════════════ */

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('resa'));

    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::gestionnaire_salle', 'web');

    $this->user  = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::admin');
    $this->admin->givePermissionTo('resa::gestionnaire_salle');

    $this->seed(ResaDatabaseSeeder::class);

    $this->lieu  = LieuUnite::create(['libelle_court' => 'Toulon', 'libelle_long' => 'Toulon', 'ordre' => 1]);
    $this->unite = Unite::create(['libelle_court' => 'ERM', 'libelle_long' => 'ERM', 'ordre' => 1, 'lieu_unite_id' => $this->lieu->id]);

    $typeSalleClassique = TypeSalle::where('mode', TypeSalle::MODE_CLASSIQUE)->firstOrFail();

    $this->salle = Salle::create([
        'libelle_court' => 'Salle Test',
        'type_salle_id' => $typeSalleClassique->id,
        'unite_id'      => $this->unite->id,
        'capacite'      => 10,
        'ordre'         => 1,
    ]);

    $categorie = RessourceCategorie::create([
        'libelle'       => 'Matériel',
        'libelle_court' => 'MAT',
        'ordre'         => 1,
    ]);

    $this->ressource = Ressource::create([
        'ressource_categorie_id' => $categorie->id,
        'libelle'                => 'Ordinateur portable',
        'libelle_court'          => 'PC',
        'ordre'                  => 1,
        'actif'                  => true,
    ]);

    $this->debut = now()->addDay()->setTime(9, 0)->toDateTimeString();
    $this->fin   = now()->addDay()->setTime(11, 0)->toDateTimeString();
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RESSOURCES MATÉRIELLES — Modèle RessourceReservation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceReservation : modèle', function () {

    it('est créée en attente par défaut', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        expect($resa->isEnAttente())->toBeTrue();
        expect($resa->isValide())->toBeFalse();
        expect($resa->validation)->toBe(RessourceReservation::EN_ATTENTE);
    });

    it('valider() passe la réservation à VALIDE et enregistre le validateur', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $resa->valider($this->admin);
        $resa->refresh();

        expect($resa->isValide())->toBeTrue();
        expect($resa->validated_by)->toBe($this->admin->id);
        expect($resa->validated_at)->not->toBeNull();
    });

    it('invalider() repasse la réservation EN_ATTENTE et efface le validateur', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::VALIDE,
            'validated_by' => $this->admin->id,
            'validated_at' => now(),
        ]);

        $resa->invalider();
        $resa->refresh();

        expect($resa->isEnAttente())->toBeTrue();
        expect($resa->validated_by)->toBeNull();
        expect($resa->validated_at)->toBeNull();
    });

    it('couleur() retourne vert si validée, orange sinon', function () {
        $enAttente = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $valide = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'fin'          => now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'validation'   => RessourceReservation::VALIDE,
        ]);

        expect($enAttente->couleur())->toBe('#f59e0b');
        expect($valide->couleur())->toBe('#22c55e');
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RESSOURCES MATÉRIELLES — Widget RessourceGanttWidget
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceGanttWidget : protection auth', function () {

    it('creerReservation() ne crée rien si non connecté', function () {
        $widget = new RessourceGanttWidget();

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Test non auth',
        ]);

        expect(RessourceReservation::count())->toBe(0);
    });

    it('creerReservation() crée la réservation EN_ATTENTE pour un utilisateur normal', function () {
        $this->actingAs($this->user);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Réservation test',
        ]);

        $resa = RessourceReservation::first();
        expect($resa)->not->toBeNull();
        expect($resa->user_id)->toBe($this->user->id);
        expect($resa->validation)->toBe(RessourceReservation::EN_ATTENTE);
    });

    it('creerReservation() crée la réservation VALIDE directement pour un admin', function () {
        $this->actingAs($this->admin);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = true;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Admin resa',
        ]);

        $resa = RessourceReservation::first();
        expect($resa->validation)->toBe(RessourceReservation::VALIDE);
        expect($resa->validated_by)->toBe($this->admin->id);
    });

    it('creerReservation() bloque si conflit détecté', function () {
        $this->actingAs($this->user);

        // Créer une réservation existante sur le même créneau
        RessourceReservation::create([
            'user_id'      => $this->user2->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::VALIDE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $creer = fn (array $data) => (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))
            ->invoke(null, $widget, $data);

        $creer([
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Conflit',
        ]);

        // Il ne doit y avoir que la réservation initiale, pas de doublon
        expect(RessourceReservation::count())->toBe(1);
    });
});

describe('RessourceGanttWidget : adminMode', function () {

    it('adminMode est true pour un utilisateur ayant resa::admin', function () {
        $this->actingAs($this->admin);

        $widget = new RessourceGanttWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est false pour un utilisateur sans permission', function () {
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeFalse();
    });

    it('un non-connecté ne peut pas déplacer une réservation (logique adminMode + user_id)', function () {
        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        // Sans authentification : adminMode = false, auth()->id() = null
        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        // Reproduire la condition de onEventDrop sans invoquer la méthode typée
        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeFalse();
    });

    it('un admin peut déplacer la réservation d\'un autre utilisateur', function () {
        $this->actingAs($this->admin);

        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,   // réservation de $user, pas $admin
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = true;

        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeTrue();
    });

    it('un utilisateur normal peut déplacer sa propre réservation (non validée)', function () {
        $this->actingAs($this->user);

        $resa = RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        $widget            = new RessourceGanttWidget();
        $widget->adminMode = false;

        $peutDeplacer = $widget->adminMode || $resa->user_id === auth()->id();

        expect($peutDeplacer)->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — Modèle Salle
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Salle : isGestionnaire()', function () {

    it('retourne false si l\'utilisateur n\'est pas gestionnaire', function () {
        expect($this->salle->isGestionnaire($this->user))->toBeFalse();
    });

    it('retourne true après avoir ajouté l\'utilisateur comme gestionnaire', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        expect($this->salle->isGestionnaire($this->user))->toBeTrue();
    });

    it('retourne false pour un autre utilisateur non gestionnaire', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        expect($this->salle->isGestionnaire($this->user2))->toBeFalse();
    });

    it('plusieurs gestionnaires peuvent être attachés', function () {
        $this->salle->gestionnaires()->attach([$this->user->id, $this->user2->id]);

        expect($this->salle->isGestionnaire($this->user))->toBeTrue();
        expect($this->salle->isGestionnaire($this->user2))->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — CalendrierWidget adminMode
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CalendrierWidget : adminMode pour gestionnaire', function () {

    it('adminMode est true si utilisateur a resa::gestionnaire_salle', function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est true si utilisateur a resa::reservation.view', function () {
        $this->user->givePermissionTo('resa::reservation.view');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });

    it('adminMode est false si utilisateur n\'a aucune permission admin', function () {
        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeFalse();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * GESTIONNAIRES DE SALLES — CreateReservation auto-validation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CreateReservation : auto-validation gestionnaire de salle', function () {

    beforeEach(function () {
        $typePoste = TypePoste::firstOrFail();

        $this->sallePoste = SallePoste::create([
            'salle_id'      => $this->salle->id,
            'libelle_court' => 'Poste principal',
            'capacite'      => 5,
            'ordre'         => 1,
            'type_poste_id' => $typePoste->id,
        ]);

        $this->baseData = [
            'participants'         => [['user_id' => $this->user->id]],
            'salle_poste_id'       => $this->sallePoste->id,
            'libelle_court'        => 'Réunion test',
            'debut'                => now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'fin'                  => now()->addDay()->setTime(11, 0)->toDateTimeString(),
            'formations_utilisees' => [],
            'data'                 => [],
        ];
    });

    it('un utilisateur normal ne reçoit pas la validation automatique', function () {
        $this->actingAs($this->user);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'] ?? Reservation::EN_ATTENTE)->toBe(Reservation::EN_ATTENTE);
    });

    it('un gestionnaire de salle reçoit la validation automatique', function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->salle->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'])->toBe(Reservation::VALIDE);
        expect($result['validated_by'])->toBe($this->user->id);
    });

    it('un admin reçoit la validation automatique', function () {
        $this->actingAs($this->admin);

        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'])->toBe(Reservation::VALIDE);
    });

    it('un gestionnaire d\'une autre salle ne reçoit pas la validation', function () {
        $autreSalle = Salle::create([
            'libelle_court' => 'Autre salle',
            'type_salle_id' => $this->salle->type_salle_id,
            'unite_id'      => $this->unite->id,
            'capacite'      => 5,
            'ordre'         => 2,
        ]);

        // user est gestionnaire de autreSalle seulement
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $autreSalle->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);

        // La réservation est pour $this->salle (via $this->sallePoste)
        $result = \Closure::bind(
            fn ($data) => $this->mutateFormDataBeforeCreate($data),
            new CreateReservation(),
            CreateReservation::class
        )($this->baseData);

        expect($result['validation'] ?? Reservation::EN_ATTENTE)->toBe(Reservation::EN_ATTENTE);
    });
});
```

### tests/Feature/GestionFormationTest.php

```php
<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Modules\RH\Models\Grade;
use Modules\Resa\Filament\Components\EmailListField;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Marin;
use Modules\RH\Models\Unite;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\CreateRessourceCategorie;
use Modules\Resa\Filament\Resources\RessourceCategorieResource\Pages\EditRessourceCategorie;
use Modules\Resa\Filament\Resources\RessourceReservationResource;
use Modules\Resa\Filament\Resources\SalleResource\Pages\CreateSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages\EditSalle;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\CreateTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\EditTypeCapacite;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\MesReservationsWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\VoirResaAction;
use Modules\Resa\Filament\Widgets\ReservationWidget;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget\Actions\VoirStagiaireAction;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\RessourceReservation;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Resa');

/* ══════════════════════════════════════════════════════════════════════════════
 * SETUP COMMUN
 * ══════════════════════════════════════════════════════════════════════════════ */

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('resa'));

    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::gestionnaire_salle', 'web');
    Permission::findOrCreate('resa::gestionnaire_formation', 'web');
    Permission::findOrCreate('resa::gestionnaire_ressource', 'web');

    $this->user  = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::admin');

    $this->seed(ResaDatabaseSeeder::class);

    $this->lieu  = LieuUnite::create(['libelle_court' => 'Toulon', 'libelle_long' => 'Toulon', 'ordre' => 1]);
    $this->unite = Unite::create(['libelle_court' => 'ERM', 'libelle_long' => 'ERM', 'ordre' => 1, 'lieu_unite_id' => $this->lieu->id]);

    $typeSalleSimulateur = TypeSalle::where('mode', TypeSalle::MODE_SIMULATEUR)->firstOrFail();

    $this->salle = Salle::create([
        'libelle_court' => 'Salle Simulateur',
        'type_salle_id' => $typeSalleSimulateur->id,
        'unite_id'      => $this->unite->id,
        'capacite'      => 10,
        'ordre'         => 1,
    ]);

    $typePoste = TypePoste::firstOrFail();

    $this->poste = SallePoste::create([
        'salle_id'      => $this->salle->id,
        'libelle_court' => 'Poste 1',
        'capacite'      => 1,
        'ordre'         => 1,
        'type_poste_id' => $typePoste->id,
    ]);

    $this->formation = Formation::create([
        'libelle_court' => 'FPV Test',
        'ordre'         => 10,
    ]);

    // Le poste doit être explicitement configuré pour la formation (CheckboxList
    // "Formations disponibles sur ce poste" dans Resa > Postes de salle) : les colonnes
    // du calendrier transversal se basent sur cette configuration, pas sur l'historique
    // des réservations (cf. CalendrierWidget::getResourcesFormationGlobale()).
    $this->poste->formations()->attach($this->formation->id);

    $this->debut = now()->addDay()->setTime(9, 0)->toDateTimeString();
    $this->fin   = now()->addDay()->setTime(11, 0)->toDateTimeString();

    $this->reservation = Reservation::create([
        'user_id'              => $this->user->id,
        'salle_poste_id'       => $this->poste->id,
        'libelle_court'        => 'Session FPV',
        'debut'                => $this->debut,
        'fin'                  => $this->fin,
        'validation'           => Reservation::EN_ATTENTE,
        'formations_utilisees' => [$this->formation->id],
    ]);

    $this->categorie = RessourceCategorie::create([
        'libelle'       => 'Matériel',
        'libelle_court' => 'MAT',
        'ordre'         => 1,
    ]);

    $this->ressource = Ressource::create([
        'ressource_categorie_id' => $this->categorie->id,
        'libelle'                => 'Casque VR',
        'libelle_court'          => 'VR',
        'ordre'                  => 1,
        'actif'                  => true,
    ]);
});

/* ══════════════════════════════════════════════════════════════════════════════
 * Formation : isGestionnaire()
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Formation : isGestionnaire()', function () {

    it('retourne false si l\'utilisateur n\'est pas gestionnaire', function () {
        expect($this->formation->isGestionnaire($this->user))->toBeFalse();
    });

    it('retourne true après avoir attaché l\'utilisateur comme gestionnaire', function () {
        $this->formation->gestionnaires()->attach($this->user->id);

        expect($this->formation->isGestionnaire($this->user))->toBeTrue();
    });

    it('ne rend pas gestionnaire un autre utilisateur non attaché', function () {
        $this->formation->gestionnaires()->attach($this->user->id);

        expect($this->formation->isGestionnaire($this->user2))->toBeFalse();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * ReservationPolicy : valider()
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationPolicy : valider()', function () {

    it('un admin peut toujours valider', function () {
        expect($this->admin->can('valider', $this->reservation))->toBeTrue();
    });

    it('un gestionnaire de la salle concernée peut valider', function () {
        $this->salle->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        expect($this->user2->can('valider', $this->reservation))->toBeTrue();
    });

    it('un gestionnaire d\'une autre salle ne peut pas valider', function () {
        $autreSalle = Salle::create([
            'libelle_court' => 'Autre salle',
            'type_salle_id' => $this->salle->type_salle_id,
            'unite_id'      => $this->unite->id,
            'ordre'         => 2,
        ]);
        $autreSalle->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        expect($this->user2->can('valider', $this->reservation))->toBeFalse();
    });

    it('un gestionnaire de la formation utilisée peut valider', function () {
        $this->formation->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        expect($this->user2->can('valider', $this->reservation))->toBeTrue();
    });

    it('un gestionnaire d\'une autre formation ne peut pas valider', function () {
        $autreFormation = Formation::create(['libelle_court' => 'Autre', 'ordre' => 11]);
        $autreFormation->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        expect($this->user2->can('valider', $this->reservation))->toBeFalse();
    });

    it('un utilisateur sans aucune permission ne peut pas valider', function () {
        expect($this->user2->can('valider', $this->reservation))->toBeFalse();
    });

    it('un gestionnaire de formation ne peut pas valider une réservation sans formation', function () {
        $reservationSansFormation = Reservation::create([
            'user_id'              => $this->user->id,
            'salle_poste_id'       => $this->poste->id,
            'libelle_court'        => 'Réunion classique',
            'debut'                => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'fin'                  => now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'validation'           => Reservation::EN_ATTENTE,
            'formations_utilisees' => [],
        ]);

        $this->formation->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        expect($this->user2->can('valider', $reservationSansFormation))->toBeFalse();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * CreateTypeCapacite / EditTypeCapacite : attribution et révocation de permission
 * ══════════════════════════════════════════════════════════════════════════════ */

/*
 * Les hooks afterCreate()/afterSave() sont invoqués directement (sans passer par
 * Livewire::test()->fillForm()) : dans cet environnement, les tests Livewire basés
 * sur fillForm() échouent systématiquement même sur des fichiers de test préexistants
 * et non modifiés (ex. ResourcesFormTest.php) — problème d'infrastructure de test
 * indépendant de ces changements. On teste donc la logique métier isolément, comme
 * le fait déjà RessourcesEtGestionnairesTest.php pour CreateReservation.
 */
describe('TypeCapacite (Formation) : permission resa::gestionnaire_formation', function () {

    it('attribue la permission au gestionnaire choisi à la création', function () {
        $this->formation->gestionnaires()->attach($this->user->id);

        $page = new CreateTypeCapacite();
        $page->record = $this->formation;

        \Closure::bind(fn () => $this->afterCreate(), $page, CreateTypeCapacite::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_formation'))->toBeTrue();
    });

    it('attribue la permission à un nouveau gestionnaire ajouté en édition', function () {
        $this->formation->gestionnaires()->attach($this->user->id);

        $page = new EditTypeCapacite();
        $page->record = $this->formation;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditTypeCapacite::class)([]);

        \Closure::bind(fn () => $this->afterSave(), $page, EditTypeCapacite::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_formation'))->toBeTrue();
    });

    it('révoque la permission quand le gestionnaire est retiré et ne gère plus aucune formation', function () {
        $this->formation->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditTypeCapacite();
        $page->record = $this->formation;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditTypeCapacite::class)([]);

        // L'admin retire le gestionnaire du formulaire avant de sauvegarder.
        $this->formation->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditTypeCapacite::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_formation'))->toBeFalse();
    });

    it('conserve la permission si le gestionnaire retiré gère encore une autre formation', function () {
        $autreFormation = Formation::create(['libelle_court' => 'Autre formation', 'ordre' => 12]);
        $autreFormation->gestionnaires()->attach($this->user->id);

        $this->formation->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditTypeCapacite();
        $page->record = $this->formation;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditTypeCapacite::class)([]);

        $this->formation->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditTypeCapacite::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_formation'))->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * CreateSalle / EditSalle : attribution et révocation de resa::gestionnaire_salle
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Salle : permission resa::gestionnaire_salle', function () {

    it('attribue la permission au gestionnaire choisi à la création', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        $page = new CreateSalle();
        $page->record = $this->salle;

        \Closure::bind(fn () => $this->afterCreate(), $page, CreateSalle::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_salle'))->toBeTrue();
    });

    it('attribue la permission à un nouveau gestionnaire ajouté en édition', function () {
        $this->salle->gestionnaires()->attach($this->user->id);

        $page = new EditSalle();
        $page->record = $this->salle;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditSalle::class)([]);

        \Closure::bind(fn () => $this->afterSave(), $page, EditSalle::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_salle'))->toBeTrue();
    });

    it('révoque la permission quand le gestionnaire est retiré et ne gère plus aucune salle', function () {
        $this->salle->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditSalle();
        $page->record = $this->salle;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditSalle::class)([]);

        $this->salle->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditSalle::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_salle'))->toBeFalse();
    });

    it('conserve la permission si le gestionnaire retiré gère encore une autre salle', function () {
        $autreSalle = Salle::create([
            'libelle_court' => 'Autre salle',
            'type_salle_id' => $this->salle->type_salle_id,
            'unite_id'      => $this->unite->id,
            'ordre'         => 2,
        ]);
        $autreSalle->gestionnaires()->attach($this->user->id);

        $this->salle->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditSalle();
        $page->record = $this->salle;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditSalle::class)([]);

        $this->salle->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditSalle::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_salle'))->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * CalendrierWidget : adminMode + mode transversal "formation"
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CalendrierWidget : adminMode pour gestionnaire de formation', function () {

    it('adminMode est true si utilisateur a resa::gestionnaire_formation', function () {
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->mount();

        expect($widget->adminMode)->toBeTrue();
    });
});

describe('CalendrierWidget : modeFormationGlobale', function () {

    it('ne restreint pas les événements à une salle, uniquement à la formation filtrée', function () {
        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->modeFormationGlobale = true;
        $widget->filtreFormations     = [$this->formation->id];
        $widget->mount();

        expect($widget->etapeNavigation)->toBe('calendrier');
        expect($widget->adminMode)->toBeTrue();

        $info = new \Guava\Calendar\ValueObjects\FetchInfo([
            'startStr' => now()->subMonth()->toIso8601String(),
            'endStr'   => now()->addMonth()->toIso8601String(),
        ]);

        $query  = \Closure::bind(fn ($i) => $this->getEvents($i), $widget, CalendrierWidget::class)($info);
        $result = $query->get();

        expect($result)->toHaveCount(1);
        expect($result->first()->id)->toBe($this->reservation->id);
    });

    it('les colonnes (postes) viennent des postes configurés pour la formation filtrée', function () {
        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->modeFormationGlobale = true;
        $widget->filtreFormations     = [$this->formation->id];
        $widget->mount();

        $resources = $widget->getResourcesJs();

        expect($resources)->toHaveCount(1);
        expect($resources[0]['id'])->toBe($this->poste->id);
        expect($resources[0]['title'])->toBe('Poste 1');
    });

    it('préfixe le libellé du poste par la salle quand plusieurs salles sont concernées', function () {
        $autreSalle = Salle::create([
            'libelle_court' => 'Autre salle',
            'type_salle_id' => $this->salle->type_salle_id,
            'unite_id'      => $this->unite->id,
            'ordre'         => 2,
        ]);
        $autrePoste = SallePoste::create([
            'salle_id'      => $autreSalle->id,
            'libelle_court' => 'Poste A',
            'capacite'      => 1,
            'ordre'         => 1,
            'type_poste_id' => TypePoste::firstOrFail()->id,
        ]);
        $autrePoste->formations()->attach($this->formation->id);

        Reservation::create([
            'user_id'              => $this->user->id,
            'salle_poste_id'       => $autrePoste->id,
            'libelle_court'        => 'Autre session FPV',
            'debut'                => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'fin'                  => now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'validation'           => Reservation::EN_ATTENTE,
            'formations_utilisees' => [$this->formation->id],
        ]);

        $this->actingAs($this->user);

        $widget = new CalendrierWidget();
        $widget->modeFormationGlobale = true;
        $widget->filtreFormations     = [$this->formation->id];
        $widget->mount();

        $titres = collect($widget->getResourcesJs())->pluck('title');

        expect($titres)->toContain('Salle Simulateur — Poste 1');
        expect($titres)->toContain('Autre salle — Poste A');
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * ReservationWidget : masquer l'assistant pour un gestionnaire de formation pur
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationWidget : canView()', function () {

    it('est visible pour un utilisateur normal', function () {
        $this->actingAs($this->user);

        expect(ReservationWidget::canView())->toBeTrue();
    });

    it('est masqué pour un gestionnaire de formation pur', function () {
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user);

        expect(ReservationWidget::canView())->toBeFalse();
    });

    it('reste visible pour un admin même avec la permission formation', function () {
        $this->admin->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->admin);

        expect(ReservationWidget::canView())->toBeTrue();
    });

    it('reste visible pour un gestionnaire de salle même avec la permission formation', function () {
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user);

        expect(ReservationWidget::canView())->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * SuiviFormationsWidget
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SuiviFormationsWidget', function () {

    it('n\'est pas visible pour un utilisateur sans aucune formation gérée', function () {
        $this->actingAs($this->user);

        expect(SuiviFormationsWidget::canView())->toBeFalse();
    });

    it('est visible pour un gestionnaire d\'au moins une formation', function () {
        $this->formation->gestionnaires()->attach($this->user->id);
        $this->actingAs($this->user);

        expect(SuiviFormationsWidget::canView())->toBeTrue();
    });

    it('est visible pour un gestionnaire de salle (vue globale)', function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user);

        expect(SuiviFormationsWidget::canView())->toBeTrue();
    });

    it('limite getFormationsDisponibles() aux formations gérées pour un non-admin', function () {
        $autreFormation = Formation::create(['libelle_court' => 'Autre', 'ordre' => 13]);
        $this->formation->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();

        $ids = $widget->getFormationsDisponibles()->pluck('id');

        expect($ids)->toContain($this->formation->id);
        expect($ids)->not->toContain($autreFormation->id);
    });

    it('démarre sur "Toutes" (null) pour un non-admin', function () {
        $this->formation->gestionnaires()->attach($this->user->id);
        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();

        expect($widget->filtreFormationId)->toBeNull();
    });

    it('affiche les onglets seulement si plusieurs formations sont gérées', function () {
        $autreFormation = Formation::create(['libelle_court' => 'Autre', 'ordre' => 14]);
        $this->formation->gestionnaires()->attach($this->user->id);
        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();
        expect($widget->afficherOnglets())->toBeFalse();

        $autreFormation->gestionnaires()->attach($this->user->id);
        $widget2 = new SuiviFormationsWidget();
        $widget2->mount();
        expect($widget2->afficherOnglets())->toBeTrue();
    });

    it('calcule des statistiques cohérentes pour la formation gérée', function () {
        $this->formation->gestionnaires()->attach($this->user->id);
        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();

        $stats = $widget->getStats();

        expect($stats['nb_formations'])->toBe(1);
        expect($stats['a_venir'])->toBe(1);
        expect($stats['en_attente'])->toBe(1);
    });

    it('toggleValidation() refuse si l\'utilisateur n\'est pas autorisé', function () {
        $this->actingAs($this->user2);

        $widget = new SuiviFormationsWidget();
        $widget->toggleValidation($this->reservation->id);

        expect($this->reservation->fresh()->isValide())->toBeFalse();
    });

    it('toggleValidation() valide la réservation pour un gestionnaire de la formation', function () {
        $this->formation->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user2);

        $widget = new SuiviFormationsWidget();
        $widget->toggleValidation($this->reservation->id);

        expect($this->reservation->fresh()->isValide())->toBeTrue();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * MesReservationsWidget
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('MesReservationsWidget', function () {

    it('est visible pour tout utilisateur connecté', function () {
        $this->actingAs($this->user);

        expect(MesReservationsWidget::canView())->toBeTrue();
    });

    it('n\'est pas visible pour un visiteur non connecté', function () {
        expect(MesReservationsWidget::canView())->toBeFalse();
    });

    it('liste les réservations de salle et de matériel du seul utilisateur connecté', function () {
        RessourceReservation::create([
            'user_id'      => $this->user->id,
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'validation'   => RessourceReservation::EN_ATTENTE,
        ]);

        // Réservation d'un autre utilisateur : ne doit pas apparaître
        Reservation::create([
            'user_id'              => $this->user2->id,
            'salle_poste_id'       => $this->poste->id,
            'libelle_court'        => 'Pas la mienne',
            'debut'                => now()->addDays(3)->setTime(9, 0)->toDateTimeString(),
            'fin'                  => now()->addDays(3)->setTime(11, 0)->toDateTimeString(),
            'validation'           => Reservation::EN_ATTENTE,
            'formations_utilisees' => [],
        ]);

        $this->actingAs($this->user);

        $widget = new MesReservationsWidget();
        $rows   = $widget->getReservations();

        expect($rows)->toHaveCount(2);
        expect($rows->pluck('type')->sort()->values()->all())->toBe(['materiel', 'salle']);
    });

    it('calcule des statistiques correctes', function () {
        $this->actingAs($this->user);

        $widget = new MesReservationsWidget();
        $stats  = $widget->getStats();

        expect($stats['total'])->toBe(1);
        expect($stats['a_venir'])->toBe(1);
        expect($stats['en_attente'])->toBe(1);
    });

    it('filtre par statut', function () {
        $this->actingAs($this->user);

        $widget = new MesReservationsWidget();
        $widget->filtreStatut = 1; // validées uniquement

        expect($widget->getReservations())->toHaveCount(0);

        $widget->filtreStatut = 0; // en attente uniquement
        expect($widget->getReservations())->toHaveCount(1);
    });

    it('supprime une réservation en attente qui m\'appartient', function () {
        $this->actingAs($this->user);

        $widget = new MesReservationsWidget();
        $widget->supprimer('salle', $this->reservation->id);

        expect(Reservation::find($this->reservation->id))->toBeNull();
    });

    it('refuse de supprimer une réservation déjà validée', function () {
        $this->reservation->update(['validation' => Reservation::VALIDE]);
        $this->actingAs($this->user);

        $widget = new MesReservationsWidget();
        $widget->supprimer('salle', $this->reservation->id);

        expect(Reservation::find($this->reservation->id))->not->toBeNull();
    });

    it('refuse de supprimer la réservation d\'un autre utilisateur', function () {
        $this->actingAs($this->user2);

        $widget = new MesReservationsWidget();
        $widget->supprimer('salle', $this->reservation->id);

        expect(Reservation::find($this->reservation->id))->not->toBeNull();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * SuiviFormationsWidget : rafraîchissement du calendrier après (in)validation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SuiviFormationsWidget : rafraîchissement du calendrier', function () {

    it('toggleValidation() dispatche calendar--refresh et incrémente calendarVersion', function () {
        $this->formation->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user2);

        $widget = new SuiviFormationsWidget();
        expect($widget->calendarVersion)->toBe(0);

        $widget->toggleValidation($this->reservation->id);

        $dispatched = \Livewire\store($widget)->get('dispatched', []);
        $noms = collect($dispatched)->map(fn ($event) => (new ReflectionProperty($event, 'name'))->getValue($event));

        expect($noms)->toContain('calendar--refresh');
        // calendarVersion force le remontage complet du calendrier embarqué (clé Livewire) :
        // plus fiable que l'événement JS seul, qui dépend du chargement différé (x-load).
        expect($widget->calendarVersion)->toBe(1);
    });

    it('ne dispatche rien et n\'incrémente pas calendarVersion si la validation est refusée', function () {
        $this->actingAs($this->user2);

        $widget = new SuiviFormationsWidget();
        $widget->toggleValidation($this->reservation->id);

        $dispatched = \Livewire\store($widget)->get('dispatched', []);

        expect($dispatched)->toBeEmpty();
        expect($widget->calendarVersion)->toBe(0);
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * VoirStagiaireAction : détail réservation + infos marin (grade, spécialité, unité)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('VoirStagiaireAction', function () {

    beforeEach(function () {
        Queue::fake();

        $this->grade = Grade::create(['libelle_court' => 'CC', 'libelle_long' => 'Capitaine de corvette', 'ordre' => 1]);

        $this->marin = Marin::create([
            'nom'      => 'DURAND',
            'prenom'   => 'Jean',
            'nid'      => substr(uniqid(), 0, 15),
            'grade_id' => $this->grade->id,
            'user_id'  => $this->user->id,
            'email'    => $this->user->email,
        ]);
    });

    /*
     * Un composant Filament (Grid/TextEntry) ne peut pas être introspecté (getState(),
     * getChildComponents() en profondeur) hors d'un vrai cycle de rendu Livewire — il
     * lève "Typed property ...$container must not be accessed before initialization".
     * On vérifie donc que la construction du schéma ne plante pas (smoke test), et on
     * teste la vraie logique métier qui l'alimente directement sur le modèle Marin.
     */
    it('n\'explose pas pour une réservation inconnue', function () {
        $schema = (new ReflectionMethod(VoirStagiaireAction::class, 'schema'))->invoke(null, null);

        expect($schema)->toBeArray()->not->toBeEmpty();
    });

    it('ne plante pas pour une réservation avec un stagiaire ayant un dossier marin', function () {
        $schema = (new ReflectionMethod(VoirStagiaireAction::class, 'schema'))->invoke(null, $this->reservation->id);

        // Grid horaires + salle/poste + capacités + objet + 1 grid par stagiaire
        expect($schema)->toBeArray();
        expect(count($schema))->toBeGreaterThanOrEqual(5);
    });

    it('Marin::fromUser() retrouve le bon dossier marin, avec son grade', function () {
        $marin = Marin::fromUser($this->user);

        expect($marin)->not->toBeNull();
        expect($marin->nom)->toBe('DURAND');
        expect($marin->grade?->libelle_court)->toBe('CC');
    });

    it('Marin::fromUser() retourne null pour un utilisateur sans dossier RH', function () {
        expect(Marin::fromUser($this->user2))->toBeNull();
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * Ressource : disponibilité / entretien
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Ressource : statut de disponibilité', function () {

    it('est disponible par défaut', function () {
        expect($this->ressource->isDisponible())->toBeTrue();
        expect($this->ressource->statut)->toBe(Ressource::STATUT_DISPONIBLE);
    });

    it('n\'est plus disponible en entretien ou indisponible', function () {
        $this->ressource->statut = Ressource::STATUT_ENTRETIEN;
        expect($this->ressource->isDisponible())->toBeFalse();

        $this->ressource->statut = Ressource::STATUT_INDISPONIBLE;
        expect($this->ressource->isDisponible())->toBeFalse();
    });

    it('expose un libellé lisible pour chaque statut', function () {
        $this->ressource->statut = Ressource::STATUT_ENTRETIEN;
        expect($this->ressource->statut_label)->toBe('En entretien');
    });
});

describe('RessourceGanttWidget : blocage de réservation si indisponible', function () {

    it('refuse la création d\'une réservation sur une ressource en entretien', function () {
        $this->ressource->update(['statut' => Ressource::STATUT_ENTRETIEN]);
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->adminMode = false;

        (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))->invoke(null, $widget, [
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Test entretien',
        ]);

        expect(RessourceReservation::count())->toBe(0);
    });

    it('autorise la création quand la ressource est disponible', function () {
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->adminMode = false;

        (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))->invoke(null, $widget, [
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
            'libelle'      => 'Test disponible',
        ]);

        expect(RessourceReservation::count())->toBe(1);
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RessourceReservation : champs obligatoires (bâtiment, service, emprunteur, officier)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceReservation : bâtiment/service/emprunteur/officier', function () {

    it('enregistre les champs et résout le grade RH via Marin::fromUser()', function () {
        $grade = Grade::create(['libelle_court' => 'MT', 'libelle_long' => 'Matelot', 'ordre' => 1]);
        Marin::create([
            'nom' => $this->user->nom, 'prenom' => $this->user->prenom,
            'nid' => substr(uniqid(), 0, 15), 'grade_id' => $grade->id, 'user_id' => $this->user->id,
        ]);

        $reservation = RessourceReservation::create([
            'user_id'                      => $this->user->id,
            'ressource_id'                 => $this->ressource->id,
            'debut'                        => $this->debut,
            'fin'                          => $this->fin,
            'batiment'                     => 'Bâtiment 12',
            'service'                      => 'SEMA',
            'emprunteur_user_id'           => $this->user->id,
            'officier_responsable_user_id' => $this->user2->id,
        ]);

        expect($reservation->batiment)->toBe('Bâtiment 12');
        expect($reservation->service)->toBe('SEMA');
        expect($reservation->emprunteur->id)->toBe($this->user->id);
        expect($reservation->emprunteur_grade)->toBe('MT');
    });

    it('retourne "—" comme grade si l\'emprunteur n\'a pas de dossier RH', function () {
        $reservation = RessourceReservation::create([
            'user_id'                      => $this->user2->id,
            'ressource_id'                 => $this->ressource->id,
            'debut'                        => $this->debut,
            'fin'                          => $this->fin,
            'emprunteur_user_id'           => $this->user2->id,
            'officier_responsable_user_id' => $this->user->id,
        ]);

        expect($reservation->emprunteur_grade)->toBe('—');
        expect($reservation->officier_responsable_grade)->toBe('—');
    });

    it('le formulaire de création exige bâtiment, service, emprunteur et officier', function () {
        $widget = new RessourceGanttWidget();

        // schema() est privée sur NouvelleResaAction : on y accède par réflexion pour
        // vérifier que les 4 champs obligatoires y sont bien déclarés.
        $schema = (new ReflectionMethod(NouvelleResaAction::class, 'schema'))->invoke(null, $widget);
        $noms = collect($schema)->map(fn ($c) => method_exists($c, 'getName') ? $c->getName() : null)->filter();

        expect($noms)->toContain('batiment');
        expect($noms)->toContain('service');
        expect($noms)->toContain('emprunteur_user_id');
        expect($noms)->toContain('officier_responsable_user_id');
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RessourceCategorie : gestionnaire (point 4 — validation autonome du matériel)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceCategorie : isGestionnaire()', function () {

    it('retourne false si l\'utilisateur n\'est pas gestionnaire', function () {
        expect($this->categorie->isGestionnaire($this->user))->toBeFalse();
    });

    it('retourne true après avoir attaché l\'utilisateur comme gestionnaire', function () {
        $this->categorie->gestionnaires()->attach($this->user->id);

        expect($this->categorie->isGestionnaire($this->user))->toBeTrue();
    });
});

describe('CreateRessourceCategorie / EditRessourceCategorie : permission resa::gestionnaire_ressource', function () {

    it('attribue la permission au gestionnaire choisi à la création', function () {
        $this->categorie->gestionnaires()->attach($this->user->id);

        $page = new CreateRessourceCategorie();
        $page->record = $this->categorie;

        \Closure::bind(fn () => $this->afterCreate(), $page, CreateRessourceCategorie::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_ressource'))->toBeTrue();
    });

    it('révoque la permission quand le gestionnaire est retiré et ne gère plus aucune catégorie', function () {
        $this->categorie->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditRessourceCategorie();
        $page->record = $this->categorie;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditRessourceCategorie::class)([]);

        $this->categorie->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditRessourceCategorie::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_ressource'))->toBeFalse();
    });

    it('conserve la permission si le gestionnaire retiré gère encore une autre catégorie', function () {
        $autreCategorie = RessourceCategorie::create(['libelle' => 'Autre', 'libelle_court' => 'AU', 'ordre' => 2]);
        $autreCategorie->gestionnaires()->attach($this->user->id);

        $this->categorie->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $page = new EditRessourceCategorie();
        $page->record = $this->categorie;
        \Closure::bind(fn ($data) => $this->mutateFormDataBeforeFill($data), $page, EditRessourceCategorie::class)([]);

        $this->categorie->gestionnaires()->detach($this->user->id);

        \Closure::bind(fn () => $this->afterSave(), $page, EditRessourceCategorie::class)();

        expect($this->user->fresh()->can('resa::gestionnaire_ressource'))->toBeTrue();
    });
});

describe('RessourceReservationPolicy : valider()', function () {

    it('un admin peut toujours valider', function () {
        $r = RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        expect($this->admin->can('valider', $r))->toBeTrue();
    });

    it('un gestionnaire de la catégorie de la ressource peut valider', function () {
        $this->categorie->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $r = RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        expect($this->user2->can('valider', $r))->toBeTrue();
    });

    it('un gestionnaire d\'une autre catégorie ne peut pas valider', function () {
        $autreCategorie = RessourceCategorie::create(['libelle' => 'Autre', 'libelle_court' => 'AU', 'ordre' => 2]);
        $autreCategorie->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $r = RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        expect($this->user2->can('valider', $r))->toBeFalse();
    });

    it('un utilisateur sans aucune permission ne peut pas valider', function () {
        $r = RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        expect($this->user2->can('valider', $r))->toBeFalse();
    });
});

describe('NouvelleResaAction : auto-validation gestionnaire de catégorie', function () {

    it('un gestionnaire de la catégorie reçoit la validation automatique', function () {
        $this->categorie->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->adminMode = false;

        (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))->invoke(null, $widget, [
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
        ]);

        $resa = RessourceReservation::first();
        expect($resa->validation)->toBe(RessourceReservation::VALIDE);
        expect($resa->validated_by)->toBe($this->user->id);
    });

    it('un utilisateur normal (sans gestion) ne reçoit pas la validation automatique', function () {
        $this->actingAs($this->user);

        $widget = new RessourceGanttWidget();
        $widget->adminMode = false;

        (new ReflectionMethod(NouvelleResaAction::class, 'creerReservation'))->invoke(null, $widget, [
            'ressource_id' => $this->ressource->id,
            'debut'        => $this->debut,
            'fin'          => $this->fin,
        ]);

        $resa = RessourceReservation::first();
        expect($resa->validation)->toBe(RessourceReservation::EN_ATTENTE);
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * EmailListField : conversion chaîne CSV ↔ liste (Salle/Formation notification)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('EmailListField', function () {

    it('toArray() nettoie les espaces et les virgules vides', function () {
        $result = EmailListField::toArray('a@test.fr, b@test.fr ,, c@test.fr');

        expect($result)->toBe(['a@test.fr', 'b@test.fr', 'c@test.fr']);
    });

    it('toArray() retourne un tableau vide pour une valeur nulle ou vide', function () {
        expect(EmailListField::toArray(null))->toBe([]);
        expect(EmailListField::toArray(''))->toBe([]);
    });

    it('toString() recompose une chaîne séparée par des virgules', function () {
        expect(EmailListField::toString(['a@test.fr', 'b@test.fr']))->toBe('a@test.fr,b@test.fr');
    });

    it('toString() retourne une chaîne vide pour un tableau vide ou null', function () {
        expect(EmailListField::toString([]))->toBe('');
        expect(EmailListField::toString(null))->toBe('');
    });

    it('round-trip : toString(toArray(x)) préserve les adresses valides', function () {
        $original = 'a@test.fr,b@test.fr,c@test.fr';

        expect(EmailListField::toString(EmailListField::toArray($original)))->toBe($original);
    });

    it('make() retourne un TagsInput nommé correctement', function () {
        $field = EmailListField::make('email');

        expect($field)->toBeInstanceOf(\Filament\Forms\Components\TagsInput::class);
        expect($field->getName())->toBe('email');
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * RessourceReservationResource : accès et validation pour un gestionnaire de catégorie
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('RessourceReservationResource : gestionnaire de catégorie', function () {

    it('est inaccessible à un utilisateur sans aucune permission', function () {
        $this->actingAs($this->user2);

        expect(RessourceReservationResource::canAccess())->toBeFalse();
    });

    it('est accessible à un gestionnaire de catégorie', function () {
        $this->categorie->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user2);

        expect(RessourceReservationResource::canAccess())->toBeTrue();
    });

    it('limite la liste aux réservations des catégories gérées', function () {
        $autreCategorie = RessourceCategorie::create(['libelle' => 'Autre', 'libelle_court' => 'AU', 'ordre' => 2]);
        $autreRessource = Ressource::create(['ressource_categorie_id' => $autreCategorie->id, 'libelle' => 'Y', 'libelle_court' => 'Y', 'ordre' => 1]);

        RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);
        RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $autreRessource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        $this->categorie->gestionnaires()->attach($this->user2->id);
        $this->user2->givePermissionTo('resa::gestionnaire_ressource');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user2);

        expect(RessourceReservationResource::getEloquentQuery()->count())->toBe(1);
    });

    it('un admin complet voit toutes les réservations, toutes catégories confondues', function () {
        $autreCategorie = RessourceCategorie::create(['libelle' => 'Autre', 'libelle_court' => 'AU', 'ordre' => 2]);
        $autreRessource = Ressource::create(['ressource_categorie_id' => $autreCategorie->id, 'libelle' => 'Y', 'libelle_court' => 'Y', 'ordre' => 1]);

        RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $this->ressource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);
        RessourceReservation::create([
            'user_id' => $this->user->id, 'ressource_id' => $autreRessource->id,
            'debut' => $this->debut, 'fin' => $this->fin,
        ]);

        $this->actingAs($this->admin);

        expect(RessourceReservationResource::getEloquentQuery()->count())->toBe(2);
    });
});

/* ══════════════════════════════════════════════════════════════════════════════
 * Reproduction du retour terrain : gestionnaire de formation, bouton Valider absent
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SuiviFormationsWidget : réservation en attente visible pour le gestionnaire', function () {

    it('une réservation EN_ATTENTE de la formation gérée apparaît avec peut_valider=true sous les filtres par défaut', function () {
        $this->formation->gestionnaires()->attach([$this->user->id, $this->user2->id]);
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // $this->reservation (créée dans beforeEach) est déjà EN_ATTENTE pour $this->formation.
        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();

        // Filtres par défaut : filtreStatut=null, filtrePeriode='mois'. La réservation est à
        // debut = demain, donc dans le mois en cours → ne doit pas être filtrée par erreur.
        $rows = $widget->getReservations();
        $ligne = $rows->firstWhere('id', $this->reservation->id);

        expect($ligne)->not->toBeNull();
        expect($ligne['valide'])->toBeFalse();
        expect($ligne['peut_valider'])->toBeTrue();
    });

    it('toggleValidation() valide bien cette réservation pour le 2e gestionnaire de la même formation', function () {
        $this->formation->gestionnaires()->attach([$this->user->id, $this->user2->id]);
        $this->user2->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user2);

        $widget = new SuiviFormationsWidget();
        $widget->mount();
        $widget->toggleValidation($this->reservation->id);

        expect($this->reservation->fresh()->isValide())->toBeTrue();
        expect($this->reservation->fresh()->validated_by)->toBe($this->user2->id);
    });

    it('la réservation déjà validée affiche peut_valider=true (pour permettre l\'Invalider) au gestionnaire', function () {
        $this->reservation->update(['validation' => Reservation::VALIDE, 'validated_by' => $this->admin->id, 'validated_at' => now()]);

        $this->formation->gestionnaires()->attach($this->user->id);
        $this->user->givePermissionTo('resa::gestionnaire_formation');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->actingAs($this->user);

        $widget = new SuiviFormationsWidget();
        $widget->mount();

        $rows = $widget->getReservations();
        $ligne = $rows->firstWhere('id', $this->reservation->id);

        expect($ligne)->not->toBeNull();
        expect($ligne['valide'])->toBeTrue();
        expect($ligne['peut_valider'])->toBeTrue();
    });
});
```

### tests/Feature/ResourcesFormTest.php

```php
<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\EditReservation;
use Modules\Resa\Filament\Resources\SallePostes\Pages\CreateSallePoste;
use Modules\Resa\Filament\Resources\SallePostes\Pages\EditSallePoste;
use Modules\Resa\Filament\Resources\SalleResource\Pages\CreateSalle;
use Modules\Resa\Filament\Resources\SalleResource\Pages\EditSalle;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\CreateTypeCapacite;
use Modules\Resa\Filament\Resources\TypeCapacites\Pages\EditTypeCapacite;
use Modules\Resa\Filament\Resources\TypePostes\Pages\CreateTypePoste;
use Modules\Resa\Filament\Resources\TypePostes\Pages\EditTypePoste;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\CreateTypeSalle;
use Modules\Resa\Filament\Resources\TypeSalles\Pages\EditTypeSalle;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Resa');

/* ══════════════════════════════════════════════════════════════════════════════
 * SETUP
 * ══════════════════════════════════════════════════════════════════════════════ */

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('resa'));

    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::reservation.view', 'web');

    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::admin');
    $this->admin->givePermissionTo('resa::reservation.view');

    $this->seed(ResaDatabaseSeeder::class);
});

/* ══════════════════════════════════════════════════════════════════════════════
 * TypeSalleResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('TypeSalleResource — formulaires', function () {

    it('crée un TypeSalle valide', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypeSalle::class)
            ->fillForm([
                'libelle_court' => 'Simulateur Naval',
                'libelle_long'  => 'Salle de simulation navale',
                'ordre'         => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_type_salles', [
            'libelle_court' => 'Simulateur Naval',
        ]);
    });

    it('refuse la création sans libelle_court', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypeSalle::class)
            ->fillForm(['libelle_court' => ''])
            ->call('create')
            ->assertHasFormErrors(['libelle_court' => 'required']);
    });

    it('modifie un TypeSalle existant', function () {
        $record = TypeSalle::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(EditTypeSalle::class, ['record' => $record->id])
            ->fillForm([
                'libelle_court' => 'Type modifié',
                'ordre'         => 99,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_type_salles', [
            'id'            => $record->id,
            'libelle_court' => 'Type modifié',
        ]);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * TypePosteResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('TypePosteResource — formulaires', function () {

    it('crée un TypePoste valide', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypePoste::class)
            ->fillForm([
                'libelle_court' => 'Poste tactique',
                'libelle_long'  => 'Poste de conduite tactique',
                'ordre'         => 3,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_type_postes', [
            'libelle_court' => 'Poste tactique',
        ]);
    });

    it('refuse la création sans libelle_court', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypePoste::class)
            ->fillForm(['libelle_court' => ''])
            ->call('create')
            ->assertHasFormErrors(['libelle_court' => 'required']);
    });

    it('modifie un TypePoste existant', function () {
        $record = TypePoste::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(EditTypePoste::class, ['record' => $record->id])
            ->fillForm(['libelle_court' => 'Poste modifié'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_type_postes', [
            'id'            => $record->id,
            'libelle_court' => 'Poste modifié',
        ]);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * FormationResource (TypeCapaciteResource)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('FormationResource — formulaires', function () {

    it('crée une Formation valide', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypeCapacite::class)
            ->fillForm([
                'libelle_court' => 'Radar',
                'libelle_long'  => 'Poste radar de surveillance',
                'ordre'         => 7,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_formations', [
            'libelle_court' => 'Radar',
        ]);
    });

    it('refuse la création sans libelle_court', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateTypeCapacite::class)
            ->fillForm(['libelle_court' => ''])
            ->call('create')
            ->assertHasFormErrors(['libelle_court' => 'required']);
    });

    it('modifie une Formation existante', function () {
        $record = Formation::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(EditTypeCapacite::class, ['record' => $record->id])
            ->fillForm(['libelle_court' => 'Capacité modifiée'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_formations', [
            'id'            => $record->id,
            'libelle_court' => 'Capacité modifiée',
        ]);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * SalleResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SalleResource — formulaires', function () {

    it('crée une Salle valide', function () {
        $typeSalle = TypeSalle::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CreateSalle::class)
            ->fillForm([
                'type_salle_id' => $typeSalle->id,
                'code'          => 'SIM-03',
                'libelle_court' => 'Salle Simulation 3',
                'libelle_long'  => 'Troisième salle de simulation',
                'capacite'      => 12,
                'color'         => 'primary',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_salles', [
            'code'          => 'SIM-03',
            'libelle_court' => 'Salle Simulation 3',
        ]);
    });

    it('modifie une Salle existante', function () {
        $record = Salle::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(EditSalle::class, ['record' => $record->id])
            ->fillForm([
                'libelle_court' => 'Salle modifiée',
                'capacite'      => 20,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_salles', [
            'id'            => $record->id,
            'libelle_court' => 'Salle modifiée',
        ]);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * SalleResource — lieu / unité
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SalleResource — lieu et unité', function () {

    beforeEach(function () {
        $this->lieu = LieuUnite::create([
            'libelle_court' => 'Toulon',
            'libelle_long'  => 'Base navale de Toulon',
            'ordre'         => 1,
        ]);

        $this->unite = Unite::create([
            'libelle_court' => 'ERM',
            'libelle_long'  => 'École de réserve marine',
            'lieu_unite_id' => $this->lieu->id,
            'ordre'         => 1,
        ]);
    });

    it('crée une salle avec unite_id et vérifie la persistance', function () {
        $typeSalle = TypeSalle::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CreateSalle::class)
            ->fillForm([
                'type_salle_id' => $typeSalle->id,
                'lieu_unite_id' => $this->lieu->id,
                'unite_id'      => $this->unite->id,
                'code'          => 'TLN-01',
                'libelle_court' => 'Salle Toulon 1',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_salles', [
            'code'      => 'TLN-01',
            'unite_id'  => $this->unite->id,
        ]);
    });

    it('le lieu n\'est pas sauvegardé en base (champ virtuel dehydrated false)', function () {
        $typeSalle = TypeSalle::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CreateSalle::class)
            ->fillForm([
                'type_salle_id' => $typeSalle->id,
                'lieu_unite_id' => $this->lieu->id,
                'unite_id'      => $this->unite->id,
                'code'          => 'TLN-02',
                'libelle_court' => 'Salle Toulon 2',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $salle = Salle::where('code', 'TLN-02')->firstOrFail();
        expect($salle->unite_id)->toBe($this->unite->id);
        expect($salle->getAttributes())->not->toHaveKey('lieu_unite_id');
    });

    it('le formulaire d\'édition pré-remplit le lieu depuis l\'unité', function () {
        $typeSalle = TypeSalle::firstOrFail();
        $salle = Salle::create([
            'type_salle_id' => $typeSalle->id,
            'unite_id'      => $this->unite->id,
            'libelle_court' => 'Salle existante',
            'code'          => 'TLN-99',
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditSalle::class, ['record' => $salle->id])
            ->assertFormSet([
                'unite_id'      => $this->unite->id,
                'lieu_unite_id' => $this->lieu->id,
            ]);
    });

    it('changer de lieu vide la sélection d\'unité', function () {
        $lieu2 = LieuUnite::create([
            'libelle_court' => 'Brest',
            'libelle_long'  => 'Base navale de Brest',
            'ordre'         => 2,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CreateSalle::class)
            ->fillForm([
                'lieu_unite_id' => $this->lieu->id,
                'unite_id'      => $this->unite->id,
            ])
            ->set('data.lieu_unite_id', $lieu2->id)
            ->assertFormSet(['unite_id' => null]);
    });

    it('la table affiche les colonnes lieu et unité', function () {
        $typeSalle = TypeSalle::firstOrFail();
        Salle::create([
            'type_salle_id' => $typeSalle->id,
            'unite_id'      => $this->unite->id,
            'libelle_court' => 'Salle avec lieu',
            'code'          => 'TLN-TABLE',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\Modules\Resa\Filament\Resources\SalleResource\Pages\ListSalles::class)
            ->assertCanSeeTableRecords(Salle::all())
            ->assertTableColumnExists('unite.lieuUnite.libelle_court')
            ->assertTableColumnExists('unite.libelle_court');
    });

    it('le filtre par lieu restreint les résultats', function () {
        $typeSalle = TypeSalle::firstOrFail();

        $lieu2  = LieuUnite::create(['libelle_court' => 'Brest', 'ordre' => 2]);
        $unite2 = Unite::create(['libelle_court' => 'BEM', 'libelle_long' => 'BEM', 'lieu_unite_id' => $lieu2->id]);

        $salleTln  = Salle::create(['type_salle_id' => $typeSalle->id, 'unite_id' => $this->unite->id,  'libelle_court' => 'Toulon salle', 'code' => 'F-TLN']);
        $salleBrest = Salle::create(['type_salle_id' => $typeSalle->id, 'unite_id' => $unite2->id, 'libelle_court' => 'Brest salle',  'code' => 'F-BRT']);

        Livewire::actingAs($this->admin)
            ->test(\Modules\Resa\Filament\Resources\SalleResource\Pages\ListSalles::class)
            ->filterTable('lieu', $this->lieu->id)
            ->assertCanSeeTableRecords(Salle::where('unite_id', $this->unite->id)->get())
            ->assertCanNotSeeTableRecords(collect([$salleBrest]));
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * SallePosteResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SallePosteResource — formulaires', function () {

    it('crée un SallePoste valide', function () {
        $salle     = Salle::firstOrFail();
        $typePoste = TypePoste::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CreateSallePoste::class)
            ->fillForm([
                'salle_id'      => $salle->id,
                'type_poste_id' => $typePoste->id,
                'libelle_court' => 'Poste Alpha',
                'ordre'         => 10,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_salle_postes', [
            'salle_id'      => $salle->id,
            'libelle_court' => 'Poste Alpha',
        ]);
    });

    it('refuse la création sans salle ni type_poste', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateSallePoste::class)
            ->fillForm([
                'salle_id'      => null,
                'type_poste_id' => null,
                'libelle_court' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['salle_id' => 'required', 'type_poste_id' => 'required', 'libelle_court' => 'required']);
    });

    it('modifie le libelle d\'un SallePoste existant', function () {
        $record = SallePoste::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(EditSallePoste::class, ['record' => $record->id])
            ->fillForm(['libelle_court' => 'Poste renommé'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_salle_postes', [
            'id'            => $record->id,
            'libelle_court' => 'Poste renommé',
        ]);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * ReservationResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationResource — formulaires', function () {

    it('crée une Reservation valide (admin → directement validée)', function () {
        $poste = SallePoste::firstOrFail();

        Livewire::actingAs($this->admin)
            ->test(CreateReservation::class)
            ->fillForm([
                'libelle_court'  => 'Exercice Neptune',
                'user_id'        => $this->admin->id,
                'salle_poste_id' => $poste->id,
                'debut'          => now()->next('Monday')->setTime(9, 0)->format('Y-m-d H:i'),
                'fin'            => now()->next('Monday')->setTime(11, 0)->format('Y-m-d H:i'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_reservations', [
            'libelle_court'  => 'Exercice Neptune',
            'user_id'        => $this->admin->id,
            'salle_poste_id' => $poste->id,
        ]);
    });

    it('refuse une Reservation sans champs obligatoires', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateReservation::class)
            ->fillForm([
                'libelle_court'  => '',
                'participants'   => [],
                'salle_poste_id' => null,
                'debut'          => null,
                'fin'            => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'libelle_court'  => 'required',
                'salle_poste_id' => 'required',
                'debut'          => 'required',
                'fin'            => 'required',
            ]);
    });

    it('modifie le libelle d\'une Reservation existante', function () {
        $poste  = SallePoste::firstOrFail();
        $record = Reservation::create([
            'user_id'        => $this->admin->id,
            'salle_poste_id' => $poste->id,
            'libelle_court'  => 'Resa initiale',
            'debut'          => now()->next('Monday')->setTime(9, 0),
            'fin'            => now()->next('Monday')->setTime(11, 0),
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditReservation::class, ['record' => $record->id])
            ->fillForm(['libelle_court' => 'Resa modifiée'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('resa_reservations', [
            'id'            => $record->id,
            'libelle_court' => 'Resa modifiée',
        ]);
    });

});
```

### database/migrations/2026_11_03_000003_add_created_by_to_resa_reservations.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        // user_id désigne le stagiaire/participant (pas forcément celui qui réserve) :
        // ce champ permet de retrouver "mes réservations" côté personne qui a
        // effectué la demande, même pour un tiers (cf. RessourceReservation qui a
        // déjà cette distinction via emprunteur_user_id).
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
```

### database/migrations/2026_10_22_000003_create_resa_ressource_categorie_gestionnaires_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        Schema::create($this->prefix . 'ressource_categorie_gestionnaires', function (Blueprint $table) {
            $table->unsignedBigInteger('ressource_categorie_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['ressource_categorie_id', 'user_id']);
            $table->foreign('ressource_categorie_id', 'resa_rc_gestionnaires_categorie_fk')
                ->references('id')->on($this->prefix . 'ressource_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'ressource_categorie_gestionnaires');
    }
};
```

### database/migrations/2026_10_22_000002_add_champs_obligatoires_to_resa_ressource_reservations.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        Schema::table($this->prefix . 'ressource_reservations', function (Blueprint $table) {
            // Nullable en base (ne casse pas les réservations existantes) ; obligatoires
            // côté formulaire pour toute nouvelle réservation (RessourceGanttWidget).
            $table->string('batiment')->nullable()->after('libelle');
            $table->string('service')->nullable()->after('batiment');
            $table->foreignId('emprunteur_user_id')->nullable()->after('service')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('officier_responsable_user_id')->nullable()->after('emprunteur_user_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressource_reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('emprunteur_user_id');
            $table->dropConstrainedForeignId('officier_responsable_user_id');
            $table->dropColumn(['batiment', 'service']);
        });
    }
};
```

### database/migrations/2026_11_03_000001_add_validation_auto_to_resa_formations.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            // Ex. "FPV Entraînement" (auto-validée) vs "FPV Examen" (validée par un
            // gestionnaire de la formation) : deux Formation distinctes, ce champ
            // pilote si la réservation est validée dès sa création ou reste en attente.
            $table->boolean('validation_auto')->default(false)->after('nb_users_par_poste');

            // Consignes envoyées dans l'email de confirmation quand la réservation est
            // auto-validée (ex. où récupérer les clés, où se trouve la salle FPS...).
            $table->text('notice')->nullable()->after('validation_auto');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'formations', function (Blueprint $table) {
            $table->dropColumn(['validation_auto', 'notice']);
        });
    }
};
```

### database/migrations/2026_11_03_000002_create_resa_formation_sous_categories_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        // Sous-catégories d'une formation (ex. FPV → "Entraînement" / "Examen") : chacune
        // porte sa propre règle de validation. 0 ou 1 sous-catégorie = la formation se
        // comporte comme avant, directement avec ses propres validation_auto/notice ;
        // 2+ sous-catégories = le réservataire doit en choisir une, qui prend le dessus.
        Schema::create($this->prefix . 'formation_sous_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained($this->prefix . 'formations')->cascadeOnDelete();
            $table->string('libelle_court', 255);
            $table->boolean('validation_auto')->default(false);
            $table->text('notice')->nullable();
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });

        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->foreignId('sous_categorie_id')
                ->nullable()
                ->after('formations_utilisees')
                ->constrained($this->prefix . 'formation_sous_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sous_categorie_id');
        });

        Schema::dropIfExists($this->prefix . 'formation_sous_categories');
    }
};
```

### database/migrations/2026_10_22_000001_add_statut_to_resa_ressources.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        Schema::table($this->prefix . 'ressources', function (Blueprint $table) {
            // 'disponible' (par défaut) | 'entretien' | 'indisponible' — distinct de `actif`
            // (actif = toujours au catalogue ; statut = peut être réservé maintenant ou non).
            $table->string('statut', 20)->default('disponible')->after('actif');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'ressources', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
```

### database/migrations/2026_11_03_000004_add_presence_to_resa_reservations.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Resa\Traits\HasTablePrefix;

return new class extends Migration
{
    use HasTablePrefix;

    public function up(): void
    {
        // Pointeuse : le marin pointe lui-même sa présence (le cas courant), un
        // gestionnaire peut le faire à sa place (marin absent, oubli, etc.).
        // null = pas encore pointé, true = présent, false = absent/annulé sur place.
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->boolean('presence')->nullable()->after('validated_at');
            $table->foreignId('presence_marquee_par')->nullable()->after('presence')->constrained('users')->nullOnDelete();
            $table->timestamp('presence_marquee_at')->nullable()->after('presence_marquee_par');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('presence_marquee_par');
            $table->dropColumn(['presence', 'presence_marquee_at']);
        });
    }
};
```
