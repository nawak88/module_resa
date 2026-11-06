<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Widgets\Widget;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Formation;
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

    public static function canView(): bool
    {
        $user = auth()->user();

        // Un gestionnaire de formation "pur" (ni admin, ni gestionnaire de salle) a
        // déjà son tableau de bord dédié (SuiviFormationsWidget) : pas besoin de
        // l'assistant générique de configuration de réservation.
        if ($user?->can('resa::gestionnaire_formation')
            && ! $user->can('resa::gestionnaire_salle')
            && ! $user->can('resa::admin')
            && ! $user->can('resa::reservation.view')
        ) {
            return false;
        }

        return true;
    }

    public ?int   $lieuActive  = null;
    public ?int   $uniteActive = null;
    public string $typeResa    = '';

    public function mount(): void
    {
        $this->lieuActive  = session('resa_nav_lieu')  ? (int) session('resa_nav_lieu')  : null;
        $this->uniteActive = session('resa_nav_unite') ? (int) session('resa_nav_unite') : null;
        $this->typeResa    = session('resa_nav_type', '');

        // Un seul lieu ET une seule unité : on les résout tout de suite plutôt que de
        // faire passer l'utilisateur par un choix évident — la page d'accueil va
        // directement au choix du type de réservation (vue "dashboard"). Dès qu'il y a
        // un choix réel à faire (2+ lieux, ou 2+ unités dans le lieu), on repasse par
        // l'ancien parcours (modal "Configurer ma réservation") : uniteActive reste
        // null, cf. @if(! $this->uniteActive) dans la vue.
        if (! $this->lieuActive) {
            $lieux = $this->optionsLieux();
            if (count($lieux) === 1) {
                $this->lieuActive = array_key_first($lieux);
            }
        }

        if ($this->lieuActive && ! $this->uniteActive) {
            $unites = $this->optionsUnites($this->lieuActive);
            if (count($unites) === 1) {
                $this->uniteActive = array_key_first($unites);
            }
        }
    }

    /** Choix direct du type de réservation depuis la page d'accueil (sans modal). */
    public function selectionnerType(string $type): void
    {
        $this->typeResa = $type;

        session([
            'resa_nav_lieu'  => $this->lieuActive,
            'resa_nav_unite' => $this->uniteActive,
            'resa_nav_type'  => $this->typeResa,
        ]);
    }

    /** Revient au choix du type (garde le lieu/unité déjà résolus). */
    public function changerType(): void
    {
        $this->typeResa = '';
        session(['resa_nav_type' => '']);
    }

    // ─── Modal configuration (étape unique, tout réactif) ────────────────────

    public function configurerAction(): Action
    {
        return Action::make('configurer')
            ->label($this->uniteActive ? 'Modifier le choix' : 'Configurer ma réservation')
            ->icon($this->uniteActive ? 'heroicon-o-pencil-square' : 'heroicon-o-adjustments-horizontal')
            ->modalHeading('Configurer ma réservation')
            ->modalWidth('xl')
            ->modalSubmitActionLabel('Valider')
            ->schema([
                // Lieu et unité restent dans le formulaire (ils déterminent les options
                // du type de réservation) mais sont masqués et auto-sélectionnés tant
                // qu'il n'y a qu'un seul choix possible — un seul site actif pour
                // l'instant : pas besoin de faire cliquer sur une évidence, et le type
                // de réservation devient le premier choix visible à l'écran.
                Select::make('lieu_id')
                    ->label('Lieu')
                    ->options(fn () => $this->optionsLieux())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('unite_id', null) & $set('type_resa', null))
                    ->default(fn () => $this->lieuActive ?? (count($this->optionsLieux()) === 1 ? array_key_first($this->optionsLieux()) : null))
                    ->hidden(fn () => count($this->optionsLieux()) <= 1),

                Select::make('unite_id')
                    ->label('Unité')
                    ->options(fn (Get $get) => $this->optionsUnites($get('lieu_id')))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('type_resa', null))
                    ->default(fn (Get $get) => $this->uniteActive ?? (count($this->optionsUnites($get('lieu_id'))) === 1 ? array_key_first($this->optionsUnites($get('lieu_id'))) : null))
                    ->hidden(fn (Get $get) => count($this->optionsUnites($get('lieu_id'))) <= 1),

                ToggleButtons::make('type_resa')
                    ->label('Type de réservation')
                    ->options(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->label->all())
                    ->icons(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->icon->all())
                    ->tooltips(fn (Get $get) => $this->choixTypes($get('unite_id'))->map->tooltip->all())
                    // Pas de ->grouped() : ce rendu-là est en w-max (une seule ligne, sans
                    // retour), ça déborde du modal dès qu'il y a plus de 2-3 types (ici
                    // jusqu'à 4 : Salle classique, Simulateur, Ateliers, Ressources).
                    // ->inline() applique flex-wrap à la place : les boutons repassent à
                    // la ligne proprement.
                    ->inline()
                    ->required()
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
            'drone'     => 'Simulateur',
            'ressource' => 'Ressources',
            default     => 'Salle classique',
        };
    }

    /**
     * Ordre d'affichage des types de réservation (cartes du dashboard, ToggleButtons de
     * la modal) : Salle classique, Simulateurs, Ateliers (pas encore un type géré — à
     * ajouter ici le jour où il existe côté données), Ressources. Centralisé ici plutôt
     * que livré par l'ordre d'insertion dans choixTypes() : un seul endroit à modifier
     * pour réordonner, et chaque clé absente de $choix est simplement ignorée.
     */
    private const ORDRE_TYPES = ['salle', 'drone', 'atelier', 'ressource'];

    /**
     * Choix du type de réservation : libellé court (affiché sous l'icône dans la carte),
     * icône SVG (heroicon), et infobulle avec le détail — pour rester lisible en carte
     * tout en gardant l'information complète (ex. liste des formations du simulateur).
     *
     * @return \Illuminate\Support\Collection<string, array{label: string, icon: string, tooltip: ?string}>
     */
    public function choixTypes(?int $uniteId): \Illuminate\Support\Collection
    {
        if (! $uniteId) return collect();

        $choix = collect();

        $salles = Salle::where('unite_id', $uniteId)->with('typeSalle')->get();

        if ($salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_CLASSIQUE)->isNotEmpty()) {
            $choix['salle'] = [
                'label'   => 'Salle classique',
                'icon'    => 'heroicon-o-building-office-2',
                'tooltip' => 'Réunion, formation, cours',
            ];
        }

        $sallesSimulateur = $salles->filter(fn ($s) => $s->typeSalle?->mode === TypeSalle::MODE_SIMULATEUR);
        if ($sallesSimulateur->isNotEmpty()) {
            // Le libellé ne doit pas présumer du type de simulateur (drone, tir, etc.) :
            // on affiche les formations réellement disponibles dessus (en infobulle), pour
            // que le gestionnaire/utilisateur reconnaisse sa formation (ex. « 20mm »,
            // « MENTOR ») au lieu de se tromper de case faute d'un libellé générique « drone ».
            $formations = Formation::whereHas(
                'sallePostes',
                fn ($q) => $q->whereIn('salle_id', $sallesSimulateur->pluck('id')),
            )->orderBy('ordre')->pluck('libelle_court');

            $choix['drone'] = [
                'label'   => $sallesSimulateur->first()->typeSalle?->libelle_court ?? 'Simulateur',
                'icon'    => 'heroicon-o-cpu-chip',
                'tooltip' => $formations->isNotEmpty() ? $formations->join(', ') : null,
            ];
        }

        if (Ressource::where('unite_id', $uniteId)->where('actif', true)->exists()) {
            $choix['ressource'] = [
                'label'   => 'Ressources matérielles',
                'icon'    => 'heroicon-o-archive-box',
                'tooltip' => 'Matériels et équipements',
            ];
        }

        return $choix
            ->sortBy(function ($c, $type) {
                // array_search(...) ?: ... serait faux ici : la position 0 ("salle",
                // premier de l'ordre) est falsy et tomberait dans le fallback.
                $position = array_search($type, self::ORDRE_TYPES, true);

                return $position !== false ? $position : count(self::ORDRE_TYPES);
            })
            ->map(fn (array $c) => (object) $c);
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
