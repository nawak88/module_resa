<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;

class NouvelleResaClassiqueAction
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
                    'libelle_court' => null,
                    'libelle_long'  => null,
                ]);
            })
            ->schema([
                Wizard::make([
                    self::etape1($widget),
                    self::etape2($widget),
                ])->columnSpanFull(),
            ])
            ->modalHeading('Nouvelle reservation')
            ->modalSubmitActionLabel('Confirmer la reservation')
            ->action(function (array $data) use ($widget): void {
                $debut = $data['debut'];
                $fin   = $data['fin'];

                $erreur = $widget->validerCreneauPublic($debut, $fin);
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    return;
                }

                // Recupere le poste fictif "Salle entiere" de la salle active
                $poste = SallePoste::where('salle_id', $widget->salleActive)
                    ->where('libelle_court', 'Salle entiere')
                    ->first();

                if (! $poste) {
                    Notification::make()->title('Configuration incorrecte. Poste fictif introuvable.')->danger()->send();
                    return;
                }

                // Verifie que la salle est libre sur ce creneau
                if (! $poste->isDisponible(new \DateTime($debut), new \DateTime($fin))) {
                    Notification::make()
                        ->title('Salle indisponible')
                        ->body('La salle est deja reservee sur ce creneau.')
                        ->danger()->send();
                    return;
                }

                Reservation::create([
                    'user_id'             => auth()->id(),
                    'salle_poste_id'      => $poste->id,
                    'libelle_court'       => $data['libelle_court'],
                    'libelle_long'        => $data['libelle_long'] ?? null,
                    'debut'               => $debut,
                    'fin'                 => $fin,
                    'formations_utilisees' => [],
                    'validation'          => $widget->adminMode ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                    'validated_by'        => $widget->adminMode ? auth()->id() : null,
                    'validated_at'        => $widget->adminMode ? now()         : null,
                ]);

                $widget->etapeDebut = null;
                $widget->etapeFin   = null;
                $widget->refreshRecords();

                Notification::make()
                    ->title('Demande envoyee')
                    ->body($widget->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                    ->success()->send();
            });
    }

    private static function etape1(object $widget): Step
    {
        return Step::make('Creneau')
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

                // Info capacite de la salle
                TextEntry::make('capacite_info')
                    ->label('Capacite de la salle')
                    ->getStateUsing(function () use ($widget) {
                        $salle = Salle::find($widget->salleActive);
                        return $salle?->capacite ? $salle->capacite . ' personnes maximum' : '—';
                    })
                    ->columnSpanFull(),
            ])
            ->afterValidation(function ($get) use ($widget) {
                $erreur = $widget->validerCreneauPublic($get('debut'), $get('fin'));
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'debut' => $erreur,
                    ]);
                }

                // Verifie disponibilite salle
                $poste = SallePoste::where('salle_id', $widget->salleActive)
                    ->where('libelle_court', 'Salle entiere')
                    ->first();

                if ($poste && ! $poste->isDisponible(new \DateTime($get('debut')), new \DateTime($get('fin')))) {
                    $msg = 'La salle est deja reservee sur ce creneau.';
                    Notification::make()->title($msg)->danger()->send();
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'debut' => $msg,
                    ]);
                }

                $widget->etapeDebut = $get('debut');
                $widget->etapeFin   = $get('fin');
            });
    }

    private static function etape2(object $widget): Step
    {
        return Step::make('Informations')
            ->icon('heroicon-o-pencil-square')
            ->schema([
                TextEntry::make('recap_creneau')
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


