<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TypeCapaciteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('libelle_court')
                ->label('Nom de la formation')
                ->required()
                ->maxLength(500)
                ->columnSpanFull(),

            Textarea::make('libelle_long')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->default(0)
                ->minValue(0),

            TextInput::make('nb_users_par_poste')
                ->label('Stagiaires par poste')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->maxValue(10)
                ->helperText('Nombre max de stagiaires pouvant partager un même poste'),

            Select::make('gestionnaires')
                ->label('Gestionnaires')
                ->helperText('Ces utilisateurs peuvent gérer cette formation et valider ses réservations')
                ->multiple()
                ->relationship('gestionnaires', 'display_name')
                ->searchable()
                ->preload()
                ->columnSpanFull(),

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

            TextInput::make('email')
                ->label('Email(s) de notification')
                ->helperText('Plusieurs adresses séparées par des virgules')
                ->maxLength(500)
                ->columnSpanFull(),
        ]);
    }
}


