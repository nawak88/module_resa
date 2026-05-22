<?php

namespace Modules\Resa\Filament\Resources\SallePostes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Resa\Models\Salle;
use Modules\Resa\Models\SallePoste;
use Filament\Tables\Columns\IconColumn;

class SallePostesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultSort('ordre')
        ->columns([
            TextColumn::make('salle.libelle_court')
                ->label('Salle')
                ->sortable()
                ->searchable(),

            TextColumn::make('libelle_court')
                ->label('Nom du poste')
                ->sortable()
                ->searchable(),

            TextColumn::make('typePoste.libelle_court')
                ->label('Type')
                ->badge()
                ->color('gray'),

            TextColumn::make('formations.libelle_court')
                ->label('Formations')
                ->badge()
                ->color('primary')
                ->separator(', '),

            TextColumn::make('reservations_count')
                ->label('Reservations')
                ->counts('reservations')
                ->badge()
                ->color('warning'),

            IconColumn::make('system')
                ->label('Systeme')
                ->boolean()
                ->trueIcon('heroicon-o-lock-closed')
                ->falseIcon('')
                ->trueColor('warning')
                ->tooltip(fn (SallePoste $record) => $record->system ? 'Poste systeme — non supprimable' : null),

            TextColumn::make('ordre')
                ->label('Ordre')
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('salle_id')
                ->label('Salle')
                ->options(fn () => Salle::orderBy('libelle_court')->pluck('libelle_court', 'id')),
        ])
        ->recordActions([
            EditAction::make()->iconButton(),
            DeleteAction::make()
                ->iconButton()
                ->disabled(fn (SallePoste $record) => $record->isSystem())
                ->tooltip(fn (SallePoste $record) => $record->isSystem() ? 'Poste systeme — non supprimable' : null),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->action(function ($records) {
                        $records->reject(fn ($r) => $r->isSystem())->each->delete();
                    }),
            ]),
        ]);
    }
}


