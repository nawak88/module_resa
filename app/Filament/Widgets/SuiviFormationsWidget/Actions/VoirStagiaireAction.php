<?php

namespace Modules\Resa\Filament\Widgets\SuiviFormationsWidget\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;
use Modules\RH\Models\Marin;

class VoirStagiaireAction
{
    public static function make(object $widget): Action
    {
        return Action::make('voirStagiaire')
            ->label('')
            ->extraAttributes(['style' => 'display:none'])
            ->modalHeading('Détail de la réservation')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer')
            ->schema(fn (array $arguments): array => self::schema($arguments['reservationId'] ?? null))
            ->action(fn () => null);
    }

    private static function schema(?int $reservationId): array
    {
        $reservation = $reservationId ? Reservation::find($reservationId) : null;

        $userIds = collect($reservation?->data['users'] ?? [])->filter()->values();
        if ($userIds->isEmpty() && $reservation?->user_id) {
            $userIds = collect([$reservation->user_id]);
        }

        $marins = User::whereIn('id', $userIds)->get()
            ->map(fn (User $u) => Marin::fromUser($u) ?? $u)
            ->values();

        return [
            Grid::make(2)->schema([
                TextEntry::make('debut_ph')
                    ->label('Début')
                    ->getStateUsing(fn () => $reservation?->debut?->format('d/m/Y H:i') ?? '—'),

                TextEntry::make('fin_ph')
                    ->label('Fin')
                    ->getStateUsing(fn () => $reservation?->fin?->format('H:i') ?? '—'),
            ]),

            TextEntry::make('salle_poste_ph')
                ->label('Salle / Poste')
                ->getStateUsing(fn () => trim(
                    ($reservation?->sallePoste?->salle?->libelle_court ?? '')
                    . ' — ' . ($reservation?->sallePoste?->libelle_court ?? '')
                )),

            TextEntry::make('capacites_ph')
                ->label('Formation')
                ->getStateUsing(fn () => empty($reservation?->formations_utilisees)
                    ? '—'
                    : Formation::whereIn('id', $reservation->formations_utilisees)->pluck('libelle_court')->join(', ')),

            TextEntry::make('libelle_ph')
                ->label('Objet')
                ->getStateUsing(fn () => $reservation?->libelle_court ?: ($reservation?->libelle_long ?? '—')),

            ...self::schemaStagiaires($marins),
        ];
    }

    /** Une section par stagiaire, avec ses infos RH quand disponibles (grade, spécialité, unité...). */
    private static function schemaStagiaires($marins): array
    {
        if ($marins->isEmpty()) {
            return [
                TextEntry::make('aucun_stagiaire_ph')->label('Stagiaire')->getStateUsing(fn () => '—'),
            ];
        }

        return $marins->flatMap(function ($marin, $index) {
            $estMarin = $marin instanceof Marin;
            $nom = $estMarin
                ? trim("{$marin->nom} {$marin->prenom}")
                : trim("{$marin->nom} {$marin->prenom}");

            return [
                Grid::make(2)->schema([
                    TextEntry::make("stagiaire_{$index}_nom")
                        ->label('Stagiaire ' . ($index + 1))
                        ->getStateUsing(fn () => $nom ?: '—'),

                    TextEntry::make("stagiaire_{$index}_matricule")
                        ->label('Matricule')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->matricule ?? '—'),

                    TextEntry::make("stagiaire_{$index}_grade")
                        ->label('Grade')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->grade?->libelle_court ?? '—'),

                    TextEntry::make("stagiaire_{$index}_specialite")
                        ->label('Spécialité')
                        ->visible($estMarin)
                        ->getStateUsing(fn () => $marin->specialite?->libelle_court ?? '—'),

                    TextEntry::make("stagiaire_{$index}_unite")
                        ->label('Unité')
                        ->getStateUsing(fn () => $estMarin
                            ? ($marin->getUnite(explore_user: true)?->libelle_court ?? '—')
                            : '—'),

                    TextEntry::make("stagiaire_{$index}_email")
                        ->label('Email')
                        ->getStateUsing(fn () => $marin->email ?? '—'),
                ]),
            ];
        })->values()->all();
    }
}
