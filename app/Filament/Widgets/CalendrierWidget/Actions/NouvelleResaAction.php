<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;
use Modules\Resa\Services\PosteDisponibiliteService;
use App\Models\User;

class NouvelleResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('nouvelle_resa')
            ->label('Nouvelle reservation')
            ->color('primary')
            ->icon('heroicon-o-plus')
            ->mountUsing(function ($form) use ($widget) {
                $form->fill([
                    'debut'         => $widget->etapeDebut ?? now()->setTime(8, 0)->toDateTimeString(),
                    'fin'           => $widget->etapeFin   ?? now()->setTime(9, 0)->toDateTimeString(),
                    'capacites'     => [],
                    'poste_id'      => null,
                    'user_id'       => auth()->id(),
                    'libelle_court' => null,
                    'libelle_long'  => null,
                ]);
            })
            ->schema([
                Wizard::make([
                    self::etape1($widget),
                    self::etape2($widget),
                    self::etape3($widget),
                ])->columnSpanFull(),
            ])
            ->modalHeading('Nouvelle reservation')
            ->modalSubmitActionLabel('Confirmer la reservation')
            ->action(function (array $data) use ($widget): void {
                $posteId = isset($data['poste_id']) ? (int) $data['poste_id'] : null;
                $debut   = $data['debut'];
                $fin     = $data['fin'];
                $userId  = $widget->adminMode ? ($data['user_id'] ?? auth()->id()) : auth()->id();

                $erreur = $widget->validerCreneauPublic($debut, $fin);
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
                        ->body("Ce creneau vient d'etre reserve. Veuillez recommencer.")
                        ->danger()->send();
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
                    'validation'          => $widget->adminMode ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                    'validated_by'        => $widget->adminMode ? auth()->id() : null,
                    'validated_at'        => $widget->adminMode ? now()         : null,
                ]);

                $widget->etapeDebut        = null;
                $widget->etapeFin          = null;
                $widget->etapeCapacites    = [];
                $widget->etapePoste        = null;
                $widget->postesDisponibles = [];

                $widget->refreshRecords();

                Notification::make()
                    ->title('Demande envoyee')
                    ->body($widget->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                    ->success()->send();
            });
    }

    private static function etape1(object $widget): Step
    {
        return Step::make('Creneau et capacites')
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
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),

                    DateTimePicker::make('fin')
                        ->label('Date fin')
                        ->required()
                        ->seconds(false)
                        ->after('debut')
                        ->minDate(now()->startOfDay())
                        ->hoursStep(1)
                        ->minutesStep(15)
                        ->disabledDates(fn () => $widget->getWeekendDatesPublic()),
                ]),

                CheckboxList::make('capacites')
                    ->label('Capacites souhaitees')
                    ->options(TypeCapacite::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->columns(2)
                    ->helperText('Selectionnez les capacites. Le systeme proposera les postes compatibles.')
                    ->columnSpanFull(),
            ])
            ->afterValidation(function ($get) use ($widget) {
                $erreur = $widget->validerCreneauPublic($get('debut'), $get('fin'));
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    return;
                }

                $service = app(PosteDisponibiliteService::class);
                $postes  = $service->postesDisponibles(
                    salleId:         $widget->salleActive,
                    debut:           new \DateTime($get('debut')),
                    fin:             new \DateTime($get('fin')),
                    typeCapaciteIds: $get('capacites') ?? [],
                );

                $widget->etapeDebut     = $get('debut');
                $widget->etapeFin       = $get('fin');
                $widget->etapeCapacites = $get('capacites') ?? [];
                $total                  = count($widget->etapeCapacites);

                $widget->postesDisponibles = $postes->mapWithKeys(function ($item) use ($total) {
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
            });
    }

    private static function etape2(object $widget): Step
    {
        return Step::make('Choix du poste')
            ->icon('heroicon-o-computer-desktop')
            ->schema(function () use ($widget): array {
                if (empty($widget->postesDisponibles)) {
                    return [
                        TextEntry::make('aucun_poste')
                            ->label('Aucun poste disponible')
                            ->getStateUsing(fn () => "Retournez a l'etape precedente.")
                            ->columnSpanFull(),
                    ];
                }

                return [
                    TextEntry::make('recap_creneau')
                        ->label('Creneau')
                        ->getStateUsing(
                            \Carbon\Carbon::parse($widget->etapeDebut)->format('d/m/Y H:i')
                            . ' - '
                            . \Carbon\Carbon::parse($widget->etapeFin)->format('H:i')
                        ),

                    Radio::make('poste_id')
                        ->label('Postes disponibles')
                        ->options($widget->postesDisponibles)
                        ->required()
                        ->columnSpanFull()
                        ->helperText('Tries par score de compatibilite decroissant.'),
                ];
            });
    }

    private static function etape3(object $widget): Step
    {
        return Step::make('Informations')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                ...($widget->adminMode ? [
                    Select::make('user_id')
                        ->label('Reserver pour')
                        ->required()
                        ->options(fn () => User::orderBy('nom')->pluck('nom', 'id'))
                        ->searchable()
                        ->default(auth()->id())
                        ->columnSpanFull(),
                ] : []),

                TextEntry::make('recap_creneau_final')
                    ->label('Creneau')
                    ->getStateUsing(function () use ($widget) {
                        if (! $widget->etapeDebut) return '—';
                        return \Carbon\Carbon::parse($widget->etapeDebut)->format('d/m/Y H:i')
                            . ' - '
                            . \Carbon\Carbon::parse($widget->etapeFin)->format('H:i');
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
            ]);
    }
}
