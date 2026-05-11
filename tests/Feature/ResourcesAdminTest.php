<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Resources;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;
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

    Permission::findOrCreate('resa::validation_salle', 'web');
    Permission::findOrCreate('resa::reservation.view', 'web');

    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::validation_salle');
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
 * TypeCapaciteResource
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('TypeCapaciteResource', function () {

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
        $record = TypeCapacite::firstOrFail();
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
