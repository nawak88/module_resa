<?php

namespace Modules\Resa\Filament\Widgets;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\SuiviFormationsWidget\Actions\VoirStagiaireAction;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;

class SuiviFormationsWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'resa::filament.widgets.suivi-formations-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    protected static ?int $sort         = 1;

    protected int|string|array $columnSpan = 'full';

    // Filtres
    public ?int    $filtreFormationId = null;
    public ?int    $filtreStatut      = null; // null=tous, 0=en attente, 1=validé
    public string  $filtrePeriode     = 'mois'; // 'semaine' | 'mois' | 'annee' | 'tout'
    public string  $filtreNom         = '';

    // Incrémenté à chaque (in)validation : utilisé dans la clé Livewire du calendrier
    // embarqué pour forcer son remontage complet (données et couleurs à jour), plutôt
    // que de compter sur l'événement JS global "calendar--refresh" (qui dépend du
    // chargement différé — x-load — du calendrier et peut rater la fenêtre).
    public int $calendarVersion = 0;

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        if ($user->can('resa::gestionnaire_salle')) return true;

        // Gestionnaire d'au moins une formation
        return Formation::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->exists();
    }

    public function mount(): void
    {
        // Par défaut, le gestionnaire voit toutes ses formations (filtreFormationId
        // reste null = "Toutes") ; il choisit lui-même de filtrer sur un onglet précis.
    }

    public function isAdmin(): bool
    {
        return auth()->user()?->can('resa::gestionnaire_salle') ?? false;
    }

    public function afficherOnglets(): bool
    {
        return ! $this->isAdmin() && $this->getFormationsDisponibles()->count() > 1;
    }

    public function selectionnerFormation(?int $id): void
    {
        $this->filtreFormationId = $id;
    }

    /**
     * Ouvre l'assistant de création de réservation du calendrier transversal embarqué,
     * pour la formation actuellement affichée (onglet actif, ou la première formation
     * gérée à défaut). La salle est déduite automatiquement : en priorité la salle dont
     * le gestionnaire a la charge, sinon la première salle disposant de postes
     * configurés pour cette formation.
     */
    public function ouvrirNouvelleReservation(): void
    {
        $formationId = $this->filtreFormationId ?: $this->getFormationsDisponibles()->first()?->id;

        if (! $formationId) {
            Notification::make()->title('Aucune formation disponible.')->danger()->send();
            return;
        }

        $userId = auth()->id();

        $salle = Salle::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $userId))
            ->whereHas('sallePostes.formations', fn ($q) => $q->where('resa_formations.id', $formationId))
            ->first()
            ?? Salle::whereHas('sallePostes.formations', fn ($q) => $q->where('resa_formations.id', $formationId))->first();

        if (! $salle) {
            Notification::make()
                ->title('Aucune salle n\'a de poste configuré pour cette formation.')
                ->body('Rattachez des postes à cette formation depuis Resa > Postes de salle.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('resa-ouvrir-nouvelle-reservation', salleId: $salle->id, formationId: $formationId);
    }

    public function getFormationsDisponibles(): Collection
    {
        if ($this->isAdmin()) {
            return Formation::orderBy('ordre')->get();
        }

        return Formation::whereHas('gestionnaires', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('ordre')
            ->get();
    }

    /** IDs de formation à inclure dans les requêtes : la formation active, ou toutes celles visibles. */
    private function formationIdsPourQuery(): Collection
    {
        if ($this->filtreFormationId) {
            return collect([$this->filtreFormationId]);
        }

        return $this->getFormationsDisponibles()->pluck('id');
    }

    private function scopeFormations($query, Collection $formationIds): void
    {
        $query->where(function ($q) use ($formationIds) {
            foreach ($formationIds as $id) {
                $q->orWhereJsonContains('formations_utilisees', (int) $id)
                  ->orWhereJsonContains('formations_utilisees', (string) $id);
            }
        });
    }

    /* ── Cartes de statistiques (indépendantes des filtres période/statut/nom) ── */

    public function getStats(): array
    {
        $formationIds = $this->formationIdsPourQuery();

        $base = Reservation::query()->whereJsonLength('formations_utilisees', '>', 0);
        $this->scopeFormations($base, $formationIds);

        $aVenir = (clone $base)->where('debut', '>=', now())->count();
        $enAttente = (clone $base)->where('debut', '>=', now())->where('validation', Reservation::EN_ATTENTE)->count();
        $ceMois = (clone $base)->whereBetween('debut', [now()->startOfMonth(), now()->endOfMonth()])->count();

        return [
            'nb_formations' => $this->getFormationsDisponibles()->count(),
            'a_venir'       => $aVenir,
            'en_attente'    => $enAttente,
            'ce_mois'       => $ceMois,
        ];
    }

    /** IDs transmis au CalendrierWidget (mode transversal) pour filtrer par formation. */
    public function getFormationIdsCalendrier(): array
    {
        return $this->formationIdsPourQuery()->values()->all();
    }

    /* ── Tableau détaillé (avec filtres période/statut/nom) ───────────────── */

    public function getReservations(): Collection
    {
        $formationIds = $this->formationIdsPourQuery();

        $query = Reservation::with(['user', 'sallePoste.salle', 'validatedBy'])
            ->whereJsonLength('formations_utilisees', '>', 0);

        $this->scopeFormations($query, $formationIds);

        // Filtre statut
        if ($this->filtreStatut !== null) {
            $query->where('validation', $this->filtreStatut);
        }

        // Filtre période
        match ($this->filtrePeriode) {
            'semaine' => $query->where('debut', '>=', now()->startOfWeek()),
            'mois'    => $query->where('debut', '>=', now()->startOfMonth()),
            'annee'   => $query->where('debut', '>=', now()->startOfYear()),
            default   => null,
        };

        // Filtre nom stagiaire (cherche dans user principal ET utilisateurs annexes via data->users)
        if ($this->filtreNom !== '') {
            $matchingUserIds = User::where('nom', 'like', '%' . $this->filtreNom . '%')
                ->orWhere('prenom', 'like', '%' . $this->filtreNom . '%')
                ->pluck('id');

            $query->where(function ($q) use ($matchingUserIds) {
                $q->whereIn('user_id', $matchingUserIds);
                foreach ($matchingUserIds as $uid) {
                    $q->orWhereJsonContains('data->users', (int) $uid)
                      ->orWhereJsonContains('data->users', (string) $uid);
                }
            });
        }

        // Pré-charger tous les users nécessaires en une requête
        $reservations = $query->orderBy('debut', 'desc')->get();
        $allUserIds = $reservations->flatMap(function (Reservation $r) {
            $ids = collect($r->data['users'] ?? [])->filter()->values();
            if ($r->user_id) $ids->push($r->user_id);
            if ($r->created_by) $ids->push($r->created_by);
            return $ids;
        })->unique()->filter();

        $usersMap = User::whereIn('id', $allUserIds)->get()->keyBy('id');

        return $reservations->map(function (Reservation $r) use ($usersMap) {
            $formationLabels = Formation::whereIn('id', $r->formations_utilisees ?? [])
                ->orderBy('ordre')
                ->pluck('libelle_court')
                ->join(', ');

            // Tous les stagiaires depuis data.users, fallback sur user_id
            $userIds = collect($r->data['users'] ?? [])->filter()->values();
            if ($userIds->isEmpty() && $r->user_id) {
                $userIds = collect([$r->user_id]);
            }
            $stagiaires = $userIds->map(fn ($id) => $usersMap->get($id))
                ->filter()
                ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                ->filter()
                ->values()
                ->toArray();

            $validatedBy = $r->validated_by ? $usersMap->get($r->validated_by) : null;
            $creePar     = $r->created_by ? $usersMap->get($r->created_by) : null;

            return [
                'id'           => $r->id,
                'stagiaires'   => $stagiaires,
                'cree_par'     => $creePar ? trim("{$creePar->nom} {$creePar->prenom}") : null,
                'formation'    => $formationLabels ?: '—',
                'poste'        => $r->sallePoste?->libelle_court ?? '—',
                'salle'        => $r->sallePoste?->salle?->libelle_court ?? '—',
                'debut'        => $r->debut?->format('d/m/Y H:i'),
                'fin'          => $r->fin?->format('H:i'),
                'valide'       => $r->isValide(),
                'validated_by' => $validatedBy ? trim("{$validatedBy->nom} {$validatedBy->prenom}") : null,
                'validated_at' => $r->validated_at?->format('d/m/Y'),
                'peut_valider' => auth()->user()?->can('valider', $r) ?? false,
                // Pointeuse : le marin pointe normalement lui-même (widget "Mes
                // réservations") ; le gestionnaire ne peut le faire qu'en secours, avec
                // les mêmes droits que pour la validation.
                'presence'       => $r->presence,
                'peut_pointer'   => $r->peutPointer() && (auth()->user()?->can('valider', $r) ?? false),
            ];
        });
    }

    public function toggleValidation(int $reservationId): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (! auth()->user()?->can('valider', $reservation)) {
            Notification::make()->title("Vous n'êtes pas autorisé à valider cette réservation.")->danger()->send();
            return;
        }

        if ($reservation->isValide()) {
            $reservation->invalider();
            Notification::make()->title('Réservation invalidée')->warning()->send();
        } else {
            $reservation->valider(auth()->user());
            Notification::make()->title('Réservation validée')->success()->send();
        }

        // Le calendrier (CalendrierWidget) est un composant Livewire séparé : on force
        // son remontage complet (clé Livewire) pour que la couleur de l'événement
        // (gris/vert) suive immédiatement le changement de statut.
        $this->calendarVersion++;
        $this->dispatch('calendar--refresh');
    }

    public function pointerGestionnaire(int $reservationId, bool $present): void
    {
        $reservation = Reservation::findOrFail($reservationId);

        if (! auth()->user()?->can('valider', $reservation)) {
            Notification::make()->title("Vous n'êtes pas autorisé à pointer cette réservation.")->danger()->send();
            return;
        }

        if (! $reservation->peutPointer()) {
            Notification::make()->title('Pointage possible uniquement le jour de la séance, une fois validée.')->danger()->send();
            return;
        }

        $reservation->pointer($present, auth()->user());

        Notification::make()
            ->title($present ? 'Présence enregistrée' : 'Absence enregistrée')
            ->success()
            ->send();
    }

    public function voirStagiaireAction(): Action
    {
        return VoirStagiaireAction::make($this);
    }

    public function updatedFiltreFormationId(): void {}
    public function updatedFiltreStatut(): void {}
    public function updatedFiltrePeriode(): void {}
    public function updatedFiltreNom(): void {}
}
