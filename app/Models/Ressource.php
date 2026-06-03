<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\RH\Models\Unite;
use Modules\Resa\Traits\HasTablePrefix;

class Ressource extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'ressource_categorie_id', 'unite_id', 'libelle', 'libelle_court', 'description', 'ordre', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(RessourceCategorie::class, 'ressource_categorie_id');
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(RessourceReservation::class, 'ressource_id');
    }

    public function getNomAttribute(): string
    {
        return $this->libelle_court ?? $this->libelle;
    }
}
