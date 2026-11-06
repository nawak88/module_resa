<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use App\Events\UnUtilisateurLocalDoitEtreCreeEvent;
use App\Models\AnnuaireUser;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Validation\ValidationException;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\FormationSousCategorie;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Services\PosteDisponibiliteService;

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
                    'debut'             => $widget->etapeDebut ?? now()->setTime(8, 0)->toDateTimeString(),
                    'fin'               => $widget->etapeFin   ?? now()->setTime(9, 0)->toDateTimeString(),
                    'formation_id'  => $widget->etapeFormations[0] ?? null,
                    'postes_attribution' => [],
                ]);
            })
            ->schema([
                Wizard::make([
                    self::etape1($widget),
                    self::etape2($widget),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString(
                    '<button type="submit" class="fi-btn fi-color-custom fi-btn-color-custom fi-btn-size-md fi-btn-primary px-4 py-2 rounded-lg bg-primary-600 text-white font-semibold shadow-sm hover:bg-primary-500">'
                    . 'Confirmer la réservation'
                    . '</button>'
                ))
                ->columnSpanFull(),
            ])
            ->modalHeading('Nouvelle reservation')
            ->modalSubmitAction(false)
            ->action(function (array $data) use ($widget): void {
                $postes = $data['postes_attribution'] ?? [];
                $formationId = $widget->etapeFormations[0] ?? null;
                $formation = $formationId ? Formation::find($formationId) : null;

                // 0 ou 1 sous-catégorie : la formation se comporte "comme avant" (ses
                // propres validation_auto/notice). 2+ : celle choisie en étape 1 prend
                // le dessus — ex. "FPV" > "Entraînement" (auto-validé) vs "Examen"
                // (validation_auto false, reste en attente d'un gestionnaire).
                $sousCategories = $formation?->sousCategories ?? collect();
                $sousCategorie = match (true) {
                    $sousCategories->count() === 1 => $sousCategories->first(),
                    $sousCategories->count() >= 2  => FormationSousCategorie::find($data['sous_categorie_id'] ?? null),
                    default                         => null,
                };

                $postesSelectionnes = array_filter($postes, fn ($l) => ! empty($l['selectionne']) && ! empty($l['user_1_id']));

                if (empty($postesSelectionnes)) {
                    Notification::make()
                        ->title('Veuillez sélectionner au moins un poste et attribuer un stagiaire.')
                        ->danger()
                        ->send();
                    return;
                }

                if (! $widget->adminMode && \Carbon\Carbon::parse($widget->etapeDebut)->lt(now())) {
                    Notification::make()->title('Impossible de reserver un creneau deja passe.')->danger()->send();
                    return;
                }

                $valideDirectement = $widget->adminMode || (bool) $formation?->validationAuto($sousCategorie);

                foreach ($postesSelectionnes as $ligne) {

                    Reservation::create([
                        'user_id'             => $ligne['user_1_id'],
                        'created_by'          => auth()->id(),
                        'salle_poste_id'      => $ligne['poste_id'],
                        'libelle_court'       => $formation?->libelle_court ?? '',
                        'debut'               => $widget->etapeDebut,
                        'fin'                 => $widget->etapeFin,
                        'formations_utilisees' => $formationId ? [(int) $formationId] : [],
                        'sous_categorie_id'   => $sousCategorie?->id,
                        'validation'          => $valideDirectement ? Reservation::VALIDE : Reservation::EN_ATTENTE,
                        'validated_by'        => $valideDirectement ? auth()->id() : null,
                        'validated_at'        => $valideDirectement ? now()         : null,
                        'data'                => [
                            'users'            => array_values(array_filter([$ligne['user_1_id'], $ligne['user_2_id'] ?? null])),
                            'formation_id' => $formationId,
                        ],
                    ]);
                }

                $widget->etapeDebut        = null;
                $widget->etapeFin          = null;
                $widget->etapeFormations    = [];
                $widget->postesDisponibles = [];

                $widget->refreshRecords();

                Notification::make()
                    ->title('Demande envoyee')
                    ->body($widget->adminMode ? 'Reservation creee et validee.' : 'Votre demande est en attente de validation.')
                    ->success()
                    ->send();
            });
    }

    private static function etape1(object $widget): Step
    {
        return Step::make('Creneau & Formation')
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

                Select::make('formation_id')
                    ->label('Type de formation')
                    ->required()
                    ->options(Formation::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('sous_categorie_id', null))
                    ->columnSpanFull(),

                // Affiché seulement s'il y a un vrai choix à faire (2+ sous-catégories) :
                // avec 0 ou 1, la formation garde son propre réglage de validation.
                Select::make('sous_categorie_id')
                    ->label('Sous-catégorie')
                    ->required()
                    ->options(fn ($get) => Formation::find($get('formation_id'))?->sousCategories->pluck('libelle_court', 'id') ?? [])
                    ->visible(fn ($get) => (Formation::find($get('formation_id'))?->sousCategories->count() ?? 0) >= 2)
                    ->columnSpanFull(),
            ])
            ->afterValidation(function ($get, $set) use ($widget) {
                $erreur = $widget->validerCreneauPublic($get('debut'), $get('fin'));
                if ($erreur) {
                    Notification::make()->title($erreur)->danger()->send();
                    throw ValidationException::withMessages(['debut' => $erreur]);
                }

                $formationId = $get('formation_id');

                $service = app(PosteDisponibiliteService::class);
                $postes = $service->postesDisponibles(
                    salleId:      $widget->salleActive,
                    debut:        new \DateTime($get('debut')),
                    fin:          new \DateTime($get('fin')),
                    formationIds: [$formationId],
                );

                $widget->etapeDebut      = $get('debut');
                $widget->etapeFin        = $get('fin');
                $widget->etapeFormations = [$formationId];

                $widget->postesDisponibles = $postes->mapWithKeys(function ($item) {
                    return [$item['poste']->id => $item['poste']->libelle_court];
                })->toArray();

                $data = $postes->map(function ($item) {
                    return [
                        'poste_id'       => $item['poste']->id,
                        'poste_libelle'  => $item['poste']->libelle_court,
                        'selectionne'    => false,
                        'ajouter_user_2' => false,
                        'user_1_id'      => null,
                        'user_2_id'      => null,
                    ];
                })->values()->toArray();

                $set('postes_attribution', $data);
            });
    }

    private static function etape2(object $widget): Step
    {
        return Step::make('Attribution des postes')
            ->icon('heroicon-o-computer-desktop')
            ->schema(function () use ($widget): array {
                $formation       = isset($widget->etapeFormations[0]) ? Formation::find($widget->etapeFormations[0]) : null;
                $nbUsersParPoste = $formation?->nb_users_par_poste ?? 1;
                $isIntradef      = config('skeletor.reseau_de_deploiement') === 'intradef';
                $showAnnuaire    = $isIntradef || config('skeletor.reseau_de_deploiement') === 'sic21';

                return [
                    TextEntry::make('recap_creneau')
                        ->label('Creneau')
                        ->getStateUsing(function () use ($widget) {
                            if (! $widget->etapeDebut) return '—';
                            return \Carbon\Carbon::parse($widget->etapeDebut)->format('d/m/Y H:i')
                                . ' — '
                                . \Carbon\Carbon::parse($widget->etapeFin)->format('H:i');
                        }),

                    Repeater::make('postes_attribution')
                        ->label('Postes disponibles')
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->schema([
                            Hidden::make('poste_id'),

                            Toggle::make('selectionne')
                                ->label(fn ($get) => $get('poste_libelle') ?? 'Poste')
                                ->live()
                                ->default(false)
                                ->columnSpanFull(),

                            // ── Stagiaire 1 ───────────────────────────────
                            Select::make('user_1_id')
                                ->label('Stagiaire 1')
                                ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                                ->searchable()
                                ->nullable()
                                ->visible(fn ($get) => (bool) $get('selectionne'))
                                ->columnSpanFull(),

                            // ── Bouton annuaire pour user 1 ───────────────
                            ...($showAnnuaire ? [
                                \Filament\Schemas\Components\Actions::make([
                                    self::annuaireAction('user_1_id'),
                                ])->visible(fn ($get) => (bool) $get('selectionne')),
                            ] : []),

                            // ── Toggle + Stagiaire 2 ──────────────────────
                            Toggle::make('ajouter_user_2')
                                ->label('Ajouter un 2ème stagiaire')
                                ->live()
                                ->default(false)
                                ->visible(fn ($get) => (bool) $get('selectionne') && $nbUsersParPoste >= 2)
                                ->columnSpanFull(),

                            Select::make('user_2_id')
                                ->label('Stagiaire 2')
                                ->options(fn () => User::orderBy('nom')->get()->mapWithKeys(fn ($u) => [$u->id => trim("{$u->nom} {$u->prenom}")]))
                                ->searchable()
                                ->nullable()
                                ->visible(fn ($get) => (bool) $get('selectionne') && (bool) $get('ajouter_user_2') && $nbUsersParPoste >= 2)
                                ->columnSpanFull(),

                            ...($showAnnuaire ? [
                                \Filament\Schemas\Components\Actions::make([
                                    self::annuaireAction('user_2_id'),
                                ])->visible(fn ($get) => (bool) $get('selectionne') && (bool) $get('ajouter_user_2') && $nbUsersParPoste >= 2),
                            ] : []),
                        ])
                        ->columnSpanFull(),
                ];
            });
    }

    private static function annuaireAction(string $targetField): Action
    {
        return Action::make('annuaire_' . $targetField)
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
                                Notification::make()
                                    ->title('Annuaire inaccessible')
                                    ->body('La connexion à l\'annuaire a expiré. Réessayez dans quelques instants.')
                                    ->danger()
                                    ->send();
                                throw ValidationException::withMessages(['ann_nom' => 'Annuaire inaccessible.']);
                            }

                            $options = AnnuaireUser::query()->get()
                                ->mapWithKeys(fn ($u) => [
                                    $u->nid => trim("{$u->nom} {$u->prenom}") . ($u->unites ? " ({$u->unites})" : ''),
                                ])
                                ->toArray();

                            $set('ann_resultats', $options);
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
                            Hidden::make('ann_resultats'),
                            Hidden::make('ann_data'),
                        ]),
                ])->columnSpanFull(),
            ])
            ->action(function (array $data) {
                $raw = $data['ann_data'] ?? null;
                if (! $raw) {
                    Notification::make()->title('Aucun utilisateur sélectionné.')->warning()->send();
                    return;
                }

                $info = json_decode($raw, true);

                $existing = User::where('email', $info['email'])->first();
                if ($existing) {
                    Notification::make()
                        ->title('Utilisateur déjà dans la liste')
                        ->body(trim("{$info['nom']} {$info['prenom']}") . ' est déjà disponible dans le sélecteur.')
                        ->info()->send();
                    return;
                }

                UnUtilisateurLocalDoitEtreCreeEvent::dispatch($info, []);

                Notification::make()
                    ->title('Utilisateur ajouté')
                    ->body(trim("{$info['nom']} {$info['prenom']}") . ' a été créé. Sélectionnez-le dans la liste.')
                    ->success()->send();
            });
    }
}
