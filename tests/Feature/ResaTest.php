<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;
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
        Filament::getPanel('poseidon'),
    );

    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::validation_salle',  'web');

    $this->user  = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::reservation.view');
    $this->admin->givePermissionTo('resa::validation_salle');

    $this->seed(ResaDatabaseSeeder::class);

    $this->salle  = Salle::where('libelle_court', 'Salle 1')->firstOrFail();
    $this->poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
    $this->poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
    $this->poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
    $this->fpv    = TypeCapacite::where('libelle_court', 'FPV')->firstOrFail();
    $this->mm20   = TypeCapacite::where('libelle_court', '20mm')->firstOrFail();

    $this->debutLibre = now()->addWeek()->startOfWeek()->setTime(9, 0)->toDateTimeString();
    $this->finLibre   = now()->addWeek()->startOfWeek()->setTime(11, 0)->toDateTimeString();
    $this->debutPris  = now()->addWeek()->startOfWeek()->setTime(14, 0)->toDateTimeString();
    $this->finPrise   = now()->addWeek()->startOfWeek()->setTime(16, 0)->toDateTimeString();

});

/* ══════════════════════════════════════════════════════════════════════════════
 * HELPER — reproduit la logique de getEvents() sans FullCalendar
 * ══════════════════════════════════════════════════════════════════════════════ */

function getEventsForRole(?User $user, int $salleId): \Illuminate\Support\Collection
{
    $query = Reservation::query()
        ->whereHas('sallePoste', fn ($q) => $q->where('salle_id', $salleId));

    if ($user === null) {
        $query->where('validation', Reservation::VALIDE);
    } elseif ($user->can('resa::reservation.view')) {
        // admin - tout voir
    } else {
        $userId = $user->id;
        $query->where(function ($q) use ($userId) {
            $q->where('validation', Reservation::VALIDE)
              ->orWhere('user_id', $userId);
        });
    }

    return $query->get();
}

/* ══════════════════════════════════════════════════════════════════════════════
 * HELPER — reproduit la logique de validerCreneau() du widget
 * ══════════════════════════════════════════════════════════════════════════════ */

function validerCreneau(string $debut, string $fin): ?string
{
    $d = \Carbon\Carbon::parse($debut);
    $f = \Carbon\Carbon::parse($fin);

    if ($d->copy()->isWeekend()) return 'Reservation impossible le week-end.';
    if ($d->copy()->startOfDay()->lt(now()->startOfDay())) return 'Impossible de reserver dans le passe.';
    if ($f->copy()->lte($d)) return 'La date de fin doit etre superieure a la date de debut.';

    $debutMin = $d->hour * 60 + $d->minute;
    $finMin   = $f->hour * 60 + $f->minute;

    if ($debutMin < 8 * 60 || $debutMin >= 17 * 60) return 'Heure de debut invalide.';
    if ($finMin <= 8 * 60 || $finMin > 17 * 60)     return 'Heure de fin invalide.';

    return null;
}

/* ══════════════════════════════════════════════════════════════════════════════
 * HELPER — reproduit la logique de creation de reservation
 * ══════════════════════════════════════════════════════════════════════════════ */

function creerReservation(
    User $actingAs,
    SallePoste $poste,
    string $debut,
    string $fin,
    array $capacites,
    string $libelle,
    ?User $pourUser = null
): ?Reservation {
    // Validation metier
    //if (validerCreneau($debut, $fin)) return null;
    $erreur = validerCreneau($debut, $fin);
    if ($erreur) {
       // dump("validerCreneau bloque: $erreur");
        return null;
    }

    if (! $poste->isDisponible(new \DateTime($debut), new \DateTime($fin))) {
        // dump("isDisponible bloque");
        return null;
    }


    // Disponibilite
    if (! $poste->isDisponible(new \DateTime($debut), new \DateTime($fin))) return null;

    $isAdmin = $actingAs->can('resa::reservation.view');
    $userId  = $pourUser ? $pourUser->id : $actingAs->id;

    return Reservation::create([
        'user_id'             => $userId,
        'salle_poste_id'      => $poste->id,
        'libelle_court'       => $libelle,
        'debut'               => $debut,
        'fin'                 => $fin,
        'capacites_utilisees' => $capacites,
        'validation'          => $isAdmin ? Reservation::VALIDE : Reservation::EN_ATTENTE,
        'validated_by'        => $isAdmin ? $actingAs->id : null,
        'validated_at'        => $isAdmin ? now()          : null,
    ]);
}

/* ══════════════════════════════════════════════════════════════════════════════
 * VISITEUR NON CONNECTE
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Visiteur non connecte', function () {

    it('voit le widget calendrier', function () {
        Livewire::test(CalendrierWidget::class, ['salleActive' => $this->salle->id])
            ->assertSuccessful();
    });

    it('ne voit pas le bouton de reservation', function () {
        Livewire::test(CalendrierWidget::class, ['salleActive' => $this->salle->id])
            ->assertSee('Se connecter pour reserver')
            ->assertDontSee('Nouvelle reservation');
    });

    it('voit uniquement les reservations validees', function () {
        $resaValidee = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Resa validee',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
        ]);

        $resaEnAttente = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste2->id,
            'libelle_court'  => 'Resa en attente',
            'debut'          => $this->debutPris,
            'fin'            => $this->finPrise,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $events = getEventsForRole(null, $this->salle->id);

        expect($events->pluck('id'))->toContain($resaValidee->id);
        expect($events->pluck('id'))->not->toContain($resaEnAttente->id);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * UTILISATEUR CONNECTE
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Utilisateur connecte', function () {

    it('voit le bouton nouvelle reservation', function () {
        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, ['salleActive' => $this->salle->id])
            ->assertSee('Nouvelle reservation');
    });

    it('voit les reservations validees et les siennes en attente', function () {
        $autreUser = User::factory()->create();

        $resaAutreValidee = Reservation::create([
            'user_id'        => $autreUser->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Autre validee',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
        ]);

        $resaAutreAttente = Reservation::create([
            'user_id'        => $autreUser->id,
            'salle_poste_id' => $this->poste2->id,
            'libelle_court'  => 'Autre en attente',
            'debut'          => $this->debutPris,
            'fin'            => $this->finPrise,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $maPropre = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste3->id,
            'libelle_court'  => 'Ma resa en attente',
            'debut'          => now()->next('Tuesday')->setTime(9, 0)->toDateTimeString(),
            'fin'            => now()->next('Tuesday')->setTime(11, 0)->toDateTimeString(),
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $events = getEventsForRole($this->user, $this->salle->id);
        $ids    = $events->pluck('id');

        expect($ids)->toContain($resaAutreValidee->id);
        expect($ids)->not->toContain($resaAutreAttente->id);
        expect($ids)->toContain($maPropre->id);
    });

    it('peut creer une reservation sur un creneau libre', function () {
        $resa = creerReservation(
            actingAs:  $this->user,
            poste:     $this->poste1,
            debut:     $this->debutLibre,
            fin:       $this->finLibre,
            capacites: [$this->fpv->id],
            libelle:   'Ma reservation test',
        );

        expect($resa)->not->toBeNull();

        $this->assertDatabaseHas('resa_reservations', [
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Ma reservation test',
            'validation'     => Reservation::EN_ATTENTE,
        ]);
    });

    it('ne peut pas reserver sur un creneau deja pris', function () {
        // Premier booking sur poste1
        Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Resa existante',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
        ]);

        // Tentative de chevauchement
        $resa = creerReservation(
            actingAs:  $this->user,
            poste:     $this->poste1,
            debut:     $this->debutLibre,
            fin:       $this->finLibre,
            capacites: [$this->fpv->id],
            libelle:   'Tentative chevauchement',
        );

        expect($resa)->toBeNull();
        $this->assertDatabaseMissing('resa_reservations', [
            'libelle_court' => 'Tentative chevauchement',
        ]);
    });

    it('peut supprimer sa reservation en attente', function () {
        $resa = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'A supprimer',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        // Verifie la condition canModify
        expect($resa->isEnAttente())->toBeTrue();

        // Supprime comme le ferait l'action
        $resa->delete();

        $this->assertDatabaseMissing('resa_reservations', ['id' => $resa->id]);
    });

    it('ne peut pas supprimer une reservation validee', function () {
        $resa = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Validee non supprimable',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
        ]);

        // canModify = isEnAttente() AND (admin OR proprietaire)
        // isEnAttente() = false donc canModify = false
        expect($resa->isEnAttente())->toBeFalse();

        $this->assertDatabaseHas('resa_reservations', ['id' => $resa->id]);
    });

    it('ne peut pas reserver le week-end', function () {
        $samedi = now()->next('Saturday')->setTime(10, 0)->toDateTimeString();
        $finSam = now()->next('Saturday')->setTime(11, 0)->toDateTimeString();

        expect(validerCreneau($samedi, $finSam))->not->toBeNull();

        $resa = creerReservation(
            actingAs:  $this->user,
            poste:     $this->poste1,
            debut:     $samedi,
            fin:       $finSam,
            capacites: [$this->fpv->id],
            libelle:   'Resa week-end',
        );

        expect($resa)->toBeNull();
        $this->assertDatabaseMissing('resa_reservations', ['libelle_court' => 'Resa week-end']);
    });

    it('ne peut pas reserver avant 08h00', function () {
        $debutHors = now()->next('Monday')->setTime(6, 0)->toDateTimeString();
        $finHors   = now()->next('Monday')->setTime(7, 0)->toDateTimeString();

        expect(validerCreneau($debutHors, $finHors))->not->toBeNull();

        $resa = creerReservation(
            actingAs:  $this->user,
            poste:     $this->poste1,
            debut:     $debutHors,
            fin:       $finHors,
            capacites: [$this->fpv->id],
            libelle:   'Resa hors horaires matin',
        );

        expect($resa)->toBeNull();
        $this->assertDatabaseMissing('resa_reservations', ['libelle_court' => 'Resa hors horaires matin']);
    });

    it('ne peut pas reserver apres 17h00', function () {
        $debutHors = now()->next('Monday')->setTime(17, 0)->toDateTimeString();
        $finHors   = now()->next('Monday')->setTime(18, 0)->toDateTimeString();

        expect(validerCreneau($debutHors, $finHors))->not->toBeNull();

        $resa = creerReservation(
            actingAs:  $this->user,
            poste:     $this->poste1,
            debut:     $debutHors,
            fin:       $finHors,
            capacites: [$this->fpv->id],
            libelle:   'Resa apres 17h',
        );

        expect($resa)->toBeNull();
        $this->assertDatabaseMissing('resa_reservations', ['libelle_court' => 'Resa apres 17h']);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * ADMIN
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Admin', function () {

    it('voit toutes les reservations y compris en attente', function () {
        $autreUser = User::factory()->create();

        $resaAttente = Reservation::create([
            'user_id'        => $autreUser->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'En attente autre user',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        $events = getEventsForRole($this->admin, $this->salle->id);

        expect($events->pluck('id'))->toContain($resaAttente->id);
    });

    it('peut valider une reservation en attente', function () {
        $resa = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'A valider',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        expect($resa->isEnAttente())->toBeTrue();

        // Appelle directement la methode du model comme le ferait l'action
        $resa->valider($this->admin);

        $this->assertDatabaseHas('resa_reservations', [
            'id'           => $resa->id,
            'validation'   => Reservation::VALIDE,
            'validated_by' => $this->admin->id,
        ]);
    });

    it('peut invalider une reservation validee', function () {
        $resa = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'A invalider',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
            'validated_by'   => $this->admin->id,
            'validated_at'   => now(),
        ]);

        expect($resa->isValide())->toBeTrue();

        // Appelle directement la methode du model
        $resa->invalider();

        $this->assertDatabaseHas('resa_reservations', [
            'id'           => $resa->id,
            'validation'   => Reservation::EN_ATTENTE,
            'validated_by' => null,
        ]);
    });

    it('peut creer une reservation pour un autre utilisateur directement validee', function () {
        $resa = creerReservation(
            actingAs:  $this->admin,
            poste:     $this->poste1,
            debut:     $this->debutLibre,
            fin:       $this->finLibre,
            capacites: [$this->fpv->id],
            libelle:   'Resa admin pour user',
            pourUser:  $this->user,
        );

        expect($resa)->not->toBeNull();

        $this->assertDatabaseHas('resa_reservations', [
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Resa admin pour user',
            'validation'     => Reservation::VALIDE,
            'validated_by'   => $this->admin->id,
        ]);
    });

    it('peut supprimer une reservation validee', function () {
        $resa = Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Resa validee a supprimer',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::VALIDE,
        ]);

        // Admin peut supprimer meme une resa validee
        // canModify = isEnAttente() OR adminMode
        $isAdmin = $this->admin->can('resa::reservation.view');
        expect($isAdmin)->toBeTrue();

        $resa->delete();

        $this->assertDatabaseMissing('resa_reservations', ['id' => $resa->id]);
    });

});
