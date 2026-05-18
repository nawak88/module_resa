<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Reservation;

class ReservationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::reservation';

    public function valider(Authenticatable $user, Reservation $reservation): bool
    {
        if ($user->can('resa::admin')) {
            return true;
        }

        if (!$user->can('resa::gestionnaire_salle')) {
            return false;
        }

        $salle = $reservation->sallePoste?->salle;

        return $salle && $salle->isGestionnaire($user);
    }
}
