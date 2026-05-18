<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Resa\Traits\HasTablePrefix;
use Modules\Resa\Models\SallePoste;


class TypePoste extends Model
{
    use HasTablePrefix;

   
    protected $fillable = [
        'libelle_court',
        'libelle_long',
        'ordre',
        'system',
    ];

    protected $casts = [
        'system' => 'boolean',
    ];

    public function sallePostes(): HasMany
    {
        return $this->hasMany(SallePoste::class, 'type_poste_id');
    }

    public function isSystem(): bool
    {
        return (bool) $this->system;
    }
}
