<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Livewire\Attributes\On;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarResource;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\FiltresAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaClassiqueAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\VoirReservationAction;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeSalle;

class CalendrierWidget extends CalendarWidget
{
    protected string $view = 'resa::filament.widgets.calendrier-widget';

    protected static bool $isDiscovered = false;

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected bool $eventClickEnabled  = true;
    protected bool $eventDragEnabled   = true;
    protected bool $eventResizeEnabled = true;
    protected bool $dateClickEnabled   = true;
    protected bool $dateSelectEnabled  = true;
    protected ?string $locale          = 'fr';
    protected bool $useFilamentTimezone = true;

    // Navigation
    public string  $etapeNavigation  = 'lieu'; // lieu | unite | type_salle | salle | calendrier
    public ?int    $lieuActive       = null;
    public ?int    $uniteActive      = null;
    public ?int    $typeSalleActive  = null;
    public ?int    $salleActive      = null;
    public bool    $skipTypeSalleNav = false;
    public ?string $modeForce        = null;  // 'classique' | 'simulateur' — forcé depuis le parent

    // Mode "formation" : calendrier transversal (toutes salles) filtré par formation,
    // utilisé par le dashboard du gestionnaire de formation (SuiviFormationsWidget).
    public bool $modeFormationGlobale = false;

    // Etat modal
    public bool    $adminMode      = false;
    public ?int    $recordEnCours  = null;
    public ?string $recordTitre    = null;

    // Etapes Wizard simulateur
    public ?string $etapeDebut        = null;
    public ?string $etapeFin          = null;
    public array   $etapeFormations    = [];
    public ?int    $etapePoste        = null;
    public array   $postesDisponibles = [];

    // Filtres calendrier
    public array $filtrePostes    = [];
    public array $filtreFormations = [];

    // Cache interne (non persisté en Livewire)
    private ?string $_modeActifCache    = null;
    private ?array  $_weekendDatesCache = null;

    /* ── Initialisation ───────────────────────────────────────────────────── */

    public function mount(): void
    {
        if ($this->modeFormationGlobale) {
            // Vue transversale toutes salles, filtrée par formation(s) via $filtreFormations :
            // pas de navigation lieu/unité/salle ; les colonnes (postes) sont résolues dans
            // getResources() à partir des salles qui ont des réservations pour ces formations.
            $this->adminMode  = true;
            $this->etapeNavigation = 'calendrier';

            return;
        }

        $user = auth()->user();
        $this->adminMode = $user?->can('resa::reservation.view') ?? false;

        // Un gestionnaire de salle a aussi accès au mode admin pour sa salle
        if (!$this->adminMode && $user?->can('resa::gestionnaire_salle')) {
            $this->adminMode = true;
        }

        // Un gestionnaire de formation (type de capacité) doit voir les réservations
        // en attente qui utilisent sa formation, pour pouvoir les valider.
        if (!$this->adminMode && $user?->can('resa::gestionnaire_formation')) {
            $this->adminMode = true;
        }

        // Si salleActive est deja defini (injection de tests ou lien direct)
        // on ne resout pas la navigation automatiquement
        if ($this->salleActive !== null) {
            $this->etapeNavigation = 'calendrier';
            $salle = Salle::find($this->salleActive);
            if ($this->typeSalleActive === null) {
                $this->typeSalleActive = $salle?->type_salle_id;
            }
            if ($this->uniteActive === null) {
                $this->uniteActive = $salle?->unite_id;
            }
            if ($this->lieuActive === null && $this->uniteActive) {
                $this->lieuActive = Unite::find($this->uniteActive)?->lieu_unite_id;
            }
            return;
        }

        // Si uniteActive est injecté depuis une page parente, sauter la navigation lieu/unité
        if ($this->uniteActive !== null) {
            if ($this->lieuActive === null) {
                $this->lieuActive = Unite::find($this->uniteActive)?->lieu_unite_id;
            }
            $this->resoudreDepuisUnite();
            return;
        }

        $this->resoudreNavigation();
    }

    /* ── Resolution automatique de la navigation ──────────────────────────── */

    protected function resoudreNavigation(): void
    {
        // Si aucune salle n'a de unite_id → ancien comportement sans lieu/unite
        if (! Salle::whereNotNull('unite_id')->exists()) {
            $this->resoudreDepuisUnite();
            return;
        }

        $lieuIds = Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))
            ->pluck('lieu_unite_id')->unique();
        $lieux = LieuUnite::whereIn('id', $lieuIds)->orderBy('ordre')->get();

        if ($lieux->count() > 1) {
            $this->etapeNavigation = 'lieu';
            return;
        }

        if ($lieux->count() === 1) {
            $this->lieuActive = $lieux->first()->id;
        }

        $this->resoudreDepuisLieu();
    }

    private function resoudreDepuisLieu(): void
    {
        $sallesAvecUnite = Salle::whereNotNull('unite_id')->pluck('unite_id');
        $query = Unite::whereIn('id', $sallesAvecUnite)->orderBy('ordre');

        if ($this->lieuActive) {
            $query->where('lieu_unite_id', $this->lieuActive);
        }

        $unites = $query->get();

        if ($unites->count() > 1) {
            $this->etapeNavigation = 'unite';
            return;
        }

        if ($unites->count() === 1) {
            $this->uniteActive = $unites->first()->id;
        }

        $this->resoudreDepuisUnite();
    }

    protected function resoudreDepuisUnite(): void
    {
        $salles = $this->getSallesScope()->with('typeSalle')->orderBy('ordre')->get();

        if ($salles->count() === 1) {
            $this->salleActive     = $salles->first()->id;
            $this->typeSalleActive = $salles->first()->type_salle_id;
            $this->etapeNavigation = 'calendrier';
            return;
        }

        // Si typeSalleActive est déjà injecté OU qu'on demande à sauter la navigation
        if ($this->typeSalleActive !== null || $this->skipTypeSalleNav) {
            $sallesFiltrees = $this->typeSalleActive
                ? $salles->where('type_salle_id', $this->typeSalleActive)
                : $salles;

            if ($sallesFiltrees->count() === 1) {
                $this->salleActive     = $sallesFiltrees->first()->id;
                $this->typeSalleActive = $sallesFiltrees->first()->type_salle_id;
            }
            // Toujours aller direct au calendrier (salleActive peut rester null → toutes les salles)
            $this->etapeNavigation = 'calendrier';
            return;
        }

        $typeSalles = TypeSalle::whereIn('id', $salles->pluck('type_salle_id')->unique())->orderBy('ordre')->get();

        if ($typeSalles->count() === 1) {
            $this->typeSalleActive = $typeSalles->first()->id;
            $this->etapeNavigation = 'salle';
            return;
        }

        $this->etapeNavigation = 'type_salle';
    }

    public function getSallesScopePublic(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->getSallesScope();
    }

    private function getSallesScope(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Salle::query();

        if ($this->uniteActive) {
            $query->where('unite_id', $this->uniteActive);
        } elseif ($this->lieuActive) {
            $uniteIds = Unite::where('lieu_unite_id', $this->lieuActive)->pluck('id');
            $query->whereIn('unite_id', $uniteIds);
        }

        // modeForce prend toujours la priorité (contrainte externe du parent)
        if ($this->modeForce !== null) {
            $typeIds = TypeSalle::where('mode', $this->modeForce)->pluck('id');
            $query->whereIn('type_salle_id', $typeIds);
        } elseif ($this->typeSalleActive !== null) {
            $query->where('type_salle_id', $this->typeSalleActive);
        }

        return $query;
    }

    /* ── Sélection lieu ──────────────────────────────────────────────────── */

    public function selectLieu(int $id): void
    {
        $this->lieuActive  = $id;
        $this->uniteActive = null;
        $this->resoudreDepuisLieu();
    }

    /* ── Sélection unité ─────────────────────────────────────────────────── */

    public function selectUnite(int $id): void
    {
        $this->uniteActive = $id;
        $this->resoudreDepuisUnite();
    }

    /* ── Sélection type salle ─────────────────────────────────────────────── */

    public function selectTypeSalle(int $id): void
    {
        $this->_modeActifCache = null;
        $this->typeSalleActive = $id;

        $salles = $this->getSallesScope()->where('type_salle_id', $id)->get();
        if ($salles->count() === 1) {
            $this->salleActive     = $salles->first()->id;
            $this->etapeNavigation = 'calendrier';
        } else {
            $this->etapeNavigation = 'salle';
        }
    }

    /* ── Sélection de salle ───────────────────────────────────────────────── */

    public function selectSalle(int $id): void
    {
        $this->_modeActifCache = null;
        $this->salleActive     = $id;
        $this->typeSalleActive = Salle::find($id)?->type_salle_id ?? $this->typeSalleActive;
        $this->etapeNavigation = 'calendrier';
        $this->filtrePostes    = [];
        $this->filtreFormations = [];
    }

    /* ── Retours ──────────────────────────────────────────────────────────── */

    public function retourSalles(): void
    {
        $this->salleActive     = null;
        $this->etapeNavigation = 'salle';
    }

    public function retourTypeSalles(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->etapeNavigation = 'type_salle';
    }

    public function retourUnites(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->etapeNavigation = 'unite';
    }

    public function retourLieux(): void
    {
        $this->salleActive     = null;
        $this->typeSalleActive = null;
        $this->uniteActive     = null;
        $this->etapeNavigation = 'lieu';
    }

    /* ── Mode de la salle active ──────────────────────────────────────────── */

    public function getModeActif(): string
    {
        if ($this->_modeActifCache !== null) return $this->_modeActifCache;
        if ($this->modeForce !== null) return $this->_modeActifCache = $this->modeForce;
        if (! $this->typeSalleActive) return $this->_modeActifCache = TypeSalle::MODE_SIMULATEUR;
        return $this->_modeActifCache = TypeSalle::find($this->typeSalleActive)?->mode ?? TypeSalle::MODE_SIMULATEUR;
    }

    public function isModeClassique(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_CLASSIQUE;
    }

    public function isModeSimulateur(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_SIMULATEUR;
    }

    public function getCalendarView(): CalendarViewType
    {
        return $this->isModeSimulateur()
            ? CalendarViewType::ResourceTimeGridWeek
            : CalendarViewType::TimeGridWeek;
    }

    public function getHeading(): ?string
    {
        if ($this->salleActive) {
            return 'Calendrier - ' . (Salle::find($this->salleActive)?->libelle_court ?? '');
        }
        return 'Reservations';
    }

    /* ── Droits ───────────────────────────────────────────────────────────── */

    private function canUserReserve(): bool
    {
        return auth()->check();
    }

    /**
     * Vrai administrateur (voit et modifie tout), à distinguer de $adminMode qui vaut
     * aussi true pour un simple gestionnaire de salle/formation — celui-ci voit toutes
     * les réservations (cf. getEvents()) mais ne doit pouvoir modifier/supprimer que
     * les siennes, cf. VoirReservationAction::footerActions().
     */
    public function estAdminComplet(): bool
    {
        $user = auth()->user();

        return ($user?->can('resa::admin') ?? false) || ($user?->can('resa::reservation.view') ?? false);
    }

    /* ── Helpers publics ──────────────────────────────────────────────────── */

    public function validerCreneauPublic(string $debut, string $fin, ?int $excludeId = null): ?string
    {
        $d = \Carbon\Carbon::parse($debut);
        $f = \Carbon\Carbon::parse($fin);

        if ($d->isWeekend()) return 'Reservation impossible le week-end.';
        if ($d->lt(now())) return 'Impossible de reserver un creneau deja passe.';
        if ($f->lte($d)) return 'La date de fin doit etre superieure a la date de debut.';

        $debutMin = $d->hour * 60 + $d->minute;
        $finMin   = $f->hour * 60 + $f->minute;

        if ($debutMin < 8 * 60 || $debutMin >= 18 * 60) return "L'heure de debut doit etre entre 08h00 et 18h00.";
        if ($finMin <= 8 * 60 || $finMin > 18 * 60)     return "L'heure de fin doit etre entre 08h00 et 18h00.";

        return null;
    }

    public function getWeekendDatesPublic(): array
    {
        if ($this->_weekendDatesCache !== null) return $this->_weekendDatesCache;

        $dates = [];
        $day   = now()->startOfDay()->copy();
        $end   = now()->addMonths(6);

        while ($day <= $end) {
            if ($day->isWeekend()) {
                $dates[] = $day->format('Y-m-d');
            }
            $day->addDay();
        }

        return $this->_weekendDatesCache = $dates;
    }

    /* ── Ressources Guava ─────────────────────────────────────────────────── */

    public function getResources(): Collection|array
    {
        if ($this->modeFormationGlobale) {
            return $this->getResourcesFormationGlobale();
        }

        // Mode classique : pas de colonnes
        if ($this->isModeClassique()) return [];

        if (! $this->salleActive) return [];

        return SallePoste::query()
            ->where('salle_id', $this->salleActive)
            ->where('libelle_court', '!=', 'Salle entiere') // exclut le poste fictif
            ->orderBy('ordre')
            ->get();
    }

    /**
     * Colonnes du calendrier transversal "formation" : un poste par poste configuré
     * pour au moins une des formations filtrées (relation SallePoste::formations),
     * avec la salle dans le libellé pour lever l'ambiguïté quand plusieurs salles
     * sont concernées. Basé sur la configuration des postes, pas sur l'historique des
     * réservations : sinon une formation sans réservation n'affiche aucune colonne
     * même si des postes lui sont bien rattachés (voir SallePosteForm — CheckboxList
     * « Formations disponibles sur ce poste »).
     */
    /** IDs des salles dont au moins un poste est configuré pour une des formations filtrées. */
    private function sallesFormationGlobale(): \Illuminate\Support\Collection
    {
        if (empty($this->filtreFormations)) return collect();

        return SallePoste::query()
            ->whereHas('formations', fn ($q) => $q->whereIn('resa_formations.id', $this->filtreFormations))
            ->pluck('salle_id')
            ->unique()
            ->values();
    }

    private function getResourcesFormationGlobale(): Collection
    {
        if (empty($this->filtreFormations)) return collect();

        // Tous les postes des salles concernées, pas seulement ceux configurés pour la
        // formation filtrée : le gestionnaire doit voir l'occupation complète de la
        // salle (autres formations y compris), même s'il ne peut valider/modifier que
        // ce qui relève de la sienne (cf. getEvents() et ReservationPolicy::valider()).
        $postes = SallePoste::query()
            ->where('libelle_court', '!=', 'Salle entiere') // exclut le poste fictif
            ->whereIn('salle_id', $this->sallesFormationGlobale())
            ->with('salle')
            ->orderBy('salle_id')
            ->orderBy('ordre')
            ->get();

        $plusieursSalles = $postes->pluck('salle_id')->unique()->count() > 1;

        return $postes->map(function (SallePoste $poste) use ($plusieursSalles) {
            $titre = $plusieursSalles
                ? trim(($poste->salle?->libelle_court ?? '') . ' — ' . $poste->libelle_court)
                : $poste->libelle_court;

            return \Guava\Calendar\ValueObjects\CalendarResource::make($poste->id)->title($titre ?? '');
        });
    }

    /* ── Evenements ───────────────────────────────────────────────────────── */

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        $query = Reservation::query()
            ->with(['sallePoste', 'sallePoste.salle', 'user'])
            ->where('debut', '<=', $info->end)
            ->where('fin',   '>=', $info->start);

        if ($this->adminMode) {
            // tout voir
        } elseif (auth()->check()) {
            $userId = auth()->id();
            $query->where(function ($q) use ($userId) {
                $q->where('validation', Reservation::VALIDE)
                  ->orWhere('user_id', $userId);
            });
        } else {
            $query->where('validation', Reservation::VALIDE);
        }

        if ($this->modeFormationGlobale) {
            // Le gestionnaire voit toute l'occupation des salles concernées par ses
            // formations — y compris les réservations d'autres formations — mais ne
            // peut valider/modifier que ce qui relève de la sienne (ReservationPolicy,
            // VoirReservationAction::footerActions()). Donc pas de filtre JSON par
            // formation ici (cf. plus bas), seulement un scope par salle.
            $query->whereHas('sallePoste', fn ($q) =>
                $q->whereIn('salle_id', $this->sallesFormationGlobale())
            );
        } elseif ($this->salleActive) {
            $query->whereHas('sallePoste', fn ($q) =>
                $q->where('salle_id', $this->salleActive)
            );
        } elseif ($this->uniteActive) {
            $sallesQuery = Salle::where('unite_id', $this->uniteActive);
            if ($this->modeForce !== null) {
                $typeIds = TypeSalle::where('mode', $this->modeForce)->pluck('id');
                $sallesQuery->whereIn('type_salle_id', $typeIds);
            } elseif ($this->typeSalleActive !== null) {
                $sallesQuery->where('type_salle_id', $this->typeSalleActive);
            }
            $salleIds = $sallesQuery->pluck('id');
            $query->whereHas('sallePoste', fn ($q) =>
                $q->whereIn('salle_id', $salleIds)
            );
        }

        if (! empty($this->filtrePostes)) {
            $query->whereIn('salle_poste_id', $this->filtrePostes);
        }

        if (! empty($this->filtreFormations) && ! $this->modeFormationGlobale) {
            $query->where(function ($q) {
                foreach ($this->filtreFormations as $id) {
                    $q->orWhereJsonContains('formations_utilisees', (int) $id)
                      ->orWhereJsonContains('formations_utilisees', (string) $id);
                }
            });
        }

        return $query;
    }

    /* ── Options FullCalendar ─────────────────────────────────────────────── */

    public function getOptions(): array
    {
        $simulateur = $this->isModeSimulateur();

        $dayView      = $simulateur ? 'resourceTimeGridDay'      : 'timeGridDay';
        $weekView     = $simulateur ? 'resourceTimeGridWeek'     : 'timeGridWeek';
        $timelineView = $simulateur ? 'resourceTimelineWeek'     : null;

        $endButtons = $simulateur
            ? "{$dayView},{$weekView},{$timelineView},dayGridMonth,listWeek,listMonth"
            : "{$dayView},{$weekView},dayGridMonth,listWeek,listMonth";

        $options = [
            'nowIndicator' => true,
            'slotDuration' => '00:30:00',
            //lotHeight'   => 60,
            'height'       => 'auto',
            'selectable'   => true,
            'editable'     => auth()->check(),
            'hiddenDays'          => [0, 6],
            'datesAboveResources' => true,
            'headerToolbar' => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => $endButtons,
            ],
            'buttonText' => [
                'today'                   => "Aujourd'hui",
                'timeGridDay'             => 'Jour',
                'timeGridWeek'            => 'Semaine',
                'resourceTimeGridDay'     => 'Jour',
                'resourceTimeGridWeek'    => 'Grille',
                'resourceTimelineWeek'    => 'Timeline',
                'dayGridMonth'            => 'Mois',
                'listWeek'                => 'Liste sem.',
                'listMonth'               => 'Liste mois',
            ],
            'locale'      => 'fr',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '18:00:00',
            'allDaySlot'  => false,
            'scrollTime'  => '08:00:00',
        ];

        if ($simulateur) {
            $options['views'] = [
                'resourceTimelineWeek' => [
                    'slotDuration'      => '01:00:00',
                    'slotMinTime'       => '08:00:00',
                    'slotMaxTime'       => '18:00:00',
                    'scrollTime'        => '08:00:00',
                ],
            ];
        }

        return $options;
    }

    /* ── Header actions ───────────────────────────────────────────────────── */

    public function getHeaderActions(): array
    {
        $actions = [];

        if ($this->etapeNavigation === 'calendrier') {
            $nbSalles    = $this->getSallesScope()->where('type_salle_id', $this->typeSalleActive)->count();
            $nbTypes     = TypeSalle::whereIn('id', $this->getSallesScope()->pluck('type_salle_id')->unique())->count();
            $nbUnites    = $this->uniteActive ? 0 : ($this->lieuActive
                ? Unite::where('lieu_unite_id', $this->lieuActive)->whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->count()
                : 0);
            $nbLieux     = LieuUnite::whereIn('id',
                Unite::whereIn('id', Salle::whereNotNull('unite_id')->pluck('unite_id'))->pluck('lieu_unite_id')->unique()
            )->count();

            if ($nbSalles > 1) {
                // $actions[] = Action::make('retour_salle')
                //     ->label('Changer de salle')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourSalles());
            } elseif ($nbTypes > 1) {
                // $actions[] = Action::make('retour_type_salle')
                //     ->label('Changer de type')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourTypeSalles());
            } elseif ($nbUnites > 1) {
                // $actions[] = Action::make('retour_unite')
                //     ->label('Changer d\'unité')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourUnites());
            } elseif ($nbLieux > 1) {
                // $actions[] = Action::make('retour_lieu')
                //     ->label('Changer de lieu')
                //     ->color('gray')
                //     ->icon('heroicon-o-arrow-left')
                //     ->action(fn () => $this->retourLieux());
            }
        }

        if (! auth()->check()) {
            // Pas de boutons de création — l'utilisateur peut voir mais pas réserver
            // VoirReservationAction reste pour l'eventClick sur les réservations existantes
            $actions[] = VoirReservationAction::make($this);
            return $actions;
        }

        if ($this->etapeNavigation === 'calendrier') {
            // Filtres uniquement en mode simulateur, hors vue transversale : son filtre
            // "Postes" dépend d'une salle active unique (absente ici, plusieurs salles
            // mélangées) et son filtre "Formations" est volontairement ignoré dans ce
            // mode (cf. getEvents()) pour montrer aussi les autres formations — inutile
            // d'afficher un bouton de filtre qui ne filtre rien.
            if ($this->isModeSimulateur() && ! $this->modeFormationGlobale) {
                $actions[] = FiltresAction::make($this);
            }

            // Wizard selon le mode. En vue transversale (dashboard gestionnaire de
            // formation), il n'y a pas de salle active unique au niveau du bouton
            // d'en-tête : on ne l'ajoute donc pas ici (pas de bouton dans la barre
            // d'outils). L'action reste toutefois mountable via nouvelle_resaAction()
            // (cf. plus bas) — ne PAS utiliser ->hidden() ici : une action masquée est
            // aussi considérée comme désactivée par Filament (isDisabled() s'appuie sur
            // isHidden()), ce qui empêche mountAction() de l'ouvrir, y compris depuis un
            // clic sur une colonne du calendrier ou le bouton dédié du dashboard.
            if (! $this->modeFormationGlobale) {
                $actions[] = $this->isModeClassique()
                    ? NouvelleResaClassiqueAction::make($this)
                    : NouvelleResaAction::make($this);
            }

            // Action cachee voir reservation
            $actions[] = VoirReservationAction::make($this);
        }

        return $actions;
    }

    /**
     * Permet à mountAction('nouvelle_resa') de résoudre l'action même quand elle n'est
     * pas enregistrée dans getHeaderActions() (vue transversale modeFormationGlobale,
     * où il n'y a pas de bouton dans la barre d'outils) : Filament retombe sur une
     * méthode {nom}Action() quand le nom n'est pas trouvé dans les actions mises en
     * cache. Hors vue transversale, l'action est déjà en cache via getHeaderActions()
     * donc cette méthode n'est utilisée que comme filet de sécurité.
     */
    public function nouvelle_resaAction(): Action
    {
        return $this->isModeClassique()
            ? NouvelleResaClassiqueAction::make($this)
            : NouvelleResaAction::make($this);
    }

    /* ── DateClick ────────────────────────────────────────────────────────── */

    protected function onDateClick(DateClickInfo $info): void
    {
        if ($this->modeFormationGlobale && ! $this->resoudreSalleDepuisResource($info->resource)) return;
        if (! $this->canUserReserve()) {
            $this->dispatch('openLoginModal');
            return;
        }
        if ($info->date->isWeekend()) return;

        $debut = $info->date->copy()->setTime(max(8, $info->date->hour), 0);
        $fin   = $debut->copy()->addHour();

        $this->etapeDebut        = $debut->toDateTimeString();
        $this->etapeFin          = $fin->toDateTimeString();
        $this->etapeFormations    = [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /**
     * Vue transversale (modeFormationGlobale) : il n'y a pas de salle active unique,
     * chaque colonne du calendrier est un poste rattaché à une salle différente. On
     * déduit donc la salle à utiliser pour la création du poste/colonne cliqué(e) ;
     * sans colonne identifiable (ex. clic hors zone resource), impossible de créer.
     */
    private function resoudreSalleDepuisResource(?CalendarResource $resource): bool
    {
        if (! $resource) return false;

        $poste = SallePoste::find($resource->getId());
        if (! $poste || ! $poste->salle_id) return false;

        $this->salleActive     = $poste->salle_id;
        $this->typeSalleActive = $poste->salle?->type_salle_id;
        $this->_modeActifCache = null;

        return true;
    }

    /**
     * Déclenché par SuiviFormationsWidget::ouvrirNouvelleReservation() : bouton explicite
     * côté gestionnaire pour créer une réservation sans dépendre d'un clic sur une case
     * du calendrier transversal (qui n'est pas toujours évident à trouver/cliquer).
     */
    #[On('resa-ouvrir-nouvelle-reservation')]
    public function ouvrirNouvelleReservationDepuisBouton(int $salleId, ?int $formationId = null): void
    {
        if (! $this->modeFormationGlobale) return;

        $salle = Salle::find($salleId);
        if (! $salle) return;

        $this->salleActive     = $salle->id;
        $this->typeSalleActive = $salle->type_salle_id;
        $this->_modeActifCache = null;

        $debut = now()->addDay()->setTime(9, 0);
        if ($debut->isWeekend()) $debut = $debut->next(\Carbon\Carbon::MONDAY)->setTime(9, 0);

        $this->etapeDebut        = $debut->toDateTimeString();
        $this->etapeFin          = $debut->copy()->addHour()->toDateTimeString();
        $this->etapeFormations    = $formationId ? [$formationId] : [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /* ── DateSelect ───────────────────────────────────────────────────────── */

    protected function onDateSelect(DateSelectInfo $info): void
    {
        if ($this->modeFormationGlobale && ! $this->resoudreSalleDepuisResource($info->resource)) return;
        if (! $this->canUserReserve()) {
            $this->dispatch('openLoginModal');
            return;
        }
        if ($info->start->isWeekend() || $info->end->isWeekend()) return;

        $this->etapeDebut        = $info->start->toDateTimeString();
        $this->etapeFin          = $info->end->toDateTimeString();
        $this->etapeFormations    = [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /* ── EventClick ───────────────────────────────────────────────────────── */

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        $this->recordEnCours = $info->record->id;
        $this->recordTitre   = $info->record->libelle_court;
        $this->mountAction('voirReservation');
    }

    /* ── Drag & Drop ──────────────────────────────────────────────────────── */

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        if (! auth()->check()) return false;

        $record = $info->record;

        if ($record->isValide() && ! $this->adminMode) return false;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;

        $debut = $info->event->getStart();
        $fin   = $info->event->getEnd();

        if ($this->validerCreneauPublic($debut, $fin, $record->id)) return false;

        $record->update(['debut' => $debut, 'fin' => $fin]);
        return true;
    }

    /* ── Resize ───────────────────────────────────────────────────────────── */

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        if (! auth()->check()) return false;

        $record = $info->record;

        if ($record->isValide() && ! $this->adminMode) return false;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;

        $fin = $info->event->getEnd();

        if ($this->validerCreneauPublic($record->debut->toDateTimeString(), $fin, $record->id)) return false;

        $record->update(['fin' => $fin]);
        return true;
    }
}
