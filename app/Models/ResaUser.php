<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResaUser extends \App\Models\User
{
    protected $table = 'users';

    public function sallesGerees(): BelongsToMany
    {
        return $this->belongsToMany(
            Salle::class,
            'resa_salle_gestionnaires',
            'user_id',
            'salle_id'
        );
    }
}
