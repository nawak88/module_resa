<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Widgets\Widget;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\TypeSalle;

class ReservationWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'resa::filament.widgets.reservation-widget';

    protected static bool $isDiscovered = false;
    protected static bool $isLazy       = false;
    protected static ?int $sort         = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool { return true; }

    public ?int   $lieuActive  = null;
    public ?int   $uniteActive = null;
    public string $typeResa    = '';

    public function mount(): void
    {
        $this->lieuActive  = session('resa_nav_lieu')  ? (int) session('resa_nav_lieu')  : null;
        $this->uniteActive = session('resa_nav_unite') ? (int) session('resa_nav_unite') : null;
        $this->typeResa    = session('resa_nav_type', '');
    }

    // ─── Modal configuration (étape unique, tout réactif) ────────────────────

    public function configurerAction(): Action
    {
        return Action::make('configurer')
            ->label($this->uniteActive ? 'Modifier le choix' : 'Configurer ma réservation')
            ->icon($this->uniteActive ? 'heroicon-o-pencil-square' : 'heroicon-o-adjustments-horizontal')
            ->modalHeading('Configurer ma réservation')
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Valider')
            ->schema([
                Select::make('lieu_id')
                    ->label('Lieu')
                    ->options(fn () => $this->optionsLieux())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('unite_id', null) & $set('type_resa', null))
                    ->default($this->lieuActive),

                Select::make('unite_id')
                    ->label('Unité')
                    ->options(fn (Get $get) => $this->optionsUnites($get('lieu_id')))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('type_resa', null))
                    ->default($this->uniteActive),

                Radio::make('type_resa')
                    ->label('Type de réservation')
                    ->options(fn (Get $get) => $this->optionsTypes($get('unite_id')))
                    ->required()
                    ->inline(false)
                    ->default($this->typeResa ?: null)
                    ->visible(fn (Get $get) => (bool) $get('unite_id'))
                    ->live(),
            ])
            ->action(function (array $data): void {
                $this->lieuActive  = (int) $data['lieu_id'];
                $this->uniteActive = (int) $data['unite_id'];
                $this->typeResa    = $data['type_resa'];

                session([
                    'resa_nav_lieu'  => $this->lieuActive,
                    'resa_nav_unite' => $this->uniteActive,
                    'resa_nav_type'  => $this->typeResa,
                ]);
            });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getTypeSalleId(): ?int
    {
        return match ($this->typeResa) {
            'drone' => TypeSalle::where('libelle_court', 'like', '%drone%')
                ->orWhere('libelle_long', 'like', '%drone%')
                ->value('id'),
            default => null,
        };
    }

    public function getLieuLabel(): string
    {
        return $this->lieuActive ? (LieuUnite::find($this->lieuActive)?->libelle_court ?? '') : '';
    }

    public function getUniteLabel(): string
    {
        return $this->uniteActive ? (Unite::find($this->uniteActive)?->libelle_court ?? '') : '';
    }

    public function getTypeLabel(): string
    {
        return match ($this->typeResa) {
            'drone'     => ' Simulateur de drone',
            'ressource' => ' Ressources',
            default     => ' Salle classique',
        };
    }

    private function optionsTypes(?int $uniteId): array
    {
        if (! $uniteId) return [];

        $options = [];

        $salles = Salle::where('unite_id', $uniteId)->with('typeSalle')->get();

        if ($salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_CLASSIQUE)->isNotEmpty()) {
            $options['salle'] = '  Salle classique — réunion, formation, cours';
        }

        if ($salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_SIMULATEUR)->isNotEmpty()) {
            $options['drone'] = '  Salle Simulateur : Formations Ordinateurs';
        }

        if (Ressource::where('unite_id', $uniteId)->where('actif', true)->exists()) {
            $options['ressource'] = '  Ressources matérielles — matériels et équipements';
        }

        return $options;
    }

    private function optionsLieux(): array
    {
        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();

        return LieuUnite::whereIn('id', $lieuIds)
            ->orderBy('ordre')
            ->pluck('libelle_court', 'id')
            ->toArray();
    }

    private function optionsUnites(?int $lieuId): array
    {
        $query = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->orderBy('ordre');

        if ($lieuId) {
            $query->where('lieu_unite_id', $lieuId);
        }

        return $query->pluck('libelle_court', 'id')->toArray();
    }
}


