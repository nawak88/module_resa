<?php

namespace Modules\Resa\Filament\Resources\TypePostes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Resa\Models\TypePoste;
use Filament\Tables\Columns\IconColumn;

class TypePostesTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->defaultSort('ordre')
        ->columns([
            TextColumn::make('libelle_court')
                ->label('Nom')
                ->searchable()
                ->sortable(),

            TextColumn::make('libelle_long')
                ->label('Description')
                ->limit(60)
                ->toggleable(),

            TextColumn::make('sallePostes_count')
                ->label('Postes')
                ->counts('sallePostes')
                ->badge()
                ->color('primary'),

            IconColumn::make('system')
                ->label('Systeme')
                ->boolean()
                ->trueIcon('heroicon-o-lock-closed')
                ->falseIcon('heroicon-o-lock-open')
                ->trueColor('warning')
                ->falseColor('gray')
                ->tooltip(fn (TypePoste $record) => $record->system ? 'Entree systeme — non supprimable' : null),

            TextColumn::make('ordre')
                ->label('Ordre')
                ->sortable(),
        ])
        ->recordActions([
            EditAction::make()->iconButton(),
            DeleteAction::make()
                ->iconButton()
                ->disabled(fn (TypePoste $record) => $record->isSystem())
                ->tooltip(fn (TypePoste $record) => $record->isSystem() ? 'Entree systeme — non supprimable' : null),
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
