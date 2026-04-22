<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
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
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;
use Modules\Resa\Services\PosteDisponibiliteService;
use Modules\RH\Models\User;

class CalendrierWidget extends CalendarWidget
{
    protected bool $eventClickEnabled  = true;
    protected bool $eventDragEnabled   = true;
    protected bool $eventResizeEnabled = true;
    protected bool $dateClickEnabled   = true;
    protected bool $dateSelectEnabled  = true;
    protected ?string $locale          = 'fr';
    // Important decalage de 2 h
    protected bool $useFilamentTimezone = true;

    public ?int    $salleActive        = null;
    public bool    $adminMode          = false;
    public ?int    $recordEnCours      = null;
    public ?string $recordTitre        = null; // garde le titre après suppression
    public ?string $etapeDebut         = null;
    public ?string $etapeFin           = null;
    public array   $etapeCapacites     = [];
    public ?int    $etapePoste         = null;
    public array   $postesDisponibles  = [];

       /* ── Initialisation ───────────────────────────────────────────────────── */

       public function mount(): void
       {
           $this->adminMode = auth()->user()?->can('resa::reservation.view') ?? false;
   
           $salles = Salle::orderBy('ordre')->get();
           if ($salles->count() === 1) {
               $this->salleActive = $salles->first()->id;
           }
       }
   
       /* ── Sélection de salle ───────────────────────────────────────────────── */
   
       public function selectSalle(int $id): void
       {
           $this->salleActive = $id;
           $this->refreshRecords();
       }
   
       public function retourSalles(): void
       {
           $this->salleActive = null;
       }
   
       /* ── Rendu conditionnel ───────────────────────────────────────────────── */
   
       public function render(): \Illuminate\Contracts\View\View
       {
           if (! $this->adminMode && $this->salleActive === null) {
               return view('resa::filament.widgets.salle-selection', [
                   'salles' => Salle::orderBy('ordre')->get(),
               ]);
           }
   
           return parent::render();
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
   
       /* ── Helper weekends ──────────────────────────────────────────────────── */
   
       private function getWeekendDates(): array
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
   
       /* ── Validation métier créneau ────────────────────────────────────────── */
   
       private function validerCreneau(string $debut, string $fin): ?string
       {
           $d = \Carbon\Carbon::parse($debut);
           $f = \Carbon\Carbon::parse($fin);
   
           if ($d->isWeekend()) {
               return 'Reservation impossible le week-end.';
           }
   
           if ($d->copy()->startOfDay()->lt(now()->startOfDay())) {
               return 'Impossible de reserver dans le passe.';
           }
   
           if ($f->lte($d)) {
               return 'La date de fin doit etre superieure a la date de debut.';
           }
   
           $debutMin = $d->hour * 60 + $d->minute;
           $finMin   = $f->hour * 60 + $f->minute;
   
           if ($debutMin < 8 * 60 || $debutMin >= 17 * 60) {
               return 'L\'heure de debut doit etre entre 08h00 et 17h00.';
           }
   
           if ($finMin <= 8 * 60 || $finMin > 17 * 60) {
               return 'L\'heure de fin doit etre entre 08h00 et 17h00.';
           }
   
           return null;
       }
   
       /* ── Ressources Guava ─────────────────────────────────────────────────── */
   
       public function getResources(): Collection|array
       {
           if (! $this->salleActive) return [];
   
           return SallePoste::query()
               ->where('salle_id', $this->salleActive)
               ->orderBy('ordre')
               ->get();
       }
   
       /* ── Evenements ───────────────────────────────────────────────────────── */
   
       protected function getEvents(FetchInfo $info): Collection|array|Builder
       {
           $query = Reservation::query()
               ->with(['sallePoste', 'user'])
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
   
           return $query;
       }
   
       /* ── Options FullCalendar ─────────────────────────────────────────────── */
   
       public function getOptions(): array
       {
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
               'initialView'      => 'timeGridWeek',
               'headerToolbar'    => [
                   'start'  => 'prev,next today',
                   'center' => 'title',
                   'end'    => 'dayGridMonth,timeGridWeek,timeGridDay,listDay',
               ],
               'buttonText' => [
                   'today'        => "Aujourd'hui",
                   'dayGridMonth' => 'Mois',
                   'timeGridWeek' => 'Semaine',
                   'timeGridDay'  => 'Jour',
                   'listDay'      => 'Agenda',
               ],
               'locale'      => 'fr',
               'slotMinTime' => '08:00:00',
               'slotMaxTime' => '17:00:00',
               'allDaySlot'  => false,
               'scrollTime'  => '08:00:00',
           ];
       }
   
       /* ── Header actions ───────────────────────────────────────────────────── */
   
       public function getHeaderActions(): array
       {
           $actions = [];
   
           if (! $this->adminMode && $this->salleActive) {
               $actions[] = Action::make('retour_salle')
                   ->label('Changer de salle')
                   ->color('gray')
                   ->action(fn () => $this->retourSalles());
           }
   
           if (! auth()->check()) {
               $actions[] = Action::make('login')
                   ->label('Se connecter pour reserver')
                   ->color('primary')
                   ->icon('heroicon-o-arrow-right-end-on-rectangle')
                   ->url(route('login'));
               return $actions;
           }
   
           /* ── Wizard nouvelle reservation ──────────────────────────────────── */
           $actions[] = Action::make('nouvelle_resa')
               ->label('Nouvelle reservation')
               ->color('primary')
               ->icon('heroicon-o-plus')
               ->mountUsing(function ($form) {
                   $form->fill([
                       'debut'         => $this->etapeDebut ?? now()->setTime(8, 0)->toDateTimeString(),
                       'fin'           => $this->etapeFin   ?? now()->setTime(9, 0)->toDateTimeString(),
                       'capacites'     => [],
                       'poste_id'      => null,
                       'user_id'       => auth()->id(),
                       'libelle_court' => null,
                       'libelle_long'  => null,
                   ]);
               })
               ->schema([
                   Wizard::make([
   
                       /* ── Étape 1 : créneau + capacités ─────────────────── */
                       Step::make('Creneau et capacites')
                           ->icon('heroicon-o-calendar')
                           ->schema([
                               Grid::make(2)->schema([
                                   DateTimePicker::make('debut')
                                       ->label('Date debut')
                                       ->required()
                                       ->seconds(false)
                                       ->minDate(now()->startOfDay())
                                       ->hoursStep(1)
                                       ->minutesStep(15)
                                       ->disabledDates(fn () => $this->getWeekendDates()),
   
                                   DateTimePicker::make('fin')
                                       ->label('Date fin')
                                       ->required()
                                       ->seconds(false)
                                       ->after('debut')
                                       ->minDate(now()->startOfDay())
                                       ->hoursStep(1)
                                       ->minutesStep(15)
                                       ->disabledDates(fn () => $this->getWeekendDates()),
                               ]),
   
                               CheckboxList::make('capacites')
                                   ->label('Capacites souhaitees')
                                   ->options(TypeCapacite::orderBy('ordre')->pluck('libelle_court', 'id'))
                                   ->columns(2)
                                   ->helperText('Selectionnez les capacites. Le systeme proposera les postes compatibles.')
                                   ->columnSpanFull(),
                           ])
                           ->afterValidation(function ($get) {
                               $erreur = $this->validerCreneau($get('debut'), $get('fin'));
                               if ($erreur) {
                                   Notification::make()->title($erreur)->danger()->send();
                                   return;
                               }
   
                               $service = app(PosteDisponibiliteService::class);
                               $postes  = $service->postesDisponibles(
                                   salleId:         $this->salleActive,
                                   debut:           new \DateTime($get('debut')),
                                   fin:             new \DateTime($get('fin')),
                                   typeCapaciteIds: $get('capacites') ?? [],
                               );
   
                               $this->etapeDebut     = $get('debut');
                               $this->etapeFin       = $get('fin');
                               $this->etapeCapacites = $get('capacites') ?? [];
                               $total                = count($this->etapeCapacites);
   
                               $this->postesDisponibles = $postes->mapWithKeys(function ($item) use ($total) {
                                   $poste      = $item['poste'];
                                   $score      = $item['score'];
                                   $manquantes = $item['manquantes'];
   
                                   if ($total === 0) {
                                       $label = $poste->libelle_court . ' - libre';
                                   } elseif ($manquantes->isEmpty()) {
                                       $label = $poste->libelle_court . ' - toutes les capacites (' . $score . '/' . $total . ')';
                                   } else {
                                       $caps  = TypeCapacite::whereIn('id', $manquantes)->pluck('libelle_court')->join(', ');
                                       $label = $poste->libelle_court . ' - ' . $score . '/' . $total . ' (manque: ' . $caps . ')';
                                   }
   
                                   return [$poste->id => $label];
                               })->toArray();
                           }),
   
                       /* ── Étape 2 : choix du poste ───────────────────────── */
                       Step::make('Choix du poste')
                           ->icon('heroicon-o-computer-desktop')
                           ->schema(function (): array {
                               if (empty($this->postesDisponibles)) {
                                   return [
                                       Placeholder::make('aucun_poste')
                                           ->label('')
                                           ->content('Aucun poste disponible. Retournez a l\'etape precedente.')
                                           ->columnSpanFull(),
                                   ];
                               }
   
                               return [
                                   Placeholder::make('recap_creneau')
                                       ->label('Creneau')
                                       ->content(
                                           \Carbon\Carbon::parse($this->etapeDebut)->format('d/m/Y H:i')
                                           . ' - '
                                           . \Carbon\Carbon::parse($this->etapeFin)->format('H:i')
                                       ),
   
                                   Radio::make('poste_id')
                                       ->label('Postes disponibles')
                                       ->options($this->postesDisponibles)
                                       ->required()
                                       ->columnSpanFull()
                                       ->helperText('Tries par score de compatibilite decroissant.'),
                               ];
                           }),
   
                       /* ── Étape 3 : informations ─────────────────────────── */
                       Step::make('Informations')
                           ->icon('heroicon-o-pencil-square')
                           ->schema([
                               // Admin uniquement : choisir l'utilisateur
                               ...($this->adminMode ? [
                                   Select::make('user_id')
                                       ->label('Reserver pour')
                                       ->required()
                                       ->options(
                                           fn () => User::orderBy('nom')
                                               ->pluck('nom', 'id')
                                       )
                                       ->searchable()
                                       ->default(auth()->id())
                                       ->columnSpanFull(),
                               ] : []),
   
                               Placeholder::make('recap_creneau_final')
                                   ->label('Creneau')
                                   ->content(function () {
                                       if (! $this->etapeDebut) return '—';
                                       return \Carbon\Carbon::parse($this->etapeDebut)->format('d/m/Y H:i')
                                           . ' - '
                                           . \Carbon\Carbon::parse($this->etapeFin)->format('H:i');
                                   }),
   
                               TextInput::make('libelle_court')
                                   ->label('Intitule de la reservation')
                                   ->required()
                                   ->maxLength(500)
                                   ->columnSpanFull(),
   
                               Textarea::make('libelle_long')
                                   ->label('Informations complementaires')
                                   ->rows(3)
                                   ->columnSpanFull(),
                           ]),
   
                   ])->columnSpanFull(),
               ])
               ->modalHeading('Nouvelle reservation')
               ->modalSubmitActionLabel('Confirmer la reservation')
               ->action(function (array $data): void {
                   $posteId = isset($data['poste_id']) ? (int) $data['poste_id'] : null;
                   $debut   = $data['debut'];
                   $fin     = $data['fin'];
                   $userId  = $this->adminMode ? ($data['user_id'] ?? auth()->id()) : auth()->id();
   
                   $erreur = $this->validerCreneau($debut, $fin);
                   if ($erreur) {
                       Notification::make()->title($erreur)->danger()->send();
                       return;
                   }
   
                   $poste = SallePoste::find($posteId);
                   if (! $poste) {
                       Notification::make()->title('Poste introuvable. Veuillez recommencer.')->danger()->send();
                       return;
                   }
   
                   if (! $poste->isDisponible(new \DateTime($debut), new \DateTime($fin))) {
                       Notification::make()
                           ->title('Creneau indisponible')
                           ->body('Ce creneau vient d\'etre reserve. Veuillez recommencer.')
                           ->danger()
                           ->send();
                       return;
                   }
   
                   Reservation::create([
                       'user_id'             => $userId,
                       'salle_poste_id'      => $poste->id,
                       'libelle_court'       => $data['libelle_court'],
                       'libelle_long'        => $data['libelle_long'] ?? null,
                       'debut'               => $debut,
                       'fin'                 => $fin,
                       'capacites_utilisees' => $data['capacites'] ?? [],
                       'validation'          => $this->adminMode ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                       'validated_by'        => $this->adminMode ? auth()->id() : null,
                       'validated_at'        => $this->adminMode ? now()         : null,
                   ]);
   
                   $this->etapeDebut        = null;
                   $this->etapeFin          = null;
                   $this->etapeCapacites    = [];
                   $this->etapePoste        = null;
                   $this->postesDisponibles = [];
   
                   $this->refreshRecords();
   
                   Notification::make()
                       ->title('Demande envoyee')
                       ->body($this->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                       ->success()
                       ->send();
               });
   
           /* ── VOIR reservation ─────────────────────────────────────────────── */
           $actions[] = Action::make('voirReservation')
               ->label('')
               ->extraAttributes(['style' => 'display:none'])
               ->schema(fn (): array => [
                   Grid::make(2)->schema([
                       Placeholder::make('debut_ph')
                           ->label('Debut')
                           ->content(fn () => $this->recordEnCours
                               ? (Reservation::find($this->recordEnCours)?->debut?->format('d/m/Y H:i') ?? '—')
                               : '—'),
   
                       Placeholder::make('fin_ph')
                           ->label('Fin')
                           ->content(fn () => $this->recordEnCours
                               ? (Reservation::find($this->recordEnCours)?->fin?->format('d/m/Y H:i') ?? '—')
                               : '—'),
                   ]),
   
                   Placeholder::make('poste_ph')
                       ->label('Poste')
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->sallePoste?->libelle_court ?? '—')
                           : '—'),
   
                   Placeholder::make('salle_ph')
                       ->label('Salle')
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->sallePoste?->salle?->libelle_court ?? '—')
                           : '—'),
   
                   Placeholder::make('user_ph')
                       ->label('Demandeur')
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->user?->nom ?? '—')
                           : '—'),
   
                   Placeholder::make('capacites_ph')
                       ->label('Capacites utilisees')
                       ->content(function () {
                           if (! $this->recordEnCours) return '—';
                           $record = Reservation::find($this->recordEnCours);
                           if (! $record || empty($record->capacites_utilisees)) return '—';
                           return TypeCapacite::whereIn('id', $record->capacites_utilisees)
                               ->pluck('libelle_court')->join(', ');
                       }),
   
                   Placeholder::make('libelle_long_ph')
                       ->label('Informations')
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->libelle_long ?? '—')
                           : '—'),
   
                   Placeholder::make('statut_ph')
                       ->label('Statut')
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->isValide() ? 'Validee' : 'En attente de validation')
                           : '—'),
   
                   Placeholder::make('valide_par_ph')
                       ->label('Validee par')
                       ->visible(fn () => $this->recordEnCours && Reservation::find($this->recordEnCours)?->isValide())
                       ->content(fn () => $this->recordEnCours
                           ? (Reservation::find($this->recordEnCours)?->validatedBy?->nom
                               . ' - '
                               . Reservation::find($this->recordEnCours)?->validated_at?->format('d/m/Y H:i'))
                           : '—'),
               ])
               ->modalHeading(fn () => $this->recordTitre ?? 'Reservation')
               ->modalSubmitAction(false)
               ->extraModalFooterActions(fn (): array => $this->getResaFooterActions())
               ->action(fn () => null);
   
   
   
           return $actions;
       }
   
       /* ── Footer actions modal voir ────────────────────────────────────────── */
   
       private function getResaFooterActions(): array
       {
           $record = $this->recordEnCours ? Reservation::find($this->recordEnCours) : null;
           if (! $record) return [];
   
           $actions = [];
   
           $canModify = $record->isEnAttente()
               && ($this->adminMode || auth()->id() === $record->user_id);
   
           if ($canModify) {
               $actions[] = Action::make('go_supprimer')
                   ->label('Supprimer')
                   ->color('danger')
                   ->icon('heroicon-o-trash')
                   ->requiresConfirmation()
                   ->action(function () use ($record): void {
                       $record->delete();
                       $this->recordEnCours = null;
                       $this->recordTitre   = null;
                       $this->refreshRecords();
                       $this->unmountAction();
                       Notification::make()->title('Reservation supprimee')->success()->send();
                   });
           }
   
           if ($this->adminMode && auth()->user()?->can('resa::validation_salle')) {
               $actions[] = Action::make('toggle_validation')
                   ->label($record->isValide() ? 'Invalider' : 'Valider')
                   ->color($record->isValide() ? 'warning' : 'success')
                   ->icon($record->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                   ->action(function () use ($record): void {
                       $record->isValide() ? $record->invalider() : $record->valider(auth()->user());
                       $this->refreshRecords();
                       Notification::make()
                           ->title($record->fresh()->isValide() ? 'Reservation validee' : 'Reservation invalidee')
                           ->success()
                           ->send();
                   });
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
           $this->recordTitre   = $info->record->libelle_court; // sauvegarde avant suppression
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
   
           if ($this->validerCreneau($debut, $fin)) return false;
   
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
   
           if ($this->validerCreneau($record->debut->toDateTimeString(), $fin)) return false;
   
           $record->update(['fin' => $fin]);
   
           return true;
       }
   }
   