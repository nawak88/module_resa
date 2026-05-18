<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Resa\Traits\HasTablePrefix;

class TypeCapacite extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'libelle_court',
        'libelle_long',
        'ordre',
    ];

    public function sallePostes(): BelongsToMany
    {
        return $this->belongsToMany(
            SallePoste::class,
            (new PosteCapacite)->getTable(),
            'type_capacite_id',
            'salle_poste_id'
        );
    }
}

