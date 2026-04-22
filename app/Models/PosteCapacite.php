<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class PosteCapacite extends Model
{
    use HasTablePrefix;

    public $timestamps = false;

    protected $fillable = [
        'salle_poste_id',
        'type_capacite_id',
    ];

    public function sallePoste(): BelongsTo
    {
        return $this->belongsTo(SallePoste::class, 'salle_poste_id');
    }

    public function typeCapacite(): BelongsTo
    {
        return $this->belongsTo(TypeCapacite::class, 'type_capacite_id');
    }
}
