<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\RessourceReservation;

class MesReservationsWidget extends Widget
{
    protected string $view = 'resa::filament.widgets.mes-reservations-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    // Affiché avant le calendrier (ReservationWidget, sort=2) : c'est ici que le
    // marin pointe sa présence, ça ne doit pas être caché en bas de page.
    protected static ?int $sort         = 0;

    protected int|string|array $columnSpan = 'full';

    public ?int $filtreStatut = null; // null=toutes, 0=en attente, 1=validée

    /**
     * Limite d'affichage (dashboard, compact) ; 0 = tout afficher (page dédiée).
     * Entier non-nullable : les propriétés Livewire typées `?int` ne restituent pas
     * fiablement `null` après un aller-retour hydrate/dehydrate (ex. suite à un
     * wire:click d'action) — 0 sert de valeur sentinelle "illimité".
     */
    public int $limite = 5;

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getReservations(): Collection
    {
        $userId = auth()->id();

        // user_id = le stagiaire ; created_by = qui a fait la demande — les deux
        // comptent comme "mes réservations", car on peut réserver pour quelqu'un
        // d'autre (ex. gestionnaire qui réserve pour un marin).
        $salles = Reservation::with(['sallePoste.salle', 'creePar', 'validatedBy'])
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhere('created_by', $userId))
            ->get()
            ->map(function (Reservation $r) use ($userId) {
                $formationLabels = Formation::whereIn('id', $r->formations_utilisees ?? [])
                    ->orderBy('ordre')
                    ->pluck('libelle_court')
                    ->join(', ');

                return [
                    'id'       => $r->id,
                    'type'     => 'salle',
                    'libelle'  => $r->libelle_court ?: ($r->sallePoste?->salle?->libelle_court ?? 'Salle'),
                    'formation' => $formationLabels ?: '—',
                    'poste'    => $r->sallePoste?->libelle_court ?? '—',
                    'detail'   => $r->sallePoste?->salle?->libelle_court ?? '—',
                    'debut'    => $r->debut,
                    'fin'      => $r->fin,
                    'valide'   => $r->isValide(),
                    'peut_modifier' => ! $r->isValide(),
                    'concernes' => $r->participantsNoms(),
                    'cree_par'  => $r->creePar ? trim("{$r->creePar->nom} {$r->creePar->prenom}") : null,
                    'validated_by' => $r->validatedBy ? trim("{$r->validatedBy->nom} {$r->validatedBy->prenom}") : null,
                    'validated_at' => $r->validated_at?->format('d/m/Y'),
                    // La pointeuse concerne le stagiaire lui-même (le jour J) : pas celui qui
                    // a juste créé la réservation pour quelqu'un d'autre.
                    'presence'     => $r->presence,
                    'peut_pointer' => $r->user_id === $userId && $r->peutPointer(),
                ];
            });

        $materiels = RessourceReservation::with(['ressource'])
            ->where('user_id', $userId)
            ->get()
            ->map(fn (RessourceReservation $r) => [
                'id'       => $r->id,
                'type'     => 'materiel',
                'libelle'  => $r->libelle ?: ($r->ressource?->libelle_court ?? 'Matériel'),
                'formation' => '—',
                'poste'    => '—',
                'detail'   => $r->ressource?->libelle_court ?? '—',
                'debut'    => $r->debut,
                'fin'      => $r->fin,
                'valide'   => $r->validation === RessourceReservation::VALIDE,
                'peut_modifier' => $r->validation !== RessourceReservation::VALIDE,
                'concernes' => [],
                'cree_par'  => null,
                'validated_by' => null,
                'validated_at' => null,
                // Pointeuse non prévue pour le matériel (hors périmètre actuel).
                'presence'     => null,
                'peut_pointer' => false,
            ]);

        $combinees = $salles->concat($materiels);

        // À venir (dont aujourd'hui) triées par date la plus proche d'abord — sinon une
        // réservation du jour (pointeuse à faire) finit enterrée sous des réservations
        // plus lointaines dans la vue compacte (dashboard, $limite tronqué) et le
        // bouton Présent/Absent n'est jamais visible sans aller sur "Voir tout".
        // Passées ensuite, plus récente d'abord.
        $avenir  = $combinees->filter(fn (array $r) => ! $r['fin']?->isPast())->sortBy('debut');
        $passees = $combinees->filter(fn (array $r) => $r['fin']?->isPast())->sortByDesc('debut');
        $toutes  = $avenir->concat($passees)->values();

        if ($this->filtreStatut !== null) {
            $toutes = $toutes->where('valide', (bool) $this->filtreStatut)->values();
        }

        return $toutes->map(fn (array $r) => [
            ...$r,
            'debut_fmt'  => $r['debut']?->format('d/m/Y H:i'),
            'fin_fmt'    => $r['fin']?->format('H:i'),
            'passee'     => $r['fin']?->isPast() ?? false,
        ]);
    }

    /** Version tronquée pour l'affichage compact (dashboard) ; cf. $limite. */
    public function getReservationsAffichees(): Collection
    {
        $toutes = $this->getReservations();

        return $this->limite > 0 ? $toutes->take($this->limite) : $toutes;
    }

    public function getStats(): array
    {
        $all = $this->getReservations();

        return [
            'total'      => $all->count(),
            'a_venir'    => $all->where('passee', false)->count(),
            'en_attente' => $all->where('valide', false)->where('passee', false)->count(),
        ];
    }

    public function supprimer(string $type, int $id): void
    {
        $userId = auth()->id();

        if ($type === 'materiel') {
            $reservation = RessourceReservation::where('id', $id)->where('user_id', $userId)->first();
        } else {
            $reservation = Reservation::where('id', $id)
                ->where(fn ($q) => $q->where('user_id', $userId)->orWhere('created_by', $userId))
                ->first();
        }

        if (! $reservation) {
            Notification::make()->title('Réservation introuvable.')->danger()->send();
            return;
        }

        $dejaValidee = $type === 'materiel'
            ? $reservation->validation === RessourceReservation::VALIDE
            : $reservation->isValide();

        if ($dejaValidee) {
            Notification::make()->title('Une réservation déjà validée ne peut plus être supprimée.')->danger()->send();
            return;
        }

        $reservation->delete();
        Notification::make()->title('Réservation supprimée')->success()->send();
    }

    public function pointer(int $id, bool $present): void
    {
        $reservation = Reservation::where('id', $id)->where('user_id', auth()->id())->first();

        if (! $reservation || ! $reservation->peutPointer()) {
            Notification::make()->title('Pointage impossible (hors créneau du jour, ou non validée).')->danger()->send();
            return;
        }

        $reservation->pointer($present, auth()->user());

        Notification::make()
            ->title($present ? 'Présence enregistrée' : 'Absence enregistrée')
            ->success()
            ->send();
    }

    public function updatedFiltreStatut(): void {}
}
