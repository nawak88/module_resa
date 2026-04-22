<?php

namespace Modules\Resa\Filament\Resources\TypeSalles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TypeSallesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('ordre', 'asc')
            ->reorderable('ordre')
            ->columns([
                TextColumn::make('libelle_court')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('libelle_long')
                    ->label('Description')
                    ->limit(60)
                    ->toggleable(),

                TextColumn::make('salles_count')
                    ->label('Salles')
                    ->counts('salles')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
