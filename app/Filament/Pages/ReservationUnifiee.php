<?php

namespace Modules\Resa\Filament\Pages;

use Filament\Pages\Page;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Filament\Widgets\CalendrierWidget;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\Salle;

class ReservationUnifiee extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string                 $navigationLabel = 'Réservations';
    protected static ?int                    $navigationSort  = 3;
    protected static string|\UnitEnum|null   $navigationGroup = 'Gestion';
    protected string                         $view            = 'resa::filament.pages.reservation-unifiee';

    // Navigation lieu/unité gérée par la page
    public string $etape       = 'lieu'; // lieu | unite | contenu
    public ?int   $lieuActive  = null;
    public ?int   $uniteActive = null;

    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $this->resoudreNavigation();
    }

    /* ── Navigation ───────────────────────────────────────────────────────── */

    private function resoudreNavigation(): void
    {
        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();
        $lieux = LieuUnite::whereIn('id', $lieuIds)->orderBy('ordre')->get();

        if ($lieux->count() > 1) {
            $this->etape = 'lieu';
            return;
        }

        if ($lieux->count() === 1) {
            $this->lieuActive = $lieux->first()->id;
        }

        $this->resoudreDepuisLieu();
    }

    private function resoudreDepuisLieu(): void
    {
        $query = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->orderBy('ordre');

        if ($this->lieuActive) {
            $query->where('lieu_unite_id', $this->lieuActive);
        }

        $unites = $query->get();

        if ($unites->count() > 1) {
            $this->etape = 'unite';
            return;
        }

        if ($unites->count() === 1) {
            $this->uniteActive = $unites->first()->id;
        }

        $this->etape = 'contenu';
    }

    public function selectLieu(int $id): void
    {
        $this->lieuActive  = $id;
        $this->uniteActive = null;
        $this->resoudreDepuisLieu();
    }

    public function selectUnite(int $id): void
    {
        $this->uniteActive = $id;
        $this->etape       = 'contenu';
    }

    public function retourLieux(): void
    {
        $this->lieuActive  = null;
        $this->uniteActive = null;
        $this->etape       = 'lieu';
    }

    public function retourUnites(): void
    {
        $this->uniteActive = null;
        $this->etape       = 'unite';
    }

    /* ── Données pour les cartes ──────────────────────────────────────────── */

    public function getLieux(): \Illuminate\Support\Collection
    {
        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();
        return LieuUnite::whereIn('id', $lieuIds)->orderBy('ordre')->get();
    }

    public function getUnites(): \Illuminate\Support\Collection
    {
        $query = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->orderBy('ordre');
        if ($this->lieuActive) {
            $query->where('lieu_unite_id', $this->lieuActive);
        }
        return $query->get();
    }

    /* ── Vérification si ressources existent pour l'unité ─────────────────── */

    public function hasRessources(): bool
    {
        if (! $this->uniteActive) return false;
        try {
            if (! \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id')) return false;
            return Ressource::where('actif', true)->where('unite_id', $this->uniteActive)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /* ── Widgets ──────────────────────────────────────────────────────────── */

    protected function getHeaderWidgets(): array
    {
        if ($this->etape !== 'contenu') return [];

        $widgets = [CalendrierWidget::class];

        if ($this->hasRessources()) {
            $widgets[] = RessourceGanttWidget::class;
        }

        return $widgets;
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return [
            'uniteActive' => $this->uniteActive,
            'lieuActive'  => $this->lieuActive,
            'filtreUnite' => $this->uniteActive,
        ];
    }
}


