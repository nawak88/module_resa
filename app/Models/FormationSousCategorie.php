<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class FormationSousCategorie extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'formation_id',
        'libelle_court',
        'validation_auto',
        'notice',
        'ordre',
    ];

    protected $casts = [
        'validation_auto' => 'boolean',
        'ordre'           => 'integer',
    ];

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
    }
}
