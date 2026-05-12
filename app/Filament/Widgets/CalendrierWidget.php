<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\FiltresAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\NouvelleResaClassiqueAction;
use Modules\Resa\Filament\Widgets\CalendrierWidget\Actions\VoirReservationAction;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeSalle;

class CalendrierWidget extends CalendarWidget
{
    protected string $view = 'resa::filament.widgets.calendrier-widget';

    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;

    protected bool $eventClickEnabled  = true;
    protected bool $eventDragEnabled   = true;
    protected bool $eventResizeEnabled = true;
    protected bool $dateClickEnabled   = true;
    protected bool $dateSelectEnabled  = true;
    protected ?string $locale          = 'fr';
    protected bool $useFilamentTimezone = true;

    // Navigation
    public string  $etapeNavigation  = 'type_salle'; // type_salle | salle | calendrier
    public ?int    $typeSalleActive  = null;
    public ?int    $salleActive      = null;

    // Etat modal
    public bool    $adminMode      = false;
    public ?int    $recordEnCours  = null;
    public ?string $recordTitre    = null;

    // Etapes Wizard simulateur
    public ?string $etapeDebut        = null;
    public ?string $etapeFin          = null;
    public array   $etapeCapacites    = [];
    public ?int    $etapePoste        = null;
    public array   $postesDisponibles = [];

    // Filtres calendrier
    public array $filtrePostes    = [];
    public array $filtreCapacites = [];

    /* ── Initialisation ───────────────────────────────────────────────────── */

    public function mount(): void
    {
        $this->adminMode = auth()->user()?->can('resa::reservation.view') ?? false;

        // Si salleActive est deja defini (injection de tests ou lien direct)
        // on ne resout pas la navigation automatiquement
        if ($this->salleActive !== null) {
            $this->etapeNavigation = 'calendrier';
            if ($this->typeSalleActive === null) {
                $this->typeSalleActive = \Modules\Resa\Models\Salle::find($this->salleActive)?->type_salle_id;
            }
            return;
        }

        $this->resoudreNavigation();
    }

    /* ── Resolution automatique de la navigation ──────────────────────────── */

    private function resoudreNavigation(): void
    {
        $salles     = Salle::with('typeSalle')->orderBy('ordre')->get();
        $typeSalles = TypeSalle::orderBy('ordre')->get();

        // 1 seule salle au total → calendrier direct
        if ($salles->count() === 1) {
            $this->salleActive     = $salles->first()->id;
            $this->typeSalleActive = $salles->first()->type_salle_id;
            $this->etapeNavigation = 'calendrier';
            return;
        }

        // Plusieurs salles, 1 seul type de salle → liste salles directement
        if ($typeSalles->count() === 1) {
            $this->typeSalleActive = $typeSalles->first()->id;
            $this->etapeNavigation = 'salle';
            return;
        }

        // Plusieurs types de salles → choix du type d'abord
        $this->etapeNavigation = 'type_salle';
    }

    /* ── Sélection type salle ─────────────────────────────────────────────── */

    public function selectTypeSalle(int $id): void
    {
        $this->typeSalleActive = $id;

        // Si 1 seule salle dans ce type → aller direct au calendrier
        $salles = Salle::where('type_salle_id', $id)->get();
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
        $this->salleActive     = $id;
        $this->etapeNavigation = 'calendrier';
        $this->filtrePostes    = [];
        $this->filtreCapacites = [];
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

    /* ── Mode de la salle active ──────────────────────────────────────────── */

    public function getModeActif(): string
    {
        if (! $this->typeSalleActive) return TypeSalle::MODE_SIMULATEUR;
        return TypeSalle::find($this->typeSalleActive)?->mode ?? TypeSalle::MODE_SIMULATEUR;
    }

    public function isModeClassique(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_CLASSIQUE;
    }

    public function isModeSimulateur(): bool
    {
        return $this->getModeActif() === TypeSalle::MODE_SIMULATEUR;
    }

    /* ── Rendu conditionnel ───────────────────────────────────────────────── */

    public function render(): \Illuminate\Contracts\View\View
    {
        return match ($this->etapeNavigation) {
            'type_salle' => view('resa::filament.widgets.type-salle-selection', [
                'typeSalles' => TypeSalle::orderBy('ordre')->get(),
            ]),
            'salle' => view('resa::filament.widgets.salle-selection', [
                'salles'    => Salle::where('type_salle_id', $this->typeSalleActive)->orderBy('ordre')->get(),
                'typeSalle' => TypeSalle::find($this->typeSalleActive),
            ]),
            default => parent::render(),
        };
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

    /* ── Helpers publics ──────────────────────────────────────────────────── */

    public function validerCreneauPublic(string $debut, string $fin, ?int $excludeId = null): ?string
    {
        $d = \Carbon\Carbon::parse($debut);
        $f = \Carbon\Carbon::parse($fin);

        if ($d->isWeekend()) return 'Reservation impossible le week-end.';
        if ($d->copy()->startOfDay()->lt(now()->startOfDay())) return 'Impossible de reserver dans le passe.';
        if ($f->lte($d)) return 'La date de fin doit etre superieure a la date de debut.';

        $debutMin = $d->hour * 60 + $d->minute;
        $finMin   = $f->hour * 60 + $f->minute;

        if ($debutMin < 8 * 60 || $debutMin >= 17 * 60) return "L'heure de debut doit etre entre 08h00 et 17h00.";
        if ($finMin <= 8 * 60 || $finMin > 17 * 60)     return "L'heure de fin doit etre entre 08h00 et 17h00.";

        if (auth()->check()) {
            $dejaReserve = Reservation::where('user_id', auth()->id())
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->where('debut', '<', $fin)
                ->where('fin',   '>', $debut)
                ->exists();

            if ($dejaReserve) return 'Vous avez deja une reservation sur ce creneau.';
        }

        return null;
    }

    public function getWeekendDatesPublic(): array
    {
        $dates = [];
        $day   = now()->startOfDay()->copy();
        $end   = now()->addMonths(6);

        while ($day <= $end) {
            if ($day->isWeekend()) {
                $dates[] = $day->format('Y-m-d');
            }
            $day->addDay();
        }

        return $dates;
    }

    /* ── Ressources Guava ─────────────────────────────────────────────────── */

    public function getResources(): Collection|array
    {
        // Mode classique : pas de colonnes
        if ($this->isModeClassique()) return [];

        if (! $this->salleActive) return [];

        return SallePoste::query()
            ->where('salle_id', $this->salleActive)
            ->where('libelle_court', '!=', 'Salle entiere') // exclut le poste fictif
            ->orderBy('ordre')
            ->get();
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

        if ($this->salleActive) {
            $query->whereHas('sallePoste', fn ($q) =>
                $q->where('salle_id', $this->salleActive)
            );
        }

        if (! empty($this->filtrePostes)) {
            $query->whereIn('salle_poste_id', $this->filtrePostes);
        }

        if (! empty($this->filtreCapacites)) {
            $query->where(function ($q) {
                foreach ($this->filtreCapacites as $id) {
                    $q->whereJsonContains('capacites_utilisees', $id);
                }
            });
        }

        return $query;
    }

    /* ── Options FullCalendar ─────────────────────────────────────────────── */

    public function getOptions(): array
    {
        $resasUser = [];
        if (auth()->check()) {
            $resasUser = Reservation::where('user_id', auth()->id())
                ->get(['debut', 'fin'])
                ->map(fn ($r) => [
                    'start' => $r->debut->toIso8601String(),
                    'end'   => $r->fin->toIso8601String(),
                ])
                ->values()
                ->toArray();
        }

        $resasJson = json_encode($resasUser);

        return [
            'nowIndicator'     => true,
            'slotDuration'     => '00:15:00',
            'height'           => 'auto',
            'selectable'       => true,
            'editable'         => auth()->check(),
            'weekends'         => false,
            'businessHours'    => [
                'daysOfWeek' => [1, 2, 3, 4, 5],
                'startTime'  => '08:00',
                'endTime'    => '17:00',
            ],
            'selectConstraint' => 'businessHours',
            'headerToolbar'    => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'timeGridDay,timeGridWeek,dayGridMonth,listWeek,listMonth',
            ],
            'buttonText' => [
                'today'        => "Aujourd'hui",
                'timeGridDay'  => 'Jour',
                'timeGridWeek' => 'Semaine',
                'dayGridMonth' => 'Mois',
                'listWeek'     => 'Liste sem.',
                'listMonth'    => 'Liste mois',
            ],
            'locale'      => 'fr',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '17:00:00',
            'allDaySlot'  => false,
            'scrollTime'  => '08:00:00',
            'selectAllow' => new HtmlString("
                function(selectInfo) {
                    var resasUser = {$resasJson};
                    var start = selectInfo.start.getTime();
                    var end   = selectInfo.end.getTime();
                    for (var i = 0; i < resasUser.length; i++) {
                        var rs = new Date(resasUser[i].start).getTime();
                        var re = new Date(resasUser[i].end).getTime();
                        if (start < re && end > rs) return false;
                    }
                    return true;
                }
            "),
        ];
    }

    /* ── Header actions ───────────────────────────────────────────────────── */

    public function getHeaderActions(): array
    {
        $actions = [];

        // Bouton retour selon l'etape
        if ($this->etapeNavigation === 'calendrier') {
            // Retour salles si plusieurs salles dans le type
            $nbSalles = Salle::where('type_salle_id', $this->typeSalleActive)->count();
            if ($nbSalles > 1) {
                $actions[] = Action::make('retour_salle')
                    ->label('Changer de salle')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-left')
                    ->action(fn () => $this->retourSalles());
            }

            // Retour type salles si plusieurs types
            if (TypeSalle::count() > 1 && $nbSalles === 1) {
                $actions[] = Action::make('retour_type_salle')
                    ->label('Changer de type')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-left')
                    ->action(fn () => $this->retourTypeSalles());
            }
        }

        if (! auth()->check()) {
            $actions[] = Action::make('login')
                ->label('Se connecter pour reserver')
                ->color('primary')
                ->icon('heroicon-o-arrow-right-end-on-rectangle')
                ->url(route('login'));
            return $actions;
        }

        if ($this->etapeNavigation === 'calendrier') {
            // Filtres uniquement en mode simulateur
            if ($this->isModeSimulateur()) {
                $actions[] = FiltresAction::make($this);
            }

            // Wizard selon le mode
            $actions[] = $this->isModeClassique()
                ? NouvelleResaClassiqueAction::make($this)
                : NouvelleResaAction::make($this);

            // Action cachee voir reservation
            $actions[] = VoirReservationAction::make($this);
        }

        return $actions;
    }

    /* ── DateClick ────────────────────────────────────────────────────────── */

    protected function onDateClick(DateClickInfo $info): void
    {
        if (! $this->canUserReserve()) return;
        if ($info->date->isWeekend()) return;

        $debut = $info->date->copy()->setTime(max(8, $info->date->hour), 0);
        $fin   = $debut->copy()->addHour();

        $this->etapeDebut        = $debut->toDateTimeString();
        $this->etapeFin          = $fin->toDateTimeString();
        $this->etapeCapacites    = [];
        $this->etapePoste        = null;
        $this->postesDisponibles = [];

        $this->mountAction('nouvelle_resa');
    }

    /* ── DateSelect ───────────────────────────────────────────────────────── */

    protected function onDateSelect(DateSelectInfo $info): void
    {
        if (! $this->canUserReserve()) return;
        if ($info->start->isWeekend() || $info->end->isWeekend()) return;

        $this->etapeDebut        = $info->start->toDateTimeString();
        $this->etapeFin          = $info->end->toDateTimeString();
        $this->etapeCapacites    = [];
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
        $record = $info->record;

        if ($record->isValide() && ! $this->adminMode) return false;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;

        $fin = $info->event->getEnd();

        if ($this->validerCreneauPublic($record->debut->toDateTimeString(), $fin, $record->id)) return false;

        $record->update(['fin' => $fin]);
        return true;
    }
}
