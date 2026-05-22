<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\Formation;
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
    Filament::setCurrentPanel(
        Filament::getPanel('resa'),
    );

    Permission::findOrCreate('resa::admin', 'web');
    Permission::findOrCreate('resa::reservation.view', 'web');

    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::admin');
    $this->admin->givePermissionTo('resa::reservation.view');

    $this->seed(ResaDatabaseSeeder::class);
});

/* ══════════════════════════════════════════════════════════════════════════════
 * SalleResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SalleResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\SalleResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\SalleResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $record = Salle::firstOrFail();
        $this->actingAs($this->admin)
            ->get(Resources\SalleResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * SallePosteResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('SallePosteResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\SallePostes\SallePosteResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\SallePostes\SallePosteResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $record = SallePoste::firstOrFail();
        $this->actingAs($this->admin)
            ->get(Resources\SallePostes\SallePosteResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * TypeSalleResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('TypeSalleResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypeSalles\TypeSalleResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypeSalles\TypeSalleResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $record = TypeSalle::firstOrFail();
        $this->actingAs($this->admin)
            ->get(Resources\TypeSalles\TypeSalleResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * TypePosteResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('TypePosteResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypePostes\TypePosteResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypePostes\TypePosteResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $record = TypePoste::firstOrFail();
        $this->actingAs($this->admin)
            ->get(Resources\TypePostes\TypePosteResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * FormationResource (TypeCapaciteResource)
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('FormationResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypeCapacites\TypeCapaciteResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\TypeCapacites\TypeCapaciteResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $record = Formation::firstOrFail();
        $this->actingAs($this->admin)
            ->get(Resources\TypeCapacites\TypeCapaciteResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * ReservationResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationResource', function () {

    it('list retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\ReservationResource::getUrl('index'))
            ->assertSuccessful();
    });

    it('create retourne 200', function () {
        $this->actingAs($this->admin)
            ->get(Resources\ReservationResource::getUrl('create'))
            ->assertSuccessful();
    });

    it('edit retourne 200', function () {
        $poste = SallePoste::firstOrFail();
        $record = Reservation::create([
            'user_id'        => $this->admin->id,
            'salle_poste_id' => $poste->id,
            'libelle_court'  => 'Resa test admin',
            'debut'          => now()->next('Monday')->setTime(9, 0)->toDateTimeString(),
            'fin'            => now()->next('Monday')->setTime(11, 0)->toDateTimeString(),
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $this->actingAs($this->admin)
            ->get(Resources\ReservationResource::getUrl('edit', ['record' => $record]))
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * Gestionnaire de salle
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Gestionnaire de salle', function () {

    beforeEach(function () {
        Permission::findOrCreate('resa::gestionnaire_salle', 'web');

        $this->gestionnaire = User::factory()->create();
        $this->gestionnaire->givePermissionTo('resa::gestionnaire_salle');

        $this->salle        = Salle::firstOrFail();
        $this->autreUser    = User::factory()->create();
        $this->autreSalle   = Salle::skip(1)->firstOrFail();

        $this->poste        = SallePoste::where('salle_id', $this->salle->id)->firstOrFail();
        $this->autrePoste   = SallePoste::where('salle_id', $this->autreSalle->id)->firstOrFail();

        $this->debut = now()->next('Monday')->setTime(9, 0)->toDateTimeString();
        $this->fin   = now()->next('Monday')->setTime(11, 0)->toDateTimeString();
    });

    /* ── Attribution ──────────────────────────────────────────────────────── */

    it('un admin peut attribuer un gestionnaire à une salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        expect($this->salle->fresh()->isGestionnaire($this->gestionnaire))->toBeTrue();
    });

    it('un admin peut retirer un gestionnaire d\'une salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);
        $this->salle->gestionnaires()->detach($this->gestionnaire->id);

        expect($this->salle->fresh()->isGestionnaire($this->gestionnaire))->toBeFalse();
    });

    it('un admin peut attribuer plusieurs gestionnaires à la même salle', function () {
        $second = User::factory()->create();
        $second->givePermissionTo('resa::gestionnaire_salle');

        $this->salle->gestionnaires()->attach([$this->gestionnaire->id, $second->id]);

        expect($this->salle->fresh()->gestionnaires()->count())->toBe(2);
    });

    it('isGestionnaire retourne false si l\'utilisateur n\'est pas assigné', function () {
        expect($this->salle->isGestionnaire($this->gestionnaire))->toBeFalse();
    });

    /* ── Validation / invalidation ────────────────────────────────────────── */

    it('un gestionnaire peut valider une réservation de sa salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->poste->id,
            'libelle_court'  => 'À valider',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        expect($this->gestionnaire->can('valider', $resa))->toBeTrue();

        $resa->valider($this->gestionnaire);

        expect($resa->fresh()->isValide())->toBeTrue()
            ->and($resa->fresh()->validated_by)->toBe($this->gestionnaire->id);
    });

    it('un gestionnaire ne peut pas valider une réservation d\'une autre salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);
        // gestionnaire n'est PAS assigné à autreSalle

        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->autrePoste->id,
            'libelle_court'  => 'Autre salle',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        expect($this->gestionnaire->can('valider', $resa))->toBeFalse();
    });

    it('un gestionnaire peut invalider une réservation validée de sa salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->poste->id,
            'libelle_court'  => 'À invalider',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::VALIDE,
            'validated_by'   => $this->admin->id,
            'validated_at'   => now(),
        ]);

        expect($this->gestionnaire->can('valider', $resa))->toBeTrue();

        $resa->invalider();

        expect($resa->fresh()->isEnAttente())->toBeTrue()
            ->and($resa->fresh()->validated_by)->toBeNull();
    });

    /* ── Suppression ──────────────────────────────────────────────────────── */

    it('un gestionnaire peut supprimer une réservation en attente de sa salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->poste->id,
            'libelle_court'  => 'À supprimer',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $resaId = $resa->id;
        $resa->delete();

        $this->assertDatabaseMissing('resa_reservations', ['id' => $resaId]);
    });

    /* ── Modification ─────────────────────────────────────────────────────── */

    it('un gestionnaire peut modifier une réservation de sa salle', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->poste->id,
            'libelle_court'  => 'Avant modif',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $resa->update(['libelle_court' => 'Après modif']);

        $this->assertDatabaseHas('resa_reservations', [
            'id'            => $resa->id,
            'libelle_court' => 'Après modif',
        ]);
    });

    it('un gestionnaire peut modifier la salle qui lui est assignée', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        expect($this->gestionnaire->can('update', $this->salle))->toBeTrue();
    });

    it('un gestionnaire ne peut pas modifier une salle qui ne lui est pas assignée', function () {
        $this->salle->gestionnaires()->attach($this->gestionnaire->id);

        expect($this->gestionnaire->can('update', $this->autreSalle))->toBeFalse();
    });

    it('un admin peut tout valider quelle que soit la salle', function () {
        $resa = Reservation::create([
            'user_id'        => $this->autreUser->id,
            'salle_poste_id' => $this->poste->id,
            'libelle_court'  => 'Admin valide',
            'debut'          => $this->debut,
            'fin'            => $this->fin,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        expect($this->admin->can('valider', $resa))->toBeTrue();
    });

});


