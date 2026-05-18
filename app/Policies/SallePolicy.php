<?php

namespace Modules\Resa\Policies;

use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\Salle;

class SallePolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::salle';

    public function update(Authenticatable $user, $model): bool
    {
        if (parent::update($user, $model)) {
            return true;
        }

        if (!$user->can('resa::gestionnaire_salle')) {
            return false;
        }

        return $model instanceof Salle && $model->isGestionnaire($user);
    }
}
