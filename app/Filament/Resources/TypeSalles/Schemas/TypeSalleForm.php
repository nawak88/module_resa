<?php

namespace Modules\Resa\Filament\Resources\TypeSalles\Schemas;

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
            ]);
    }
}
