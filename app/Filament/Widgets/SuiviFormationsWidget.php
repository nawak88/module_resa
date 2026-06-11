<?php

namespace Modules\Resa\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;

class SuiviFormationsWidget extends Widget
{
    protected string $view = 'resa::filament.widgets.suivi-formations-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    protected static ?int $sort         = 3;

    protected int|string|array $columnSpan = 'full';

    // Filtres
    public ?int    $filtreFormationId = null;
    public ?int    $filtreStatut      = null; // null=tous, 0=en attente, 1=validé
    public string  $filtrePeriode     = 'mois'; // 'semaine' | 'mois' | 'annee' | 'tout'
    public string  $filtreNom         = '';

    public static function canView(): bool
    {
        $user = auth()->user();
        if (! $user) return false;
        //if ($user->can('resa::reservation.view')) return true;
        if ($user->can('resa::gestionnaire_salle')) return true;

        // Gestionnaire d'au moins une formation
        return Formation::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->exists();
    }

    public function isAdmin(): bool
    {
        return auth()->user()?->can('resa::gestionnaire_salle') ?? false;
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

    public function getReservations(): Collection
    {
        $formationIds = $this->getFormationsDisponibles()->pluck('id');

        $query = Reservation::with(['user', 'sallePoste.salle', 'validatedBy'])
            ->whereJsonLength('formations_utilisees', '>', 0);

        // Restreindre aux formations visibles
        $query->where(function ($q) use ($formationIds) {
            foreach ($formationIds as $id) {
                $q->orWhereJsonContains('formations_utilisees', (int) $id)
                  ->orWhereJsonContains('formations_utilisees', (string) $id);
            }
        });

        // Filtre formation spécifique
        if ($this->filtreFormationId) {
            $query->where(function ($q) {
                $q->orWhereJsonContains('formations_utilisees', (int) $this->filtreFormationId)
                  ->orWhereJsonContains('formations_utilisees', (string) $this->filtreFormationId);
            });
        }

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

            return [
                'id'           => $r->id,
                'stagiaires'   => $stagiaires,
                'formation'    => $formationLabels ?: '—',
                'poste'        => $r->sallePoste?->libelle_court ?? '—',
                'salle'        => $r->sallePoste?->salle?->libelle_court ?? '—',
                'debut'        => $r->debut?->format('d/m/Y H:i'),
                'fin'          => $r->fin?->format('H:i'),
                'valide'       => $r->isValide(),
                'validated_by' => $validatedBy ? trim("{$validatedBy->nom} {$validatedBy->prenom}") : null,
                'validated_at' => $r->validated_at?->format('d/m/Y'),
            ];
        });
    }

    public function updatedFiltreFormationId(): void {}
    public function updatedFiltreStatut(): void {}
    public function updatedFiltrePeriode(): void {}
    public function updatedFiltreNom(): void {}
}


