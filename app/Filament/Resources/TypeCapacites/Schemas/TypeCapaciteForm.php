<?php

namespace Modules\Resa\Filament\Resources\TypeCapacites\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TypeCapaciteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('libelle_court')
                ->label('Nom du type de poste')
                ->required()
                ->maxLength(500)
                ->columnSpanFull()
                ->helperText('Ex: Poste standard, PC VR'),
            Textarea::make('libelle_long')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),
            TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->default(0)
                ->minValue(0),
        ]);
    }
}
