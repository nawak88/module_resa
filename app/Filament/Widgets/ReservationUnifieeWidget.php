<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Widgets\Widget;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\Salle;

class ReservationUnifieeWidget extends Widget
{
    protected string $view = 'resa::filament.widgets.reservation-unifiee-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy      = false;
    protected static ?int $sort        = 10;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    public string $etape       = 'lieu';
    public ?int   $lieuActive  = null;
    public ?int   $uniteActive = null;

    public function mount(): void
    {
        $this->resoudreNavigation();
    }

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
}


