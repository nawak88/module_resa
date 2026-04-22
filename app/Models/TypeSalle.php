<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;
use Modules\Resa\Models\Salle;

class TypeSalle extends Model
{
    use HasTablePrefix;


    protected $fillable = [
        'libelle_court',
        'libelle_long',
        'ordre',
    ];

    public function salles(): HasMany
    {
        return $this->hasMany(Salle::class, 'type_salle_id');
    }
}
