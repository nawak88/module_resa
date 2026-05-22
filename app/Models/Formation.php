<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Resa\Traits\HasTablePrefix;

class Formation extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'libelle_court',
        'libelle_long',
        'ordre',
        'nb_users_par_poste',
        'email',
        'couleur',
    ];

    protected $casts = [
        'nb_users_par_poste' => 'integer',
    ];

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_formation_gestionnaires', 'formation_id', 'user_id');
    }

    public function sallePostes(): BelongsToMany
    {
        return $this->belongsToMany(
            SallePoste::class,
            (new PosteFormation)->getTable(),
            'formation_id',
            'salle_poste_id'
        );
    }
}


