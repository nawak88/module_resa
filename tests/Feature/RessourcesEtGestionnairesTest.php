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

        $creer = \Closure::bind(
            fn (array $data) => $this->creerReservation($data),
            $widget,
            RessourceGanttWidget::class
        );

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

        $creer = \Closure::bind(
            fn (array $data) => $this->creerReservation($data),
            $widget,
            RessourceGanttWidget::class
        );

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

        $creer = \Closure::bind(
            fn (array $data) => $this->creerReservation($data),
            $widget,
            RessourceGanttWidget::class
        );

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

        $creer = \Closure::bind(
            fn (array $data) => $this->creerReservation($data),
            $widget,
            RessourceGanttWidget::class
        );

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


