<?php

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Livewire\Livewire;
use Modules\Resa\Database\Seeders\ResaDatabaseSeeder;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Mail\ReservationCree;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\Formation;
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
        Filament::getPanel('poseidon'),
    );

    Permission::findOrCreate('resa::reservation.view', 'web');
    Permission::findOrCreate('resa::admin',  'web');

    $this->user  = User::factory()->create();
    $this->admin = User::factory()->create(['admin' => 1]);
    $this->admin->givePermissionTo('resa::reservation.view');
    $this->admin->givePermissionTo('resa::admin');

    $this->seed(ResaDatabaseSeeder::class);

    $this->salle  = Salle::where('libelle_court', 'Salle 1')->firstOrFail();
    $this->poste1 = SallePoste::where('libelle_court', 'Poste 1')->firstOrFail();
    $this->poste2 = SallePoste::where('libelle_court', 'Poste 2')->firstOrFail();
    $this->poste3 = SallePoste::where('libelle_court', 'Poste 3')->firstOrFail();
    $this->fpv    = Formation::where('libelle_court', 'FPV')->firstOrFail();
    $this->mm20   = Formation::where('libelle_court', '20mm')->firstOrFail();

    $this->debutLibre = now()->next('Monday')->setTime(9, 0)->toDateTimeString();
    $this->finLibre   = now()->next('Monday')->setTime(11, 0)->toDateTimeString();
    $this->debutPris  = now()->next('Monday')->setTime(14, 0)->toDateTimeString();
    $this->finPrise   = now()->next('Monday')->setTime(16, 0)->toDateTimeString();
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

function validerCreneau(string $debut, string $fin, ?int $userId = null): ?string
{
    $d = \Carbon\Carbon::parse($debut);
    $f = \Carbon\Carbon::parse($fin);

    if ($d->isWeekend()) return 'Reservation impossible le week-end.';
    if ($d->copy()->startOfDay()->lt(now()->startOfDay())) return 'Impossible de reserver dans le passe.';
    if ($f->lte($d)) return 'La date de fin doit etre superieure a la date de debut.';

    $debutMin = $d->hour * 60 + $d->minute;
    $finMin   = $f->hour * 60 + $f->minute;

    if ($debutMin < 8 * 60 || $debutMin >= 17 * 60) return 'Heure de debut invalide.';
    if ($finMin <= 8 * 60 || $finMin > 17 * 60)     return 'Heure de fin invalide.';

    // Verifie chevauchement pour le user
    if ($userId) {
        $dejaReserve = \Modules\Resa\Models\Reservation::where('user_id', $userId)
            ->where('debut', '<', $fin)
            ->where('fin',   '>', $debut)
            ->exists();

        if ($dejaReserve) return 'Vous avez deja une reservation sur ce creneau.';
    }

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
    if (validerCreneau($debut, $fin, $actingAs->id)) return null;

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
        'formations_utilisees' => $capacites,
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
        Livewire::test(CalendrierWidget::class, ['salleActive' => $this->salle->id, 'etapeNavigation' => 'calendrier', 'typeSalleActive' => $this->salle->type_salle_id])
            ->assertSuccessful();
    });

    it('ne voit pas le bouton de reservation', function () {
        Livewire::test(CalendrierWidget::class, ['salleActive' => $this->salle->id, 'etapeNavigation' => 'calendrier', 'typeSalleActive' => $this->salle->type_salle_id])
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
            ->test(CalendrierWidget::class, ['salleActive' => $this->salle->id, 'etapeNavigation' => 'calendrier', 'typeSalleActive' => $this->salle->type_salle_id])
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

/* ══════════════════════════════════════════════════════════════════════════════
 * SALLE CLASSIQUE
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('Salle classique', function () {

    beforeEach(function () {
        // Recupere la salle classique et son poste fictif crees par le seeder
        $this->salleClassique  = Salle::where('libelle_court', 'Salle 2')->firstOrFail();
        $this->posteFictif     = SallePoste::where('salle_id', $this->salleClassique->id)
                                           ->where('libelle_court', 'Salle entiere')
                                           ->firstOrFail();
        $this->typeSalleActive = $this->salleClassique->type_salle_id;

        $this->debutClassique = now()->addWeek()->startOfWeek()->setTime(9, 0)->toDateTimeString();
        $this->finClassique   = now()->addWeek()->startOfWeek()->setTime(11, 0)->toDateTimeString();
    });

    /* ── Logique metier ──────────────────────────────────────────────────── */

    it('la salle classique a un TypeSalle en mode classique', function () {
        $typeSalle = TypeSalle::find($this->salleClassique->type_salle_id);

        expect($typeSalle)->not->toBeNull();
        expect($typeSalle->isClassique())->toBeTrue();
        expect($typeSalle->isSimulateur())->toBeFalse();
    });

    it('le poste fictif Salle entiere existe et est system', function () {
        expect($this->posteFictif)->not->toBeNull();
        expect($this->posteFictif->isSystem())->toBeTrue();
        expect($this->posteFictif->libelle_court)->toBe('Salle entiere');
    });

    it('peut reserver la salle entiere sur un creneau libre', function () {
        $erreur = validerCreneau($this->debutClassique, $this->finClassique, $this->user->id);
        expect($erreur)->toBeNull();

        $disponible = $this->posteFictif->isDisponible(
            new \DateTime($this->debutClassique),
            new \DateTime($this->finClassique)
        );
        expect($disponible)->toBeTrue();

        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Reunion section B',
            'debut'               => $this->debutClassique,
            'fin'                 => $this->finClassique,
            'formations_utilisees' => [],
            'validation'          => Reservation::EN_ATTENTE,
        ]);

        $this->assertDatabaseHas('resa_reservations', [
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->posteFictif->id,
            'libelle_court'  => 'Reunion section B',
            'validation'     => Reservation::EN_ATTENTE,
        ]);
    });

    it('ne peut pas reserver si la salle est deja occupee sur ce creneau', function () {
        // Premiere reservation
        Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Resa existante',
            'debut'               => $this->debutClassique,
            'fin'                 => $this->finClassique,
            'formations_utilisees' => [],
            'validation'          => Reservation::VALIDE,
        ]);

        // Tentative de chevauchement sur la meme salle
        $disponible = $this->posteFictif->isDisponible(
            new \DateTime($this->debutClassique),
            new \DateTime($this->finClassique)
        );

        expect($disponible)->toBeFalse();

        $this->assertDatabaseMissing('resa_reservations', [
            'libelle_court' => 'Tentative salle occupee',
        ]);
    });

    it('deux users ne peuvent pas reserver la meme salle classique en meme temps', function () {
        $autreUser = User::factory()->create();

        // User 1 reserve
        Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Resa user 1',
            'debut'               => $this->debutClassique,
            'fin'                 => $this->finClassique,
            'formations_utilisees' => [],
            'validation'          => Reservation::VALIDE,
        ]);

        // User 2 tente de reserver le meme creneau
        $disponible = $this->posteFictif->isDisponible(
            new \DateTime($this->debutClassique),
            new \DateTime($this->finClassique)
        );

        expect($disponible)->toBeFalse();
    });

    it('peut reserver des creneaux non chevauchants dans la meme salle', function () {
        // Resa matin
        Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Resa matin',
            'debut'               => now()->addWeek()->startOfWeek()->setTime(8, 0)->toDateTimeString(),
            'fin'                 => now()->addWeek()->startOfWeek()->setTime(10, 0)->toDateTimeString(),
            'formations_utilisees' => [],
            'validation'          => Reservation::VALIDE,
        ]);

        // Resa apres-midi — ne chevauche pas
        $debutAM = now()->addWeek()->startOfWeek()->setTime(13, 0)->toDateTimeString();
        $finAM   = now()->addWeek()->startOfWeek()->setTime(15, 0)->toDateTimeString();

        $disponible = $this->posteFictif->isDisponible(
            new \DateTime($debutAM),
            new \DateTime($finAM)
        );

        expect($disponible)->toBeTrue();

        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Resa apres-midi',
            'debut'               => $debutAM,
            'fin'                 => $finAM,
            'formations_utilisees' => [],
            'validation'          => Reservation::EN_ATTENTE,
        ]);

        $this->assertDatabaseHas('resa_reservations', [
            'libelle_court' => 'Resa apres-midi',
        ]);
    });

    it('les capacites utilisees sont vides en mode classique', function () {
        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'Resa classique sans capacites',
            'debut'               => $this->debutClassique,
            'fin'                 => $this->finClassique,
            'formations_utilisees' => [],
            'validation'          => Reservation::EN_ATTENTE,
        ]);

        expect($resa->formations_utilisees)->toBeArray();
        expect($resa->formations_utilisees)->toBeEmpty();
    });

    it('le poste fictif est exclu des ressources du calendrier', function () {
        // En mode classique getResources() doit retourner []
        // On verifie que le poste fictif n\'apparait pas dans une requete simulateur
        $postesVisibles = SallePoste::where('salle_id', $this->salleClassique->id)
            ->where('libelle_court', '!=', 'Salle entiere')
            ->get();

        expect($postesVisibles)->toBeEmpty();
    });

    it('admin peut valider une reservation classique', function () {
        $resa = Reservation::create([
            'user_id'             => $this->user->id,
            'salle_poste_id'      => $this->posteFictif->id,
            'libelle_court'       => 'A valider classique',
            'debut'               => $this->debutClassique,
            'fin'                 => $this->finClassique,
            'formations_utilisees' => [],
            'validation'          => Reservation::EN_ATTENTE,
        ]);

        $resa->valider($this->admin);

        $this->assertDatabaseHas('resa_reservations', [
            'id'           => $resa->id,
            'validation'   => Reservation::VALIDE,
            'validated_by' => $this->admin->id,
        ]);
    });

    it('les regles horaires s\'appliquent aussi en mode classique', function () {
        $samedi = now()->next('Saturday')->setTime(10, 0)->toDateTimeString();
        $finSam = now()->next('Saturday')->setTime(11, 0)->toDateTimeString();

        expect(validerCreneau($samedi, $finSam))->not->toBeNull();

        $hors = now()->next('Monday')->setTime(6, 0)->toDateTimeString();
        $finH = now()->next('Monday')->setTime(7, 0)->toDateTimeString();

        expect(validerCreneau($hors, $finH))->not->toBeNull();
    });

    /* ── Navigation ──────────────────────────────────────────────────────── */

    it('le widget s\'affiche en mode calendrier pour la salle classique', function () {
        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class, [
                'salleActive'      => $this->salleClassique->id,
                'etapeNavigation'  => 'calendrier',
                'typeSalleActive'  => $this->typeSalleActive,
            ])
            ->assertSuccessful();
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * Navigation lieu / unité — résolution automatique
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('CalendrierWidget — navigation lieu et unité', function () {

    beforeEach(function () {
        $this->typeSalle = TypeSalle::firstOrFail();
    });

    /* helper : crée lieu + unite + salle liés */
    function makeLieuUniteSalle(TypeSalle $type, string $lieu, string $unite, string $salle): array
    {
        $l = LieuUnite::create(['libelle_court' => $lieu, 'ordre' => 1]);
        $u = Unite::create(['libelle_court' => $unite, 'libelle_long' => $unite, 'lieu_unite_id' => $l->id]);
        $s = Salle::create(['libelle_court' => $salle, 'type_salle_id' => $type->id, 'unite_id' => $u->id]);
        return [$l, $u, $s];
    }

    it('affiche le choix lieu quand plusieurs lieux existent', function () {
        makeLieuUniteSalle($this->typeSalle, 'Toulon', 'ERM', 'Salle TLN');
        makeLieuUniteSalle($this->typeSalle, 'Brest',  'BEM', 'Salle BRT');

        $widget = Livewire::actingAs($this->user)->test(CalendrierWidget::class);

        $widget->assertSet('etapeNavigation', 'lieu');
    });

    it('saute le choix lieu et affiche les unités quand 1 seul lieu', function () {
        [$lieu, , ] = makeLieuUniteSalle($this->typeSalle, 'Toulon', 'ERM', 'Salle TLN 1');
        $unite2 = Unite::create(['libelle_court' => 'ECTM', 'libelle_long' => 'ECTM', 'lieu_unite_id' => $lieu->id]);
        Salle::create(['libelle_court' => 'Salle TLN 2', 'type_salle_id' => $this->typeSalle->id, 'unite_id' => $unite2->id]);

        $widget = Livewire::actingAs($this->user)->test(CalendrierWidget::class);

        $widget->assertSet('etapeNavigation', 'unite')
               ->assertSet('lieuActive', $lieu->id);
    });

    it('saute lieu et unité quand 1 seul lieu et 1 seule unité', function () {
        makeLieuUniteSalle($this->typeSalle, 'Toulon', 'ERM', 'Salle A');
        makeLieuUniteSalle($this->typeSalle, 'Toulon bis', 'ERM bis', 'Salle B');

        // Forcer 1 seul lieu + 1 seule unité avec 2 salles
        Salle::query()->delete();
        $lieu  = LieuUnite::create(['libelle_court' => 'Unique', 'ordre' => 99]);
        $unite = Unite::create(['libelle_court' => 'U1', 'libelle_long' => 'U1', 'lieu_unite_id' => $lieu->id]);
        Salle::create(['libelle_court' => 'S1', 'type_salle_id' => $this->typeSalle->id, 'unite_id' => $unite->id]);
        Salle::create(['libelle_court' => 'S2', 'type_salle_id' => $this->typeSalle->id, 'unite_id' => $unite->id]);

        $widget = Livewire::actingAs($this->user)->test(CalendrierWidget::class);

        // 1 lieu + 1 unite + 2 salles → doit aller à l'étape salle
        $widget->assertSet('etapeNavigation', 'salle')
               ->assertSet('lieuActive', $lieu->id)
               ->assertSet('uniteActive', $unite->id);
    });

    it('ignore le flow lieu/unite si aucune salle n\'a de unite_id', function () {
        // Les salles du seeder n'ont pas de unite_id
        $widget = Livewire::actingAs($this->user)->test(CalendrierWidget::class);

        $widget->assertSet('lieuActive', null)
               ->assertSet('uniteActive', null);
        // doit être sur une étape "classique" (pas lieu ni unite)
        expect($widget->get('etapeNavigation'))->not->toBe('lieu')
            ->and($widget->get('etapeNavigation'))->not->toBe('unite');
    });

    it('selectLieu positionne lieuActive et passe à l\'étape unite', function () {
        [$lieu, , ] = makeLieuUniteSalle($this->typeSalle, 'Toulon', 'ERM',  'Salle TLN 1');
        $u2 = Unite::create(['libelle_court' => 'ECTM', 'libelle_long' => 'ECTM', 'lieu_unite_id' => $lieu->id]);
        Salle::create(['libelle_court' => 'Salle TLN 2', 'type_salle_id' => $this->typeSalle->id, 'unite_id' => $u2->id]);

        makeLieuUniteSalle($this->typeSalle, 'Brest', 'BEM', 'Salle BRT');

        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class)
            ->assertSet('etapeNavigation', 'lieu')
            ->call('selectLieu', $lieu->id)
            ->assertSet('lieuActive', $lieu->id)
            ->assertSet('etapeNavigation', 'unite');
    });

    it('selectUnite positionne uniteActive et passe à l\'étape suivante', function () {
        [$lieu, $unite, ] = makeLieuUniteSalle($this->typeSalle, 'Toulon', 'ERM', 'Salle TLN');
        makeLieuUniteSalle($this->typeSalle, 'Brest', 'BEM', 'Salle BRT');

        Livewire::actingAs($this->user)
            ->test(CalendrierWidget::class)
            ->call('selectLieu', $lieu->id)
            ->call('selectUnite', $unite->id)
            ->assertSet('uniteActive', $unite->id)
            // 1 seule salle dans cette unité → calendrier direct
            ->assertSet('etapeNavigation', 'calendrier')
            ->assertSet('salleActive', Salle::where('unite_id', $unite->id)->first()->id);
    });

});

/* ══════════════════════════════════════════════════════════════════════════════
 * Notification email à la création d'une réservation
 * ══════════════════════════════════════════════════════════════════════════════ */

describe('ReservationObserver — notification email', function () {

    it('envoie un email quand la salle a un email renseigné', function () {
        Mail::fake();

        $this->salle->update(['email' => 'responsable@example.com']);

        Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Test notification',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        Mail::assertSent(ReservationCree::class, fn ($mail) =>
            $mail->hasTo('responsable@example.com')
        );
    });

    it('envoie un email à chaque adresse si plusieurs séparées par virgule', function () {
        Mail::fake();

        $this->salle->update(['email' => 'alpha@example.com, beta@example.com']);

        Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Multi-destinataires',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        Mail::assertSent(ReservationCree::class, 2);
        Mail::assertSent(ReservationCree::class, fn ($mail) => $mail->hasTo('alpha@example.com'));
        Mail::assertSent(ReservationCree::class, fn ($mail) => $mail->hasTo('beta@example.com'));
    });

    it('n\'envoie aucun email si le champ email est null', function () {
        Mail::fake();

        $this->salle->update(['email' => null]);

        Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Sans email',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        Mail::assertNothingSent();
    });

    it('n\'envoie aucun email si le champ email est une chaîne vide', function () {
        Mail::fake();

        $this->salle->update(['email' => '']);

        Reservation::create([
            'user_id'        => $this->user->id,
            'salle_poste_id' => $this->poste1->id,
            'libelle_court'  => 'Email vide',
            'debut'          => $this->debutLibre,
            'fin'            => $this->finLibre,
            'validation'     => Reservation::EN_ATTENTE,
        ]);

        Mail::assertNothingSent();
    });

});


