<?php

namespace Modules\Resa\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Validator;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\RessourceReservation;

class RessourceGanttWidget_OLD extends Widget
{
    protected string $view = 'resa::filament.widgets.ressource-gantt-widget';

    protected static bool $isLazy       = false;
    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    const HEURE_DEBUT = 8;
    const HEURE_FIN   = 18;

    public string $vue         = 'jour'; // 'jour' | 'semaine' | 'gantt'
    public string $currentDate = '';
    public string $semaineDebut = '';

    // Modal création
    public bool   $showModal        = false;
    public ?int   $modalRessourceId = null;
    public string $modalDebut       = '';
    public string $modalFin         = '';
    public string $modalLibelle     = '';
    public array  $modalErrors      = [];

    // Modal vue
    public bool $showViewModal        = false;
    public ?int $viewingReservationId = null;

    public bool $adminMode = false;

    public ?int    $filtreCategorie = null;
    public ?int    $filtreLieu      = null;
    public ?int    $filtreUnite     = null;
    public string  $etape           = 'gantt'; // 'lieu' | 'unite' | 'gantt'

    public function mount(): void
    {
        $this->adminMode    = auth()->user()?->can('resa::admin') ?? false;
        $this->currentDate  = now()->format('Y-m-d');
        $this->semaineDebut = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        // Si la catégorie a des ressources liées à une unité → flux lieu/unité
        if ($this->filtreCategorie) {
            $hasUnite = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
                ->where('actif', true)
                ->whereNotNull('unite_id')
                ->exists();
            $this->etape = $hasUnite ? 'lieu' : 'gantt';
        }
    }

    /* ── Navigation lieu/unité ────────────────────────────────────────────── */

    public function getLieux()
    {
        // Lieux qui ont des unités possédant des ressources de cette catégorie
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)
            ->whereNotNull('unite_id')
            ->pluck('unite_id');

        return LieuUnite::whereIn('id',
            Unite::whereIn('id', $uniteIds)->pluck('lieu_unite_id')
        )->orderBy('ordre')->get();
    }

    public function getUnites()
    {
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)
            ->whereNotNull('unite_id')
            ->pluck('unite_id');

        return Unite::whereIn('id', $uniteIds)
            ->when($this->filtreLieu, fn ($q) => $q->where('lieu_unite_id', $this->filtreLieu))
            ->orderBy('ordre')
            ->get();
    }

    public function selectLieu(int $id): void
    {
        $this->filtreLieu = $id;
        $this->etape      = 'unite';
    }

    public function selectUnite(int $id): void
    {
        $this->filtreUnite = $id;
        $this->etape       = 'gantt';
    }

    public function retourLieux(): void
    {
        $this->filtreLieu  = null;
        $this->filtreUnite = null;
        $this->etape       = 'lieu';
    }

    public function retourUnites(): void
    {
        $this->filtreUnite = null;
        $this->etape       = 'unite';
    }

    /* ── Vue ──────────────────────────────────────────────────────────────── */

    public function setVue(string $vue): void
    {
        $this->vue = $vue;
        if ($vue !== 'jour') {
            // Synchronise la semaine sur la date courante
            $this->semaineDebut = Carbon::parse($this->currentDate)
                ->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }
    }

    /* ── Navigation ───────────────────────────────────────────────────────── */

    public function precedent(): void
    {
        if ($this->vue === 'jour') {
            $this->currentDate = Carbon::parse($this->currentDate)->subDay()->format('Y-m-d');
        } else {
            $this->semaineDebut = Carbon::parse($this->semaineDebut)->subWeek()->format('Y-m-d');
        }
    }

    public function suivant(): void
    {
        if ($this->vue === 'jour') {
            $this->currentDate = Carbon::parse($this->currentDate)->addDay()->format('Y-m-d');
        } else {
            $this->semaineDebut = Carbon::parse($this->semaineDebut)->addWeek()->format('Y-m-d');
        }
    }

    public function auJourdHui(): void
    {
        $this->currentDate  = now()->format('Y-m-d');
        $this->semaineDebut = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    /* ── Données communes ─────────────────────────────────────────────────── */

    public function getHeures(): array
    {
        return range(self::HEURE_DEBUT, self::HEURE_FIN - 1);
    }

    public function getJoursSemaine(): array
    {
        $debut = Carbon::parse($this->semaineDebut);
        return collect(range(0, 6))->map(fn ($i) => $debut->copy()->addDays($i))->all();
    }

    public function getCategories()
    {
        $user    = auth()->user();
        $isAdmin = $user?->can('resa::admin') ?? false;
        $userUniteId = $user?->unite_id;

        return RessourceCategorie::with('ressourcesActives')
            ->when($this->filtreCategorie, fn ($q) => $q->where('id', $this->filtreCategorie))
            ->orderBy('ordre')
            ->get()
            ->map(function ($cat) use ($isAdmin, $userUniteId) {
                $cat->ressourcesActivesForUser = $cat->ressourcesActives->filter(function ($r) use ($isAdmin, $userUniteId) {
                    // Ressource sans unité → toujours visible
                    if (is_null($r->unite_id)) return true;
                    // Ressource avec unité filtrée par l'étape unite sélectionnée
                    if ($this->filtreUnite) return $r->unite_id === $this->filtreUnite;
                    // Admin → tout voir
                    if ($isAdmin) return true;
                    // User avec unité → sa propre unité
                    return $userUniteId && $r->unite_id === $userUniteId;
                })->values();
                return $cat;
            })
            ->filter(fn ($cat) => $cat->ressourcesActivesForUser->isNotEmpty());
    }

    /* ── Données vue JOUR ─────────────────────────────────────────────────── */

    public function getPlanningJour(): array
    {
        $debut = Carbon::parse($this->currentDate)->setTime(self::HEURE_DEBUT, 0);
        $fin   = Carbon::parse($this->currentDate)->setTime(self::HEURE_FIN, 0);

        $reservations = RessourceReservation::with(['user', 'ressource'])
            ->where('debut', '<', $fin)
            ->where('fin',   '>', $debut)
            ->get();

        $planning = [];
        foreach ($reservations as $resa) {
            $slotDebut = max((int) $resa->debut->format('H'), self::HEURE_DEBUT);
            $slotFin   = min((int) $resa->fin->format('H'), self::HEURE_FIN);
            if ($resa->fin->format('i') === '00') {
                $slotFin--;
            }
            for ($h = $slotDebut; $h <= $slotFin && $h < self::HEURE_FIN; $h++) {
                $planning[$resa->ressource_id][$h] = $resa;
            }
        }

        return $planning;
    }

    public function getRowsJour(): array
    {
        $heures   = $this->getHeures();
        $planning = $this->getPlanningJour();
        $rows     = [];

        foreach ($this->getCategories() as $categorie) {
            $ressourceRows = [];
            foreach ($categorie->ressourcesActivesForUser as $ressource) {
                $cells = [];
                $i     = 0;
                $count = count($heures);
                while ($i < $count) {
                    $heure = $heures[$i];
                    $resa  = $planning[$ressource->id][$heure] ?? null;
                    if ($resa) {
                        $colspan = 1;
                        for ($j = $i + 1; $j < $count; $j++) {
                            if (isset($planning[$ressource->id][$heures[$j]])
                                && $planning[$ressource->id][$heures[$j]]->id === $resa->id) {
                                $colspan++;
                            } else break;
                        }
                        $cells[] = ['type' => 'reservation', 'resa' => $resa, 'colspan' => $colspan];
                        $i += $colspan;
                    } else {
                        $cells[] = ['type' => 'empty', 'heure' => $heure, 'ressource_id' => $ressource->id];
                        $i++;
                    }
                }
                $ressourceRows[] = ['ressource' => $ressource, 'cells' => $cells];
            }
            if (! empty($ressourceRows)) {
                $rows[] = ['categorie' => $categorie, 'ressources' => $ressourceRows];
            }
        }

        return $rows;
    }

    /* ── Données vue SEMAINE ──────────────────────────────────────────────── */

    public function getPlanningData(): array
    {
        $debut = Carbon::parse($this->semaineDebut)->startOfDay();
        $fin   = $debut->copy()->addDays(6)->endOfDay();

        $reservations = RessourceReservation::with(['user', 'ressource'])
            ->where('debut', '<=', $fin)
            ->where('fin',   '>=', $debut)
            ->get();

        $planning = [];
        foreach ($reservations as $resa) {
            $day = Carbon::parse(max($resa->debut->format('Y-m-d'), $debut->format('Y-m-d')));
            $end = Carbon::parse(min($resa->fin->format('Y-m-d'),   $fin->format('Y-m-d')));
            while ($day <= $end) {
                $planning[$resa->ressource_id][$day->format('Y-m-d')] = $resa;
                $day->addDay();
            }
        }

        return $planning;
    }

    public function getRowsSemaine(): array
    {
        $jours    = $this->getJoursSemaine();
        $planning = $this->getPlanningData();
        $rows     = [];

        foreach ($this->getCategories() as $categorie) {
            $ressourceRows = [];
            foreach ($categorie->ressourcesActivesForUser as $ressource) {
                $cells = [];
                $i     = 0;
                while ($i < 7) {
                    $dateStr = $jours[$i]->format('Y-m-d');
                    $resa    = $planning[$ressource->id][$dateStr] ?? null;
                    if ($resa) {
                        $colspan = 1;
                        for ($j = $i + 1; $j < 7; $j++) {
                            $nextDate = $jours[$j]->format('Y-m-d');
                            if (isset($planning[$ressource->id][$nextDate])
                                && $planning[$ressource->id][$nextDate]->id === $resa->id) {
                                $colspan++;
                            } else break;
                        }
                        $cells[] = ['type' => 'reservation', 'resa' => $resa, 'colspan' => $colspan];
                        $i += $colspan;
                    } else {
                        $cells[] = [
                            'type'         => 'empty',
                            'date'         => $dateStr,
                            'ressource_id' => $ressource->id,
                            'weekend'      => $jours[$i]->isWeekend(),
                        ];
                        $i++;
                    }
                }
                $ressourceRows[] = ['ressource' => $ressource, 'cells' => $cells];
            }
            if (! empty($ressourceRows)) {
                $rows[] = ['categorie' => $categorie, 'ressources' => $ressourceRows];
            }
        }

        return $rows;
    }

    /* ── Données vue GANTT ────────────────────────────────────────────────── */

    /**
     * Retourne les réservations de la semaine groupées par ressource_id,
     * avec left% et width% calculés sur la timeline lun-dim 08h-18h.
     * Total slots = 7 jours × 10 heures = 70
     */
    public function getRowsGantt(): array
    {
        $debut = Carbon::parse($this->semaineDebut)->startOfDay();
        $fin   = $debut->copy()->addDays(6)->endOfDay();
        $total = 7 * (self::HEURE_FIN - self::HEURE_DEBUT); // 70

        $reservations = RessourceReservation::with(['user', 'ressource'])
            ->where('debut', '<', $fin)
            ->where('fin',   '>', $debut)
            ->orderBy('debut')
            ->get();

        // Indexer par ressource
        $byRessource = [];
        foreach ($reservations as $resa) {
            // Clamp aux bornes de la semaine + heures ouverture
            $resaDebut = $resa->debut->copy();
            $resaFin   = $resa->fin->copy();

            // Offset en heures depuis lun 08h00
            $offsetDebut = $debut->copy()->setTime(self::HEURE_DEBUT, 0)->diffInMinutes($resaDebut->max($debut->copy()->setTime(self::HEURE_DEBUT, 0)), false);
            $offsetFin   = $debut->copy()->setTime(self::HEURE_DEBUT, 0)->diffInMinutes($resaFin->min($fin->copy()->setTime(self::HEURE_FIN, 0)), false);

            $offsetDebut = max(0, $offsetDebut);
            $offsetFin   = min($total * 60, $offsetFin);

            if ($offsetFin <= $offsetDebut) continue;

            $left  = round($offsetDebut / ($total * 60) * 100, 4);
            $width = round(($offsetFin - $offsetDebut) / ($total * 60) * 100, 4);

            $byRessource[$resa->ressource_id][] = compact('resa', 'left', 'width');
        }

        $rows = [];
        foreach ($this->getCategories() as $categorie) {
            $ressourceRows = [];
            foreach ($categorie->ressourcesActivesForUser as $ressource) {
                $bars = $byRessource[$ressource->id] ?? [];
                $ressourceRows[] = ['ressource' => $ressource, 'bars' => $bars];
            }
            if (! empty($ressourceRows)) {
                $rows[] = ['categorie' => $categorie, 'ressources' => $ressourceRows];
            }
        }

        return $rows;
    }

    /* ── Création ─────────────────────────────────────────────────────────── */

    public function ouvrirModal(int $ressourceId, string $debutStr, ?string $finStr = null): void
    {
        $this->modalRessourceId = $ressourceId;
        $this->modalDebut       = $debutStr;
        $this->modalFin         = $finStr ?? $debutStr;
        $this->modalLibelle     = '';
        $this->modalErrors      = [];
        $this->showModal        = true;
    }

    public function fermerModal(): void
    {
        $this->showModal   = false;
        $this->modalErrors = [];
    }

    public function creerReservation(): void
    {
        $validator = Validator::make([
            'ressource_id' => $this->modalRessourceId,
            'debut'        => $this->modalDebut,
            'fin'          => $this->modalFin,
        ], [
            'ressource_id' => 'required|exists:resa_ressources,id',
            'debut'        => 'required|date',
            'fin'          => 'required|date|after:debut',
        ], [
            'fin.after'      => 'La fin doit être après le début.',
            'debut.required' => 'La date/heure de début est obligatoire.',
        ]);

        if ($validator->fails()) {
            $this->modalErrors = $validator->errors()->all();
            return;
        }

        $conflit = RessourceReservation::where('ressource_id', $this->modalRessourceId)
            ->where('debut', '<', $this->modalFin)
            ->where('fin',   '>', $this->modalDebut)
            ->exists();

        if ($conflit) {
            $this->modalErrors = ['Cette ressource est déjà réservée sur cette période.'];
            return;
        }

        RessourceReservation::create([
            'user_id'      => auth()->id(),
            'ressource_id' => $this->modalRessourceId,
            'libelle'      => $this->modalLibelle,
            'debut'        => $this->modalDebut,
            'fin'          => $this->modalFin,
            'validation'   => $this->adminMode ? RessourceReservation::VALIDE : RessourceReservation::EN_ATTENTE,
            'validated_by' => $this->adminMode ? auth()->id() : null,
            'validated_at' => $this->adminMode ? now() : null,
        ]);

        $this->showModal   = false;
        $this->modalErrors = [];
    }

    /* ── Vue / actions ────────────────────────────────────────────────────── */

    public function voirReservation(int $id): void
    {
        $this->viewingReservationId = $id;
        $this->showViewModal        = true;
    }

    public function fermerViewModal(): void
    {
        $this->showViewModal        = false;
        $this->viewingReservationId = null;
    }

    public function validerReservation(int $id): void
    {
        if (! $this->adminMode) return;
        RessourceReservation::find($id)?->valider(auth()->user());
        $this->fermerViewModal();
    }

    public function invaliderReservation(int $id): void
    {
        if (! $this->adminMode) return;
        RessourceReservation::find($id)?->invalider();
        $this->fermerViewModal();
    }

    public function supprimerReservation(int $id): void
    {
        $resa = RessourceReservation::find($id);
        if (! $resa) return;
        if (! $this->adminMode && $resa->user_id !== auth()->id()) return;
        $resa->delete();
        $this->fermerViewModal();
    }

    public function getViewingReservation(): ?RessourceReservation
    {
        if (! $this->viewingReservationId) return null;
        return RessourceReservation::with(['user', 'ressource.categorie', 'validatedBy'])
            ->find($this->viewingReservationId);
    }
}


