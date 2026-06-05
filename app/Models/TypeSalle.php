<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;

class TypeSalle extends Model
{
    use HasTablePrefix;

    const MODE_SIMULATEUR = 'simulateur';
    const MODE_CLASSIQUE  = 'classique';

    protected $fillable = [
        'libelle_court',
        'libelle_long',
        'ordre',
        'mode',
        'icone',
        'couleur',
    ];

    protected $casts = [
        'mode' => 'string',
    ];

    public function salles(): HasMany
    {
        return $this->hasMany(Salle::class, 'type_salle_id');
    }

    public function isSimulateur(): bool
    {
        return $this->mode === self::MODE_SIMULATEUR;
    }

    public function isClassique(): bool
    {
        return $this->mode === self::MODE_CLASSIQUE;
    }

    public function scopeSimulateur($query)
    {
        return $query->where('mode', self::MODE_SIMULATEUR);
    }

    public function scopeClassique($query)
    {
        return $query->where('mode', self::MODE_CLASSIQUE);
    }
}


