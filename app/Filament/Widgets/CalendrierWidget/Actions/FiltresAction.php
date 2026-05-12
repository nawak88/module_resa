<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;

class FiltresAction
{
    public static function make(object $widget): Action
    {
        $hasFilters = ! empty($widget->filtrePostes) || ! empty($widget->filtreCapacites);

        return Action::make('filtres')
            ->label('Filtres')
            ->icon('heroicon-o-funnel')
            ->color($hasFilters ? 'warning' : 'gray')
            ->badge($hasFilters ? (count($widget->filtrePostes) + count($widget->filtreCapacites)) : null)
            ->mountUsing(fn ($form) => $form->fill([
                'postes'    => $widget->filtrePostes,
                'capacites' => $widget->filtreCapacites,
            ]))
            ->schema([
                Section::make('Postes')
                    ->description('Afficher uniquement les réservations de ces postes')
                    ->icon('heroicon-o-computer-desktop')
                    ->compact()
                    ->schema([
                        ToggleButtons::make('postes')
                            ->label('')
                            ->multiple()
                            ->options(
                                SallePoste::where('salle_id', $widget->salleActive)
                                    ->orderBy('ordre')
                                    ->pluck('libelle_court', 'id')
                            )
                            ->colors(array_fill_keys(
                                SallePoste::where('salle_id', $widget->salleActive)
                                    ->orderBy('ordre')
                                    ->pluck('id')
                                    ->map(fn ($id) => (string) $id)
                                    ->toArray(),
                                'primary'
                            ))
                            ->grouped(),
                    ]),

                Section::make('Capacités')
                    ->description('Filtrer par type de capacité utilisé')
                    ->icon('heroicon-o-tag')
                    ->compact()
                    ->schema([
                        ToggleButtons::make('capacites')
                            ->label('')
                            ->multiple()
                            ->options(TypeCapacite::orderBy('ordre')->pluck('libelle_court', 'id'))
                            ->colors(array_fill_keys(
                                TypeCapacite::orderBy('ordre')
                                    ->pluck('id')
                                    ->map(fn ($id) => (string) $id)
                                    ->toArray(),
                                'info'
                            ))
                            ->grouped(),
                    ]),
            ])
            ->modalHeading('Filtrer le calendrier')
            ->modalDescription('Sélectionnez les éléments à afficher sur le calendrier.')
            ->modalIcon('heroicon-o-adjustments-horizontal')
            ->modalWidth('lg')
            ->modalSubmitActionLabel('Appliquer les filtres')
            ->extraModalFooterActions([
                Action::make('reinitialiser')
                    ->label('Tout effacer')
                    ->color('danger')
                    ->outlined()
                    ->icon('heroicon-o-x-mark')
                    ->action(function () use ($widget): void {
                        $widget->filtrePostes    = [];
                        $widget->filtreCapacites = [];
                        $widget->refreshRecords();
                    }),
            ])
            ->action(function (array $data) use ($widget): void {
                $widget->filtrePostes    = $data['postes'] ?? [];
                $widget->filtreCapacites = $data['capacites'] ?? [];
                $widget->refreshRecords();
            });
    }
}
