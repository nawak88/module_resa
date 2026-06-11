<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\CreateReservation;
use Modules\Resa\Filament\Resources\ReservationResource\Pages\EditReservation;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\ReservationWidget;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
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
 * SETUP
 * ══════════════════════════════════════════════════════════════════════════════ */

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('resa'));

    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::gestionnaire_salle', 'web');

    $this->user  = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->user3 = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::gestionnaire_salle');
    $this->admin->givePermissionTo('resa::admin');

    $this->seed(ResaDatabaseSeeder::class);

    $this->typeSalleClassique  = TypeSalle::where('mode', TypeSalle::MODE_CLASSIQUE)->firstOrFail();
    $this->typeSalleSimulateur = TypeSalle::where('mode', TypeSalle::MODE_SIMULATEUR)->firstOrFail();

    $this->lieu   = LieuUnite::create(['libelle_court' => 'Toulon', 'libelle_long' => 'Toulon', 'ordre' => 1]);
    $this->unite  = Unite::create(['libelle_court' => 'ERM', 'libelle_long' => 'ERM', 'ordre' => 1, 'lieu_unite_id' => $this->lieu->id]);

    $this->salleClassique = Salle::create([
        'libelle_court' => 'Salle A',
        'type_salle_id' => $this->typeSalleClassique->id,
        'unite_id'      => $this->unite->id,
        'capacite'      => 10,
        'ordre'         => 1,
    ]);

    $this->salleSimulateur = Salle::create([
        'libelle_court' => 'Simulateur 1',
        'type_salle_id' => $this->typeSalleSimulateur->id,
        'unite_id'      => $this->unite->id,
        'capacite'      => 4,
        'ordre'         => 2,
    ]);

    $typePoste = TypePoste::firstOrFail();

    $this->posteClassique = SallePoste::create([
        'salle_id'      => $this->salleClassique->id,
        'type_poste_id' => $typePoste->id,
        'libelle_court' => 'Salle entiere',
        'ordre'         => 1,
        'system'        => true,
    ]);

    $this->posteSimulateur = SallePoste::create([
        'salle_id'      => $this->salleSimulateur->id,
        'type_poste_id' => $typePoste->id,
        'libelle_court' => 'Poste 1',
        'ordre'         => 1,
    ]);

    $this->debut = now()->next('Monday')->setTime(9, 0)->toDateTimeString();
    $this->fin   = now()->next('Monday')->setTime(11, 0)->toDateTimeString();
});

/* ══════════════════════════════════════════════════════════════════════════════
 * CalendrierWidget — modeForce
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CalendrierWidget — modeForce', function () {

    it('mode classique : affiche uniquement les salles classiques', function () {
        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'classique',
            ])
            ->assertSet('etapeNavigation', 'calendrier')
            ->assertSet('modeForce', 'classique');
    });

    it('mode simulateur : affiche uniquement les salles simulateur', function () {
        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'simulateur',
            ])
            ->assertSet('etapeNavigation', 'calendrier')
            ->assertSet('modeForce', 'simulateur');
    });

    it('mode classique : isModeClassique retourne true', function () {
        $widget = Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'classique',
            ]);
        expect($widget->instance()->isModeClassique())->toBeTrue();
        expect($widget->instance()->isModeSimulateur())->toBeFalse();
    });

    it('mode simulateur : isModeSimulateur retourne true', function () {
        $widget = Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'simulateur',
            ]);
        expect($widget->instance()->isModeSimulateur())->toBeTrue();
        expect($widget->instance()->isModeClassique())->toBeFalse();
    });

    it('getSallesScope filtre par mode classique', function () {
        $widget = Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'classique',
            ]);
        $salles = $widget->instance()->getSallesScopePublic()->get();
        expect($salles->pluck('id'))->toContain($this->salleClassique->id);
        expect($salles->pluck('id'))->not->toContain($this->salleSimulateur->id);
    });

    it('getSallesScope filtre par mode simulateur', function () {
        $widget = Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'uniteActive'      => $this->unite->id,
                'skipTypeSalleNav' => true,
                'modeForce'        => 'simulateur',
            ]);
        $salles = $widget->instance()->getSallesScopePublic()->get();
        expect($salles->pluck('id'))->toContain($this->salleSimulateur->id);
        expect($salles->pluck('id'))->not->toContain($this->salleClassique->id);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * Reservation — participants (data.users)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Reservation — participants multiples', function () {

    // Donne à $this->user l'accès au formulaire (canAccess requiert gestionnaire_salle)
    beforeEach(function () {
        $this->user->givePermissionTo('resa::gestionnaire_salle');
    });

    it('crée une réservation classique avec plusieurs participants', function () {
        // actingAs $this->user : le défaut Repeater = user->id, fillForm met user->id en [0]
        Livewire::actingAs($this->user)
            ->test(CreateReservation::class)
            ->fillForm([
                'libelle_court'  => 'Exercice multi',
                'salle_poste_id' => $this->posteClassique->id,
                'debut'          => $this->debut,
                'fin'            => $this->fin,
                'participants'   => [
                    ['user_id' => $this->user->id],
                    ['user_id' => $this->user2->id],
                    ['user_id' => $this->user3->id],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $resa = Reservation::where('libelle_court', 'Exercice multi')->firstOrFail();
        expect($resa->data['users'])->toContain($this->user->id);
        expect($resa->data['users'])->toContain($this->user2->id);
        expect($resa->data['users'])->toContain($this->user3->id);
        expect($resa->user_id)->toBe($this->user->id);
    });

    it('crée une réservation avec un seul participant (user_id = participants[0])', function () {
        Livewire::actingAs($this->user)
            ->test(CreateReservation::class)
            ->fillForm([
                'libelle_court'  => 'Solo',
                'salle_poste_id' => $this->posteClassique->id,
                'debut'          => $this->debut,
                'fin'            => $this->fin,
                'participants'   => [['user_id' => $this->user->id]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $resa = Reservation::where('libelle_court', 'Solo')->firstOrFail();
        expect($resa->user_id)->toBe($this->user->id);
        expect($resa->data['users'])->toContain($this->user->id);
    });

    it('edit : mutateFormDataBeforeFill peuple les participants depuis data.users', function () {
        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteClassique->id,
            'libelle_court'       => 'Edit test',
            'debut'               => $this->debut,
            'fin'                 => $this->fin,
            'formations_utilisees'=> [],
            'validation'          => Reservation::EN_ATTENTE,
            'data'                => ['users' => [$this->user->id, $this->user2->id]],
        ]);

        // Le Repeater Filament utilise des UUIDs comme clés → vérifier via l'instance
        $component = Livewire::actingAs($this->admin)
            ->test(EditReservation::class, ['record' => $resa->id]);

        $userIds = collect($component->instance()->data['participants'] ?? [])
            ->pluck('user_id')
            ->values()
            ->toArray();

        expect($userIds)->toContain($this->user->id);
        expect($userIds)->toContain($this->user2->id);
    });

    it('edit : sauvegarde les participants modifiés dans data.users', function () {
        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteClassique->id,
            'libelle_court'       => 'Edit save',
            'debut'               => $this->debut,
            'fin'                 => $this->fin,
            'formations_utilisees'=> [],
            'validation'          => Reservation::EN_ATTENTE,
            'data'                => ['users' => [$this->user->id]],
        ]);

        Livewire::actingAs($this->admin)
            ->test(EditReservation::class, ['record' => $resa->id])
            ->fillForm([
                'participants' => [
                    ['user_id' => $this->user->id],
                    ['user_id' => $this->user3->id],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $resa->refresh();
        expect($resa->data['users'])->toContain($this->user->id);
        expect($resa->data['users'])->toContain($this->user3->id);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * ReservationResource — formulaire formations visible selon type salle
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationResource — champ formations conditionnel', function () {

    it('formations_utilisees visible pour poste simulateur', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateReservation::class)
            ->fillForm(['salle_poste_id' => $this->posteSimulateur->id])
            ->assertFormFieldIsVisible('formations_utilisees');
    });

    it('formations_utilisees masqué pour poste classique', function () {
        Livewire::actingAs($this->admin)
            ->test(CreateReservation::class)
            ->fillForm(['salle_poste_id' => $this->posteClassique->id])
            ->assertFormFieldIsHidden('formations_utilisees');
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * SuiviFormationsWidget — visibilité et filtres
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SuiviFormationsWidget — visibilité', function () {

    it('invisible pour un utilisateur sans droits ni formation', function () {
        expect(SuiviFormationsWidget::canView())->toBeFalse();

        // Avec l'user connecté sans droits
        $this->actingAs($this->user);
        expect(SuiviFormationsWidget::canView())->toBeFalse();
    });

    it('visible pour un admin', function () {
        $this->actingAs($this->admin);
        expect(SuiviFormationsWidget::canView())->toBeTrue();
    });

    it('visible pour un gestionnaire de formation', function () {
        $formation = Formation::firstOrFail();
        $formation->gestionnaires()->attach($this->user->id);

        $this->actingAs($this->user);
        expect(SuiviFormationsWidget::canView())->toBeTrue();
    });

});

describe('SuiviFormationsWidget — données', function () {

    beforeEach(function () {
        $this->formation = Formation::firstOrFail();

        $this->resaFormation = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteSimulateur->id,
            'libelle_court'       => 'FPV Session',
            'debut'               => $this->debut,
            'fin'                 => $this->fin,
            'formations_utilisees'=> [$this->formation->id],
            'validation'          => Reservation::VALIDE,
            'data'                => ['users' => [$this->user->id, $this->user2->id]],
        ]);
    });

    it('admin voit toutes les formations', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviFormationsWidget::class)
            ->assertSet('filtreFormationId', null)
            ->assertSuccessful();

        $widget = Livewire::actingAs($this->admin)->test(SuiviFormationsWidget::class);
        $rows = $widget->instance()->getReservations();
        expect($rows->count())->toBeGreaterThanOrEqual(1);
    });

    it('la ligne contient les deux stagiaires depuis data.users', function () {
        Livewire::actingAs($this->admin)
            ->test(SuiviFormationsWidget::class);

        $widget = Livewire::actingAs($this->admin)->test(SuiviFormationsWidget::class);
        $row = $widget->instance()->getReservations()
            ->firstWhere('id', $this->resaFormation->id);

        expect($row)->not->toBeNull();
        expect(count($row['stagiaires']))->toBe(2);
    });

    it('filtre par statut validé ne retourne que les validées', function () {
        Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteSimulateur->id,
            'libelle_court'       => 'En attente',
            'debut'               => now()->next('Tuesday')->setTime(9, 0),
            'fin'                 => now()->next('Tuesday')->setTime(11, 0),
            'formations_utilisees'=> [$this->formation->id],
            'validation'          => Reservation::EN_ATTENTE,
            'data'                => ['users' => [$this->user->id]],
        ]);

        $widget = Livewire::actingAs($this->admin)
            ->test(SuiviFormationsWidget::class)
            ->set('filtreStatut', 1);

        $rows = $widget->instance()->getReservations();
        expect($rows->every(fn ($r) => $r['valide'] === true))->toBeTrue();
    });

    it('filtre par nom stagiaire fonctionne', function () {
        $widget = Livewire::actingAs($this->admin)
            ->test(SuiviFormationsWidget::class)
            ->set('filtreNom', $this->user->nom);

        $rows = $widget->instance()->getReservations();
        expect($rows->count())->toBeGreaterThanOrEqual(1);
    });

    it('gestionnaire ne voit que ses formations', function () {
        $autreFormation = Formation::create([
            'libelle_court' => 'Formation Autre',
            'libelle_long'  => 'Formation Autre',
            'ordre'         => 99,
        ]);

        Reservation::create([
            'user_id'             => $this->user2->id,
            'salle_poste_id'      => $this->posteSimulateur->id,
            'libelle_court'       => 'Autre session',
            'debut'               => now()->next('Wednesday')->setTime(9, 0),
            'fin'                 => now()->next('Wednesday')->setTime(11, 0),
            'formations_utilisees'=> [$autreFormation->id],
            'validation'          => Reservation::VALIDE,
            'data'                => ['users' => [$this->user2->id]],
        ]);

        $this->formation->gestionnaires()->attach($this->user->id);
        $this->actingAs($this->user);

        $widget = Livewire::actingAs($this->user)->test(SuiviFormationsWidget::class);
        $formations = $widget->instance()->getFormationsDisponibles();

        expect($formations->pluck('id'))->toContain($this->formation->id);
        expect($formations->pluck('id'))->not->toContain($autreFormation->id);
    });

});


