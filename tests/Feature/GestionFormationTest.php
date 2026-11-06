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
