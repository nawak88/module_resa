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
                'user_id'        => null,
                'salle_poste_id' => null,
                'debut'          => null,
                'fin'            => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'libelle_court'  => 'required',
                'user_id'        => 'required',
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


