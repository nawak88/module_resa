<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class PosteFormation extends Model
{
    use HasTablePrefix;

    public $timestamps = false;

    protected $fillable = [
        'salle_poste_id',
        'formation_id',
    ];

    public function sallePoste(): BelongsTo
    {
        return $this->belongsTo(SallePoste::class, 'salle_poste_id');
    }

    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class, 'formation_id');
    }
}


