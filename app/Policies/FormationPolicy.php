<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Formation;

class FormationPolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::formation';

    /** Gestionnaire d'au moins une formation : accès à la liste (filtrée aux siennes). */
    public function viewAny(?Authenticatable $user): bool
    {
        if (parent::viewAny($user)) {
            return true;
        }

        return (bool) $user?->can('resa::gestionnaire_formation');
    }

    public function view(?Authenticatable $user, $model): bool
    {
        if (parent::view($user, $model)) {
            return true;
        }

        return $this->estGestionnaire($user, $model);
    }

    public function update(Authenticatable $user, $model): bool
    {
        if (parent::update($user, $model)) {
            return true;
        }

        return $this->estGestionnaire($user, $model);
    }

    private function estGestionnaire(?Authenticatable $user, $model): bool
    {
        if (! $user?->can('resa::gestionnaire_formation')) {
            return false;
        }

        return $model instanceof Formation && $model->isGestionnaire($user);
    }
}
