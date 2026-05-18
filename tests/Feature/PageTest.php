<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Resa\Filament\Resources;

use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Resa');

beforeEach(function () {
    Filament::setCurrentPanel(
        Filament::getPanel('resa'),
    );

    $this->admin = User::factory()->create();
    $this->admin->admin = 1;
    $this->admin->save();

    $this->user = User::factory()->create();
});

it('affiche la page Resource Index Salle Poste', function () {
    $this->actingAs($this->admin)
        ->get(Resources\SallePostes\SallePosteResource::getUrl('index'))
        ->assertSuccessful()
    ;
});


it('affiche la page Resource Index Salle', function () {
    $this->actingAs($this->admin)
        ->get(Resources\SalleResource::getUrl('index'))
        ->assertSuccessful()
    ;
});


it('affiche la page Resource Index Type Salle', function () {
    $this->actingAs($this->admin)
        ->get(Resources\TypeSalles\TypeSalleResource::getUrl('index'))
        ->assertSuccessful()
    ;
});

it('affiche la page Resource Index Type Poste', function () {
    $this->actingAs($this->admin)
        ->get(Resources\TypePostes\TypePosteResource::getUrl('index'))
        ->assertSuccessful()
    ;
});

it('affiche la page Resource Index Type Capacites', function () {
    $this->actingAs($this->admin)
        ->get(Resources\TypeCapacites\TypeCapaciteResource::getUrl('index'))
        ->assertSuccessful()
    ;
});

it('affiche la page Resource Index Reservation', function () {
    $this->actingAs($this->admin)
        ->get(Resources\ReservationResource::getUrl('index'))
        ->assertSuccessful()
    ;
});

