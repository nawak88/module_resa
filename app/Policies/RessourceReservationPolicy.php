<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\RessourceReservation;

class RessourceReservationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::ressource_reservation';

    public function valider(Authenticatable $user, RessourceReservation $reservation): bool
    {
        if ($user->can('resa::admin')) {
            return true;
        }

        if ($user->can('resa::gestionnaire_ressource')) {
            $categorie = $reservation->ressource?->categorie;
            if ($categorie && $categorie->isGestionnaire($user)) {
                return true;
            }
        }

        return false;
    }
}
