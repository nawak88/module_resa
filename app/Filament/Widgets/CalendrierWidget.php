<?php

namespace Modules\Resa\Filament\Widgets;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Guava\Calendar\ValueObjects\EventResizeInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;

class CalendrierWidget extends CalendarWidget
{
    protected bool $eventClickEnabled = true;
    protected ?string $defaultEventClickAction = 'view';
    protected ?string $locale = 'fr';
    protected bool $eventDragEnabled = true;
    protected bool $eventResizeEnabled = true;
    protected bool $dateClickEnabled = true;
    protected bool $dateSelectEnabled = true;

    // Salle sélectionnée (null = toutes)
    public ?int $salleActive = null;  // null = écran de sélection
    public bool $adminMode = false;

    public function mount(): void
    {
        // Ne pas pré-sélectionner → on affiche d'abord la grille
    }

    public function selectSalle(int $id): void
    {
        $this->salleActive = $id;
        $this->refreshRecords();
    }

    public function retourSalles(): void
    {
        $this->salleActive = null;
    }

  
    

    public function render(): \Illuminate\Contracts\View\View
    {
        if ($this->salleActive === null) {
            return view('resa::filament.widgets.salle-selection');
        }

        return parent::render();
    }

    public function getHeading(): string|null
    {
        if ($this->salleActive) {
            $salle = Salle::find($this->salleActive);
            return 'Calendrier — ' . ($salle?->libelle_court ?? '');
        }
        return 'Réservations';
    }

    protected function getEvents(FetchInfo $info): Collection|array|Builder
{
    $query = Reservation::query();

    if (!$this->adminMode) {
        $query->where('validation', 1);
    }

    if ($this->salleActive) {
        $query->where('salle_id', $this->salleActive);
    }

    return $query->get();
}


    public function getResources(): Collection|array
    {
        return [];
    }

    public function getOptions(): array
    {
        return [
            'nowIndicator' => true,
            'slotDuration' => '00:15:00',
            'height'       => 'auto',
            'selectable'   => true,
            'editable'     => auth()->check(),
        ];
    }

    // Schéma selon connexion
    public function defaultSchema(Schema $schema): Schema
    {
        if (auth()->check()) {
            return $schema->components($this->schemaInfo());
        }

        return $schema->components([
            Wizard::make([
                Step::make('Demande')
                    ->label('Votre demande')
                    ->schema($this->schemaInfo()),
                Step::make('Identité')
                    ->label('Vos informations')
                    ->schema($this->schemaIdentite()),
            ]),
        ]);
    }


    private function schemaInfo(): array
    {
        return [
            Hidden::make('salle_id'),
            TextInput::make('libelle_court')
                ->required()
                ->label('Intitulé de la demande')
                ->columnSpanFull(),
            Grid::make(2)->schema([
                DateTimePicker::make('debut')
                    ->label('Date début')
                    ->required()
                    ->seconds(false),

                DateTimePicker::make('fin')
                    ->label('Date fin')
                    ->required()
                    ->seconds(false)
                    ->after('debut'),
            ]),
            Textarea::make('libelle_long')
                ->label('Informations complémentaires')
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    private function schemaIdentite(): array
    {
        return [
            TextInput::make('display_name')
                ->required()
                ->label('Nom Prénom'),

            TextInput::make('email')
                ->required()
                ->email()
                ->label('Votre email'),

            TextInput::make('nid')
                ->required()
                ->label('Votre NID'),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('retour')
                ->label('← Changer de salle')
                ->color('gray')
                ->action(fn () => $this->retourSalles()),
    
            $this->createAction(Reservation::class, 'createCalendrier')
                ->label('Demande de réservation')
                ->modalHeading('Demande de réservation')
                ->mountUsing(function ($arguments, $form) {
                    $fill = [
                        'debut'    => $arguments['debut'] ?? null,
                        'fin'      => $arguments['fin'] ?? null,
                        'salle_id' => $this->salleActive,
                    ];
                    if (auth()->check()) {
                        $fill['display_name'] = auth()->user()->name;
                        $fill['email']        = auth()->user()->email;
                        $fill['nid']          = auth()->user()->nid ?? null;
                    }
                    $form->fill($fill);
                })
                ->after(function () {
                    $this->refreshRecords();
                    \Filament\Notifications\Notification::make()
                        ->title('Demande envoyée')
                        ->body('Votre demande est en attente de validation.')
                        ->success()
                        ->send();
                }),
        ];
    }
    

    protected function onDateClick(DateClickInfo $info): void
    {
        $this->mountAction('createCalendrier', [
            'debut' => $info->date->toDateTimeString(),
            'fin'   => $info->date->toDateTimeString(),
        ]);
    }

    protected function onDateSelect(DateSelectInfo $info): void
    {
        $this->mountAction('createCalendrier', [
            'debut' => $info->start->toDateTimeString(),
            'fin'   => $info->end->toDateTimeString(),
        ]);
    }

    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        $info->record->update([
            'debut' => $info->event->getStart(),
            'fin'   => $info->event->getEnd(),
        ]);
        return true;
    }

    public function onEventResize(EventResizeInfo $info, Model $event): bool
    {
        $info->record->update(['fin' => $info->event->getEnd()]);
        return true;
    }
}
