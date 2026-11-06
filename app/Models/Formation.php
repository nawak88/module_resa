<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'validation_auto',
        'notice',
    ];

    protected $casts = [
        'nb_users_par_poste' => 'integer',
        'validation_auto'    => 'boolean',
    ];

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_formation_gestionnaires', 'formation_id', 'user_id');
    }

    public function isGestionnaire(User $user): bool
    {
        return $this->gestionnaires()->where('user_id', $user->id)->exists();
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

    public function sousCategories(): HasMany
    {
        return $this->hasMany(FormationSousCategorie::class)->orderBy('ordre');
    }

    /**
     * Règle de validation effective pour une sous-catégorie donnée (ou la formation
     * elle-même si aucune n'est choisie / qu'il n'y en a pas) — 0 ou 1 sous-catégorie
     * laisse la formation se comporter "comme avant", avec ses propres champs.
     */
    public function validationAuto(?FormationSousCategorie $sousCategorie = null): bool
    {
        return (bool) ($sousCategorie?->validation_auto ?? $this->validation_auto);
    }

    public function notice(?FormationSousCategorie $sousCategorie = null): ?string
    {
        return $sousCategorie?->notice ?? $this->notice;
    }
}
