<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Resa\Filament\Resources\SallePostes\SallePosteResource;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypePoste;
use Modules\Resa\Models\TypeSalle;
use Spatie\Permission\Models\Permission;

uses(Tests\TestCase::class, RefreshDatabase::class)->group('Resa');

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('resa'));
    foreach (['resa::sallePoste.index', 'resa::sallePoste.update', 'resa::gestionnaire_salle'] as $name) {
        Permission::findOrCreate($name, 'web');
    }
    $type = TypeSalle::create(['libelle_court' => 'Cours', 'mode' => 'classique']);
    $posteType = TypePoste::create(['libelle_court' => 'Standard']);
    $this->room = Salle::create(['libelle_court' => 'Salle', 'type_salle_id' => $type->id]);
    $this->poste = SallePoste::create(['salle_id' => $this->room->id, 'type_poste_id' => $posteType->id, 'libelle_court' => 'Poste']);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('reconnait les droits explicites de consultation et modification des postes', function (): void {
    $this->user->givePermissionTo(['resa::sallePoste.index', 'resa::sallePoste.update']);
    expect(SallePosteResource::canAccess())->toBeTrue()
        ->and(SallePosteResource::getEloquentQuery()->whereKey($this->poste->id)->exists())->toBeTrue()
        ->and(SallePosteResource::canEdit($this->poste))->toBeTrue();
});

it('conserve le filtrage des gestionnaires limites a leurs salles', function (): void {
    $this->user->givePermissionTo('resa::gestionnaire_salle');
    expect(SallePosteResource::canAccess())->toBeTrue()
        ->and(SallePosteResource::getEloquentQuery()->whereKey($this->poste->id)->exists())->toBeFalse();
    $this->room->gestionnaires()->attach($this->user);
    expect(SallePosteResource::getEloquentQuery()->whereKey($this->poste->id)->exists())->toBeTrue();
});

it('refuse les postes a un utilisateur sans droit', function (): void {
    expect(SallePosteResource::canAccess())->toBeFalse()
        ->and(SallePosteResource::canEdit($this->poste))->toBeFalse();
});
