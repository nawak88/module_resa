<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use App\Models\AnnuaireUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Validation\ValidationException;
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
                    'user_id'       => auth()->id(),
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
                    'user_id'             => $data['user_id'] ?? auth()->id(),
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
        $isIntradef = config('skeletor.reseau_de_deploiement') === 'intradef';

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

                Select::make('user_id')
                    ->label('Réservation pour')
                    ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                    ->searchable()
                    ->required()
                    ->default(auth()->id())
                    ->columnSpanFull(),

                ...($isIntradef ? [
                    Actions::make([self::annuaireAction()])->columnSpanFull(),
                ] : []),

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

    private static function annuaireAction(): Action
    {
        return Action::make('annuaire_user')
            ->label('Rechercher dans l\'annuaire')
            ->icon('heroicon-o-magnifying-glass')
            ->color('gray')
            ->modalHeading('Recherche annuaire')
            ->schema([
                Wizard::make([
                    Step::make('Critères')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('ann_nom')->label('Nom'),
                                TextInput::make('ann_prenom')->label('Prénom'),
                                TextInput::make('ann_email')->label('Email'),
                                TextInput::make('ann_unite')->label('Unité'),
                            ]),
                        ])
                        ->afterValidation(function ($get, $set) {
                            $ok = AnnuaireUser::setQuery([
                                'nom'    => $get('ann_nom')    ?? '',
                                'prenom' => $get('ann_prenom') ?? '',
                                'email'  => $get('ann_email')  ?? '',
                                'unite'  => $get('ann_unite')  ?? '',
                            ]);
                            if (! $ok) {
                                Notification::make()->title('Annuaire inaccessible')->danger()->send();
                                throw ValidationException::withMessages(['ann_nom' => 'Annuaire inaccessible.']);
                            }
                            $set('ann_resultats', AnnuaireUser::query()->get()
                                ->mapWithKeys(fn ($u) => [$u->nid => trim("{$u->nom} {$u->prenom}") . ($u->unites ? " ({$u->unites})" : '')])
                                ->toArray());
                            $set('ann_nid', null);
                            $set('ann_data', null);
                        }),

                    Step::make('Résultats')
                        ->icon('heroicon-o-user-plus')
                        ->schema([
                            Select::make('ann_nid')
                                ->label('Sélectionner un utilisateur')
                                ->options(fn ($get) => $get('ann_resultats') ?? [])
                                ->required()
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (! $state) return;
                                    $user = AnnuaireUser::query()->get()->firstWhere('nid', $state);
                                    if ($user) {
                                        $set('ann_data', json_encode([
                                            'nom'    => $user->nom,
                                            'prenom' => $user->prenom ?? $user->prenomusuel ?? '',
                                            'email'  => $user->email,
                                        ]));
                                    }
                                }),
                            \Filament\Forms\Components\Hidden::make('ann_resultats'),
                            \Filament\Forms\Components\Hidden::make('ann_data'),
                        ]),
                ])->columnSpanFull(),
            ])
            ->action(function (array $data, $set) {
                $raw = $data['ann_data'] ?? null;
                if (! $raw) {
                    Notification::make()->title('Aucun utilisateur sélectionné.')->warning()->send();
                    return;
                }
                $info = json_decode($raw, true);
                $existing = User::where('email', $info['email'])->first();
                if ($existing) {
                    $set('user_id', $existing->id);
                    return;
                }
                \App\Events\UnUtilisateurLocalDoitEtreCreeEvent::dispatch($info, []);
                Notification::make()
                    ->title('Utilisateur ajouté')
                    ->body(trim("{$info['nom']} {$info['prenom']}") . ' a été créé. Sélectionnez-le dans la liste.')
                    ->success()->send();
            });
    }
}


