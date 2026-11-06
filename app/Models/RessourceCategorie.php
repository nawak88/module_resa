<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;

class RessourceCategorie extends Model
{
    use HasTablePrefix;

    protected $fillable = ['libelle', 'libelle_court', 'couleur', 'icone', 'ordre'];

    public function ressources(): HasMany
    {
        return $this->hasMany(Ressource::class, 'ressource_categorie_id')->orderBy('ordre');
    }

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_ressource_categorie_gestionnaires', 'ressource_categorie_id', 'user_id');
    }

    public function isGestionnaire(User $user): bool
    {
        return $this->gestionnaires()->where('user_id', $user->id)->exists();
    }

    public function ressourcesActives(): HasMany
    {
        return $this->hasMany(Ressource::class, 'ressource_categorie_id')
            ->where('actif', true)
            ->orderBy('ordre');
    }

    public function getRessourcesActivesForUserAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        $user    = auth()->user();
        $isAdmin = $user?->can('resa::admin') ?? false;
        $uniteId = $user?->unite_id;

        // Admin → toutes les ressources actives
        if ($isAdmin || ! $uniteId) {
            return $this->ressourcesActives()->get();
        }

        // Utilisateur avec unité → sa propre unité + ressources sans unité
        return $this->ressourcesActives()
            ->where(function ($q) use ($uniteId) {
                $q->whereNull('unite_id')->orWhere('unite_id', $uniteId);
            })
            ->get();
    }
}
