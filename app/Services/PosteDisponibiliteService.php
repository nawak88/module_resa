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
     * @param  array     $typeCapaciteIds  IDs des TypeCapacite demandées
     * @param  int|null  $excludeResaId    ID réservation à exclure (pour modification)
     * @return Collection<array{poste: SallePoste, score: int, total: int, manquantes: Collection}>
     */
    public function postesDisponibles(
        int $salleId,
        \DateTime $debut,
        \DateTime $fin,
        array $typeCapaciteIds = [],
        ?int $excludeResaId = null
    ): Collection {
        $postes = SallePoste::query()
            ->where('salle_id', $salleId)
            ->with('typeCapacites')
            ->orderBy('ordre')
            ->get();

        return $postes
            ->filter(fn (SallePoste $poste) => $poste->isDisponible($debut, $fin, $excludeResaId))
            ->map(function (SallePoste $poste) use ($typeCapaciteIds) {
                $capacitesPoste  = $poste->typeCapacites->pluck('id');
                $score           = empty($typeCapaciteIds)
                    ? 0
                    : $capacitesPoste->intersect($typeCapaciteIds)->count();
                $manquantes      = collect($typeCapaciteIds)->diff($capacitesPoste);

                return [
                    'poste'      => $poste,
                    'score'      => $score,
                    'total'      => count($typeCapaciteIds),
                    'manquantes' => $manquantes, // IDs des capacités non couvertes
                ];
            })
            ->filter(fn ($item) => empty($typeCapaciteIds) || $item['score'] > 0)
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
        array $typeCapaciteIds = []
    ): bool {
        return $this->postesDisponibles($salleId, $debut, $fin, $typeCapaciteIds)->isEmpty();
    }
}
