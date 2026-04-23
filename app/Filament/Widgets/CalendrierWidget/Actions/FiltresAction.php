<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Modules\Resa\Models\SallePoste;
use Modules\Resa\Models\TypeCapacite;

class FiltresAction
{
    public static function make(object $widget): Action
    {
        return Action::make('filtres')
            ->label('Filtres')
            ->icon('heroicon-o-funnel')
            ->color(
                (! empty($widget->filtrePostes) || ! empty($widget->filtreCapacites))
                    ? 'warning'
                    : 'gray'
            )
            ->mountUsing(fn ($form) => $form->fill([
                'postes'    => $widget->filtrePostes,
                'capacites' => $widget->filtreCapacites,
            ]))
            ->schema([
                CheckboxList::make('postes')
                    ->label('Postes')
                    ->options(
                        SallePoste::where('salle_id', $widget->salleActive)
                            ->orderBy('ordre')
                            ->pluck('libelle_court', 'id')
                    )
                    ->columns(2),

                CheckboxList::make('capacites')
                    ->label('Capacites')
                    ->options(TypeCapacite::orderBy('ordre')->pluck('libelle_court', 'id'))
                    ->columns(2),
            ])
            ->modalHeading('Filtrer le calendrier')
            ->modalSubmitActionLabel('Appliquer')
            ->extraModalFooterActions([
                Action::make('reinitialiser')
                    ->label('Reinitialiser')
                    ->color('gray')
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
