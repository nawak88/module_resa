<?php

namespace Modules\Resa\Filament\Resources\TypeSalles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TypeSalleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle_court')
                    ->label('Nom du type de salle')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Textarea::make('libelle_long')
                    ->label('Description')
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('ordre')
                    ->label('Ordre d\'affichage')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),

                Select::make('icone')
                    ->label('Icône')
                    ->searchable()
                    ->options(self::iconOptions())
                    ->allowHtml()
                    ->placeholder('Choisir une icône'),

                Select::make('couleur')
                    ->label('Couleur')
                    ->options(self::colorOptions())
                    ->allowHtml()
                    ->placeholder('Choisir une couleur'),
            ]);
    }

    public static function iconOptions(): array
    {
        $icons = [
            'heroicon-o-academic-cap'        => 'Académique',
            'heroicon-o-archive-box'         => 'Archive',
            'heroicon-o-arrow-path'          => 'Rafraîchir',
            'heroicon-o-beaker'              => 'Laboratoire',
            'heroicon-o-bolt'                => 'Électricité',
            'heroicon-o-book-open'           => 'Livre ouvert',
            'heroicon-o-briefcase'           => 'Mallette',
            'heroicon-o-building-library'    => 'Bibliothèque',
            'heroicon-o-building-office'     => 'Bureau',
            'heroicon-o-building-office-2'   => 'Immeuble',
            'heroicon-o-calendar-days'       => 'Calendrier',
            'heroicon-o-chart-bar'           => 'Graphique',
            'heroicon-o-chat-bubble-left'    => 'Salle de réunion',
            'heroicon-o-circle-stack'        => 'Serveurs',
            'heroicon-o-clipboard-document'  => 'Presse-papier',
            'heroicon-o-cloud'               => 'Cloud',
            'heroicon-o-cog-6-tooth'         => 'Paramètres',
            'heroicon-o-command-line'        => 'Terminal',
            'heroicon-o-computer-desktop'    => 'Ordinateur',
            'heroicon-o-cube'                => 'Cube',
            'heroicon-o-document-text'       => 'Document',
            'heroicon-o-film'                => 'Cinéma',
            'heroicon-o-fire'                => 'Feu',
            'heroicon-o-flag'                => 'Drapeau',
            'heroicon-o-globe-alt'           => 'Globe',
            'heroicon-o-home'                => 'Maison',
            'heroicon-o-key'                 => 'Clé',
            'heroicon-o-light-bulb'          => 'Ampoule',
            'heroicon-o-map-pin'             => 'Localisation',
            'heroicon-o-microphone'          => 'Microphone',
            'heroicon-o-musical-note'        => 'Musique',
            'heroicon-o-paint-brush'         => 'Peinture',
            'heroicon-o-photo'               => 'Photo',
            'heroicon-o-presentation-chart-bar' => 'Présentation',
            'heroicon-o-printer'             => 'Imprimante',
            'heroicon-o-puzzle-piece'        => 'Puzzle',
            'heroicon-o-rocket-launch'       => 'Fusée',
            'heroicon-o-server'              => 'Serveur',
            'heroicon-o-shield-check'        => 'Sécurité',
            'heroicon-o-signal'              => 'Signal',
            'heroicon-o-speaker-wave'        => 'Son',
            'heroicon-o-squares-2x2'         => 'Grille',
            'heroicon-o-star'                => 'Étoile',
            'heroicon-o-table-cells'         => 'Tableau',
            'heroicon-o-tv'                  => 'TV',
            'heroicon-o-truck'               => 'Véhicule',
            'heroicon-o-user-group'          => 'Groupe',
            'heroicon-o-video-camera'        => 'Caméra',
            'heroicon-o-wifi'                => 'Wifi',
            'heroicon-o-wrench'              => 'Outil',
        ];

        $options = [];
        foreach ($icons as $value => $label) {
            $svg = '<x-' . $value . ' class="inline w-4 h-4 mr-1" />';
            $options[$value] = $label;
        }
        return $options;
    }

    public static function colorOptions(): array
    {
        return [
            'primary'  => '🔵 Primary',
            'secondary'=> '⚫ Secondary',
            'success'  => '🟢 Vert (success)',
            'warning'  => '🟡 Jaune (warning)',
            'danger'   => '🔴 Rouge (danger)',
            'info'     => '🩵 Info',
            'gray'     => '🩶 Gris',
            '#3b82f6'  => '🔵 Bleu',
            '#6366f1'  => '🟣 Indigo',
            '#8b5cf6'  => '🟣 Violet',
            '#ec4899'  => '🩷 Rose',
            '#f97316'  => '🟠 Orange',
            '#eab308'  => '🟡 Jaune',
            '#22c55e'  => '🟢 Vert',
            '#14b8a6'  => '🩵 Teal',
            '#06b6d4'  => '🔵 Cyan',
            '#0ea5e9'  => '🔵 Sky',
            '#64748b'  => '🩶 Slate',
            '#78716c'  => '🤎 Stone',
        ];
    }
}


