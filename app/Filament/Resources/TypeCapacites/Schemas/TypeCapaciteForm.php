<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Resa\Filament\Components\EmailListField;

class TypeCapaciteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations générales')
                ->description('Le nom et les réglages de base de la formation.')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    TextInput::make('libelle_court')
                        ->label('Nom de la formation')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),

                    Textarea::make('libelle_long')
                        ->label('Description')
                        ->rows(2)
                        ->columnSpanFull(),

                    Grid::make(3)->schema([
                        TextInput::make('ordre')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('nb_users_par_poste')
                            ->label('Stagiaires par poste')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10)
                            ->helperText('Nombre max partageant un même poste'),

                        Select::make('couleur')
                            ->label('Couleur')
                            ->options([
                                'primary'   => 'Primary',
                                'secondary' => 'Secondary',
                                'success'   => 'Success',
                                'danger'    => 'Danger',
                                'warning'   => 'Warning',
                                'info'      => 'Info',
                                'gray'      => 'Gray',
                            ])
                            ->native(false),
                    ]),
                ]),

            Section::make('Gestion')
                ->description('Qui gère cette formation, et qui est prévenu par email.')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Select::make('gestionnaires')
                        ->label('Gestionnaires')
                        ->helperText('Ces utilisateurs peuvent gérer cette formation et valider ses réservations.')
                        ->multiple()
                        ->relationship('gestionnaires', 'display_name')
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),

                    EmailListField::make('email'),
                ]),

            Section::make('Validation des réservations')
                ->description('Par défaut, une réservation reste en attente jusqu\'à ce qu\'un gestionnaire la valide. Vous pouvez activer la validation automatique — globalement, ou différemment selon des sous-catégories (ex. « Entraînement » auto-validé, « Examen » validé par un instructeur).')
                ->icon('heroicon-o-check-badge')
                ->schema([
                    Repeater::make('sousCategories')
                        ->relationship('sousCategories')
                        ->label('Sous-catégories')
                        ->helperText('Optionnel : laissez vide pour un réglage unique ci-dessous. Avec 2 sous-catégories ou plus, le réservataire devra en choisir une au moment de la réservation.')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('libelle_court')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Toggle::make('validation_auto')
                                    ->label('Validation automatique')
                                    ->live()
                                    ->columnSpan(2)
                                    ->inline(false),
                            ]),

                            Textarea::make('notice')
                                ->label('Consignes envoyées dans l\'email de confirmation')
                                ->helperText('Ex. où récupérer les clés, où se trouve la salle…')
                                ->rows(2)
                                ->visible(fn ($get) => (bool) $get('validation_auto'))
                                ->columnSpanFull(),
                        ])
                        ->orderColumn('ordre')
                        // Filament pré-remplit tout Repeater avec 1 ligne vide par défaut
                        // (Repeater::setUp() appelle defaultItems(1)) — sans ce ->defaultItems(0),
                        // une formation "simple" (0 sous-catégorie, cas normal) partait déjà
                        // avec une ligne invalide ("Nom" vide, required), bloquant toute
                        // création à la validation alors que l'utilisateur n'a jamais touché
                        // au Repeater.
                        ->defaultItems(0)
                        ->addActionLabel('Ajouter une sous-catégorie')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['libelle_court'] ?? null)
                        ->columnSpanFull(),

                    Grid::make(1)
                        ->schema([
                            Toggle::make('validation_auto')
                                ->label('Validation automatique')
                                ->helperText('Toute réservation sur cette formation est validée dès sa création.')
                                ->live(),

                            Textarea::make('notice')
                                ->label('Consignes envoyées dans l\'email de confirmation')
                                ->rows(3)
                                ->visible(fn ($get) => (bool) $get('validation_auto')),
                        ])
                        // N'a de sens que sans sous-catégorie (ou une seule) : au-delà, le
                        // réglage de chaque sous-catégorie prend le dessus (cf.
                        // Formation::validationAuto()) et afficher les deux prêterait à
                        // confusion.
                        ->visible(fn ($get) => count(array_filter($get('sousCategories') ?? [])) < 2)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
