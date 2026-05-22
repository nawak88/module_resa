<?php

namespace Modules\Resa\Services;

use Illuminate\Support\Collection;
use Modules\Resa\Models\SallePoste;

class PosteDisponibiliteService
{
    /**
     * Retourne les postes disponibles pour un créneau et des capacités données,
     * triés par score décroissant (capacités couvertes / demandées).
     *
     * @param  int       $salleId          ID de la salle
     * @param  \DateTime $debut            Début du créneau
     * @param  \DateTime $fin              Fin du créneau
     * @param  array     $formationIds     IDs des Formation demandées
     * @param  int|null  $excludeResaId    ID réservation à exclure (pour modification)
     * @return Collection<array{poste: SallePoste, score: int, total: int, manquantes: Collection}>
     */
    public function postesDisponibles(
        int $salleId,
        \DateTime $debut,
        \DateTime $fin,
        array $formationIds = [],
        ?int $excludeResaId = null
    ): Collection {
        $postes = SallePoste::query()
            ->where('salle_id', $salleId)
            ->with('formations')
            ->orderBy('ordre')
            ->get();

        return $postes
            ->filter(fn (SallePoste $poste) => $poste->isDisponible($debut, $fin, $excludeResaId))
            ->map(function (SallePoste $poste) use ($formationIds) {
                $formationsPoste = $poste->formations->pluck('id');
                $score           = empty($formationIds)
                    ? 0
                    : $formationsPoste->intersect($formationIds)->count();
                $manquantes      = collect($formationIds)->diff($formationsPoste);

                return [
                    'poste'      => $poste,
                    'score'      => $score,
                    'total'      => count($formationIds),
                    'manquantes' => $manquantes, // IDs des formations non couvertes
                ];
            })
            ->filter(fn ($item) => empty($formationIds) || $item['score'] > 0)
            ->sortByDesc('score')
            ->values();
    }

    /**
     * Vérifie si la salle est complète pour un créneau et des capacités données.
     * La salle est complète si aucun poste libre ne couvre au moins une capacité demandée.
     */
    public function salleComplete(
        int $salleId,
        \DateTime $debut,
        \DateTime $fin,
        array $formationIds = []
    ): bool {
        return $this->postesDisponibles($salleId, $debut, $fin, $formationIds)->isEmpty();
    }
}


