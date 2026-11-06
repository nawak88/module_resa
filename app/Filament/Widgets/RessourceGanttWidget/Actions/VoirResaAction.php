<?php

namespace Modules\Resa\Filament\Widgets\RessourceGanttWidget\Actions;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Modules\Resa\Models\RessourceReservation;

class VoirResaAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirResa')
            ->label('Réservation')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->extraAttributes(['style' => 'display:none'])
            ->modalHeading(fn () => $widget->viewingId
                ? (RessourceReservation::find($widget->viewingId)?->ressource?->nom ?? 'Réservation')
                : 'Réservation'
            )
            ->modalSubmitAction(false)
            ->schema(fn (): array => self::schema($widget))
            ->extraModalFooterActions(fn (): array => self::footerActions($widget))
            ->action(fn () => null);
    }

    private static function schema(object $widget): array
    {
        $r = fn () => $widget->viewingId
            ? RessourceReservation::with(['ressource', 'user', 'validatedBy', 'emprunteur', 'officierResponsable'])->find($widget->viewingId)
            : null;

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Début')
                    ->getStateUsing(fn () => $r()?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $r()?->fin?->format('d/m/Y H:i') ?? '—'),
            ]),

            TextEntry::make('ressource_ph')
                ->label('Ressource')
                ->getStateUsing(fn () => $r()?->ressource?->nom ?? '—'),

            TextEntry::make('libelle_ph')
                ->label('Objet')
                ->getStateUsing(fn () => $r()?->libelle ?: '(sans objet)'),

            TextEntry::make('user_ph')
                ->label('Réservé par')
                ->getStateUsing(fn () => $r()?->user?->nom ?? '—'),

            Grid::make(2)->schema([
                TextEntry::make('batiment_ph')
                    ->label('Bâtiment')
                    ->getStateUsing(fn () => $r()?->batiment ?? '—'),

                TextEntry::make('service_ph')
                    ->label('Service')
                    ->getStateUsing(fn () => $r()?->service ?? '—'),
            ]),

            Grid::make(2)->schema([
                TextEntry::make('emprunteur_ph')
                    ->label('Emprunteur')
                    ->getStateUsing(fn () => $r()?->emprunteur
                        ? trim($r()->emprunteur->nom . ' ' . $r()->emprunteur->prenom) . ' (' . $r()->emprunteur_grade . ')'
                        : '—'),

                TextEntry::make('officier_ph')
                    ->label('Officier responsable')
                    ->getStateUsing(fn () => $r()?->officierResponsable
                        ? trim($r()->officierResponsable->nom . ' ' . $r()->officierResponsable->prenom) . ' (' . $r()->officier_responsable_grade . ')'
                        : '—'),
            ]),

            TextEntry::make('statut_ph')
                ->label('Statut')
                ->getStateUsing(fn () => $r()?->isValide() ? 'Validée' : 'En attente')
                ->badge()
                ->color(fn ($state) => $state === 'Validée' ? 'success' : 'warning'),

            TextEntry::make('valide_par_ph')
                ->label('Validée par')
                ->visible(fn () => $widget->viewingId && $r()?->isValide())
                ->getStateUsing(fn () => ($r()?->validatedBy?->nom ?? '') . ' — ' . ($r()?->validated_at?->format('d/m/Y H:i') ?? '')),
        ];
    }

    private static function footerActions(object $widget): array
    {
        if (! $widget->viewingId) return [];

        $r = RessourceReservation::find($widget->viewingId);
        if (! $r) return [];

        $actions = [];

        $canModify = (! $r->isValide()) && ($widget->adminMode || auth()->id() === $r->user_id);

        if ($canModify) {
            $actions[] = Action::make('supprimer_resa')
                ->label('Supprimer')
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->requiresConfirmation()
                ->action(function () use ($r, $widget): void {
                    $id = $r->id;
                    $widget->viewingId = null;
                    RessourceReservation::destroy($id);
                    $widget->refreshRecords();
                    Notification::make()->title('Réservation supprimée')->success()->send();
                });
        }

        if (auth()->user()?->can('valider', $r)) {
            $actions[] = Action::make('toggle_validation')
                ->label($r->isValide() ? 'Invalider' : 'Valider')
                ->color($r->isValide() ? 'warning' : 'success')
                ->icon($r->isValide() ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->action(function () use ($r, $widget): void {
                    $r->isValide() ? $r->invalider() : $r->valider(auth()->user());
                    $widget->refreshRecords();
                    Notification::make()->title($r->fresh()->isValide() ? 'Validée' : 'Invalidée')->success()->send();
                });
        }

        return $actions;
    }
}
