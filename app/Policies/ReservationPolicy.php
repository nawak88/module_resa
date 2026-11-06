<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;

class ReservationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::reservation';

    public function valider(Authenticatable $user, Reservation $reservation): bool
    {
        if ($user->can('resa::admin')) {
            return true;
        }

        if ($user->can('resa::gestionnaire_salle')) {
            $salle = $reservation->sallePoste?->salle;
            if ($salle && $salle->isGestionnaire($user)) {
                return true;
            }
        }

        if ($user->can('resa::gestionnaire_formation') && !empty($reservation->formations_utilisees)) {
            return Formation::whereIn('id', $reservation->formations_utilisees)
                ->whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))
                ->exists();
        }

        return false;
    }
}
