<?php

namespace Modules\Resa\Filament\Widgets\CalendrierWidget\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Modules\Resa\Models\Reservation;
use Modules\Resa\Models\Formation;

class VoirReservationAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirReservation')
            ->label('')
            ->extraAttributes(['style' => 'display:none'])
            ->schema(fn (): array => self::schema($widget))
            ->modalHeading(fn () => $widget->recordTitre ?? 'Reservation')
            ->modalSubmitAction(false)
            ->extraModalFooterActions(fn (): array => self::footerActions($widget))
            ->action(fn () => null);
    }

    private static function schema(object $widget): array
    {
        $r = fn () => $widget->recordEnCours ? Reservation::find($widget->recordEnCours) : null;

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Debut')
                    ->getStateUsing(fn () => $r()?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $r()?->fin?->format('d/m/Y H:i') ?? '—'),
            ]),

            TextEntry::make('poste_ph')
                ->label('Poste')
                ->getStateUsing(fn () => $r()?->sallePoste?->libelle_court ?? '—'),

            TextEntry::make('salle_ph')
                ->label('Salle')
                ->getStateUsing(fn () => $r()?->sallePoste?->salle?->libelle_court ?? '—'),

            TextEntry::make('user_ph')
                ->label('Stagiaire 1')
                ->getStateUsing(fn () => $r()?->user?->nom ?? '—'),

            TextEntry::make('user2_ph')
                ->label('Stagiaire 2')
                ->visible(function () use ($r) {
                    $record = $r();
                    return $record && ! empty($record->data['users'][1]);
                })
                ->getStateUsing(function () use ($r) {
                    $record = $r();
                    $user2Id = $record?->data['users'][1] ?? null;
                    if (! $user2Id) return '—';
                    return \App\Models\User::find($user2Id)?->nom ?? '—';
                }),

            TextEntry::make('capacites_ph')
                ->label('Capacites utilisees')
                ->getStateUsing(function () use ($r) {
                    $record = $r();
                    if (! $record || empty($record->formations_utilisees)) return '—';
                    return Formation::whereIn('id', $record->formations_utilisees)
                        ->pluck('libelle_court')->join(', ');
                }),

            TextEntry::make('libelle_long_ph')
                ->label('Informations')
                ->getStateUsing(fn () => $r()?->libelle_long ?? '—'),

            TextEntry::make('statut_ph')
                ->label('Statut')
                ->getStateUsing(fn () => $r()?->isValide() ? 'Validee' : 'En attente de validation')
                ->badge()
                ->color(fn ($state) => $state === 'Validee' ? 'success' : 'warning'),

            TextEntry::make('valide_par_ph')
                ->label('Validee par')
                ->visible(fn () => $widget->recordEnCours && $r()?->isValide())
                ->getStateUsing(fn () => ($r()?->validatedBy?->nom ?? '') . ' - ' . ($r()?->validated_at?->format('d/m/Y H:i') ?? '')),
        ];
    }

    private static function footerActions(object $widget): array
    {
        $record = $widget->recordEnCours ? Reservation::find($widget->recordEnCours) : null;
        if (! $record) return [];

        $actions = [];

        $canModify = $record->isEnAttente()
            && ($widget->adminMode || auth()->id() === $record->user_id);

        if ($canModify) {
            $actions[] = Action::make('go_supprimer')
                ->label('Supprimer')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () use ($record, $widget): void {
                    $id = $record->id;
                    $widget->recordEnCours = null;
                    $widget->recordTitre   = null;
                    $widget->eventRecord   = null;
                    Reservation::destroy($id);
                    $widget->refreshRecords();
                    Notification::make()->title('Reservation supprimee')->success()->send();
                });
        }

        $peutValider = $widget->adminMode && auth()->user()?->can('valider', $record);

        if ($peutValider) {
            $actions[] = Action::make('toggle_validation')
                ->label($record->isValide() ? 'Invalider' : 'Valider')
                ->color($record->isValide() ? 'warning' : 'success')
                ->icon($record->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function () use ($record, $widget): void {
                    $record->isValide() ? $record->invalider() : $record->valider(auth()->user());
                    $widget->refreshRecords();
                    Notification::make()
                        ->title($record->fresh()->isValide() ? 'Reservation validee' : 'Reservation invalidee')
                        ->success()->send();
                });
        }

        return $actions;
    }
}



