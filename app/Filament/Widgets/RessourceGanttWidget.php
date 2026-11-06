<?php

namespace Modules\Resa\Filament\Widgets;

use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\NouvelleResaAction;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\SeConnecterAction;
use Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions\VoirResaAction;
use Modules\RH\Models\LieuUnite;
use Modules\RH\Models\Unite;
use Modules\Resa\Models\Ressource;
use Modules\Resa\Models\RessourceCategorie;
use Modules\Resa\Models\RessourceReservation;

class RessourceGanttWidget extends CalendarWidget
{
    protected string $view = 'resa::filament.widgets.ressource-gantt-widget';

    protected static bool $isDiscovered = false;

    protected int | string | array $columnSpan = 'full';

    protected CalendarViewType $calendarView = CalendarViewType::ResourceTimelineWeek;

    protected bool $eventClickEnabled   = true;
    protected bool $dateSelectEnabled   = true;
    protected bool $eventDragEnabled    = true;
    protected bool $eventResizeEnabled  = true;
    protected ?string $locale           = 'fr';
    protected bool $useFilamentTimezone = true;

    // Navigation
    public string  $etape           = 'gantt'; // 'lieu' | 'unite' | 'gantt'
    public ?int    $filtreCategorie = null;
    public ?int    $filtreLieu      = null;
    public ?int    $filtreUnite     = null;

    // État admin
    public bool $adminMode = false;

    // État pour voir une réservation cliquée
    public ?int $viewingId = null;

    // État pour créer
    public ?string $newDebut       = null;
    public ?string $newFin         = null;
    public ?int    $newRessourceId = null;

    public function mount(): void
    {
        $this->adminMode = auth()->user()?->can('resa::admin') ?? false;

        if ($this->filtreCategorie && \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id')) {
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
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)->whereNotNull('unite_id')->pluck('unite_id');

        return LieuUnite::whereIn('id',
            Unite::whereIn('id', $uniteIds)->pluck('lieu_unite_id')
        )->orderBy('ordre')->get();
    }

    public function getUnites()
    {
        $uniteIds = Ressource::where('ressource_categorie_id', $this->filtreCategorie)
            ->where('actif', true)->whereNotNull('unite_id')->pluck('unite_id');

        return Unite::whereIn('id', $uniteIds)
            ->when($this->filtreLieu, fn ($q) => $q->where('lieu_unite_id', $this->filtreLieu))
            ->orderBy('ordre')->get();
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

    /* ── Ressources Guava (colonnes du timeline) ──────────────────────────── */

    public function getResources(): Collection|array
    {
        if ($this->etape !== 'gantt') return [];

        try {
            $user        = auth()->user();
            $isAdmin     = $user?->can('resa::admin') ?? false;
            $userUniteId = \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id')
                ? $user?->unite_id
                : null;

            $hasUniteCol = \Illuminate\Support\Facades\Schema::hasColumn('resa_ressources', 'unite_id');

            return Ressource::query()
                ->where('actif', true)
                ->when($this->filtreCategorie, fn ($q) => $q->where('ressource_categorie_id', $this->filtreCategorie))
                ->when($hasUniteCol && $this->filtreUnite, fn ($q) => $q->where('unite_id', $this->filtreUnite))
                ->when($hasUniteCol && ! $this->filtreUnite && ! $isAdmin && $userUniteId, fn ($q) =>
                    $q->where(fn ($q2) => $q2->whereNull('unite_id')->orWhere('unite_id', $userUniteId))
                )
                ->orderBy('ordre')
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function getResourcesJs(): array
    {
        return $this->getResources()
            ->map(function (Ressource $r) {
                $titre = $r->nom;
                $titre .= match ($r->statut) {
                    Ressource::STATUT_ENTRETIEN     => ' ⚠️ En entretien',
                    Ressource::STATUT_INDISPONIBLE => ' ⛔ Indisponible',
                    default                         => '',
                };

                return \Guava\Calendar\ValueObjects\CalendarResource::make($r->id)
                    ->title($titre)
                    ->toCalendarObject();
            })
            ->toArray();
    }

    /* ── Événements ───────────────────────────────────────────────────────── */

    public function getEventsJs(array $info): array
    {
        $events = $this->getEvents(new FetchInfo($info));

        if ($events instanceof Builder) {
            $events = $events->get();
        }

        if (is_array($events)) {
            $events = collect($events);
        }

        $isAdmin   = $this->adminMode;
        $tzOffset  = data_get($info, 'tzOffset');
        $useFilTz  = $this->shouldUseFilamentTimezone();

        return $events
            ->map(function (RessourceReservation $r) use ($isAdmin, $tzOffset, $useFilTz): array {
                $nom   = $isAdmin ? ($r->user?->nom ?? '') : '';
                $titre = implode("\n", array_filter([
                    $r->libelle ?: ($r->user?->nom ?? ''),
                    $nom && $r->libelle ? '👤 ' . $nom : '',
                    $r->debut?->format('H:i') . ' — ' . $r->fin?->format('H:i'),
                ]));

                $couleur = $r->validation === 1 ? '#22c55e' : '#f59e0b';

                return \Guava\Calendar\ValueObjects\CalendarEvent::make($r)
                    ->title($titre)
                    ->start($r->debut?->format('Y-m-d H:i:s'))
                    ->end($r->fin?->format('Y-m-d H:i:s'))
                    ->resourceId($r->ressource_id)
                    ->backgroundColor($couleur)
                    ->editable($r->validation !== 1 && auth()->id() === $r->user_id)
                    ->toCalendarObject($tzOffset, $useFilTz);
            })
            ->all();
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {
        if ($this->etape !== 'gantt') return [];

        $ressourceIds = $this->getResources()->pluck('id');

        // Catégories dont l'utilisateur est gestionnaire : il voit aussi les réservations
        // en attente du matériel de ces catégories, qu'il n'a pas lui-même créées.
        $user = auth()->user();
        $categorieIdsGerees = ($user && $user->can('resa::gestionnaire_ressource'))
            ? \Modules\Resa\Models\RessourceCategorie::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->pluck('id')
            : collect();

        return RessourceReservation::query()
            ->with(['user', 'ressource'])
            ->whereIn('ressource_id', $ressourceIds)
            ->where('debut', '<', $info->end)
            ->where('fin',   '>', $info->start)
            ->when(! $this->adminMode, fn ($q) =>
                $q->where(function ($q2) use ($categorieIdsGerees) {
                    $q2->where('validation', RessourceReservation::VALIDE)
                        ->orWhere('user_id', auth()->id());

                    if ($categorieIdsGerees->isNotEmpty()) {
                        $q2->orWhereHas('ressource', fn ($q3) => $q3->whereIn('ressource_categorie_id', $categorieIdsGerees));
                    }
                })
            );
    }

    /* ── Options ──────────────────────────────────────────────────────────── */

    public function getOptions(): array
    {
        return [
            'nowIndicator'        => true,
            'height'              => 'auto',
            'selectable'          => true,
            'editable'            => auth()->check(),
            'hiddenDays'          => [0, 6],
            'datesAboveResources' => true,
            'headerToolbar'       => [
                'start'  => 'prev,next today',
                'center' => 'title',
                'end'    => 'resourceTimelineDay,resourceTimelineWeek,resourceTimeGridWeek,dayGridMonth',
            ],
            'buttonText' => [
                'today'                 => "Aujourd'hui",
                'resourceTimelineDay'   => 'Jour',
                'resourceTimelineWeek'  => 'Semaine',
                'resourceTimeGridWeek'  => 'Grille',
                'dayGridMonth'          => 'Mois',
            ],
            'locale'      => 'fr',
            'slotMinTime' => '08:00:00',
            'slotMaxTime' => '18:00:00',
            'scrollTime'  => '08:00:00',
            'allDaySlot'  => false,
            'views'       => [
                'resourceTimelineDay'  => ['slotDuration' => '01:00:00'],
                'resourceTimelineWeek' => ['slotDuration' => '01:00:00'],
            ],
        ];
    }

    /* ── Heading ──────────────────────────────────────────────────────────── */

    public function getHeading(): ?string
    {
        if ($this->filtreCategorie) {
            $cat = RessourceCategorie::find($this->filtreCategorie);
            return 'Réservations — ' . ($cat?->libelle ?? '');
        }
        return 'Réservations de ressources';
    }

    /* ── DateSelect → ouvrir modal création ──────────────────────────────── */

    protected function onDateSelect(DateSelectInfo $info): void
    {
        if (! auth()->check()) {
            $this->mountAction('seConnecter');
            return;
        }

        $this->newDebut       = $info->start->toDateTimeString();
        $this->newFin         = $info->end->toDateTimeString();
        $this->newRessourceId = null;

        $this->mountAction('nouvelleResa');
    }

    /* ── EventClick → ouvrir modal vue ───────────────────────────────────── */

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        $this->viewingId = $info->record->id;
        $this->mountAction('voirResa');
    }

    /* ── Drag & Resize ────────────────────────────────────────────────────── */

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        $record = $info->record;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;
        $record->update(['debut' => $info->event->getStart(), 'fin' => $info->event->getEnd()]);
        return true;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        $record = $info->record;
        if (! $this->adminMode && $record->user_id !== auth()->id()) return false;
        $record->update(['fin' => $info->event->getEnd()]);
        return true;
    }

    /* ── Header actions ───────────────────────────────────────────────────── */

    public function getHeaderActions(): array
    {
        if ($this->etape !== 'gantt') return [];

        return [
            NouvelleResaAction::make($this),
            VoirResaAction::make($this),
        ];
    }

    public function seConnecterAction(): \Filament\Actions\Action
    {
        return SeConnecterAction::make($this);
    }

    public function redirectSso(): void
    {
        session()->put('login_redirect_after', url()->current());
        $this->redirect(route('keycloak.login.redirect'));
    }
}
