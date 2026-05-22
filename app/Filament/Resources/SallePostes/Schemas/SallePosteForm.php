<?php

namespace Modules\Resa\Filament\Resources\SallePostes\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\TypePoste;

class SallePosteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('salle_id')
                ->label('Salle')
                ->options(fn () => Salle::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                ->searchable()
                ->required()
                ->disabledOn('edit'),

            Select::make('type_poste_id')
                ->label('Type de poste')
                ->options(fn () => TypePoste::orderBy('ordre')->pluck('libelle_court', 'id'))
                ->searchable()
                ->required(),

            TextInput::make('libelle_court')
                ->label('Nom du poste')
                ->required()
                ->maxLength(500),

            TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->default(0)
                ->minValue(0),

            Textarea::make('libelle_long')
                ->label('Description')
                ->rows(3)
                ->columnSpanFull(),

            CheckboxList::make('formations')
                ->label('Formations disponibles sur ce poste')
                ->relationship('formations', 'libelle_court')
                ->columns(3)
                ->columnSpanFull()
                ->helperText('Selectionnez les capacites disponibles sur ce poste.')
                ->hidden(fn (?SallePoste $record) => $record?->isSystem()),

            Toggle::make('system')
                ->label('Poste systeme (non supprimable)')
                ->disabled()
                ->helperText('Ce champ est gere automatiquement par le systeme.')
                ->hidden(fn (?SallePoste $record) => ! $record?->isSystem()),
        ]);
    }
}


