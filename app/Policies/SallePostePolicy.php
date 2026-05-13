<?php

namespace Modules\Resa\Policies;

use App\Models\User;
use App\Policies\GenericSkeletorPolicy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Resa\Models\SallePoste;

class SallePostePolicy extends GenericSkeletorPolicy
{
    protected $slug = 'resa::sallePoste';

    private function isGestionnaireOf(Authenticatable $user, $model): bool
    {
        if (!$user->can('resa::gestionnaire_salle')) {
            return false;
        }

        $salle = $model instanceof SallePoste ? $model->salle : null;

        return $salle && $salle->isGestionnaire($user);
    }

    public function create(Authenticatable $user): bool
    {
        return parent::create($user) || $user->can('resa::gestionnaire_salle');
    }

    public function update(Authenticatable $user, $model): bool
    {
        return parent::update($user, $model) || $this->isGestionnaireOf($user, $model);
    }

    public function delete(Authenticatable $user, $model): bool
    {
        return parent::delete($user, $model) || $this->isGestionnaireOf($user, $model);
    }
}
