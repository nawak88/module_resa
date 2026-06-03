<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class RessourceReservation extends Model
{
    use HasTablePrefix;

    protected $fillable = [
        'user_id', 'ressource_id', 'libelle', 'debut', 'fin',
        'validation', 'validated_by', 'validated_at', 'data',
    ];

    protected $casts = [
        'debut'        => 'datetime',
        'fin'          => 'datetime',
        'validation'   => 'integer',
        'validated_at' => 'datetime',
        'data'         => 'array',
    ];

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ressource(): BelongsTo
    {
        return $this->belongsTo(Ressource::class, 'ressource_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isValide(): bool
    {
        return $this->validation === self::VALIDE;
    }

    public function isEnAttente(): bool
    {
        return $this->validation === self::EN_ATTENTE;
    }

    public function valider(User $admin): void
    {
        $this->update([
            'validation'   => self::VALIDE,
            'validated_by' => $admin->id,
            'validated_at' => now(),
        ]);
    }

    public function invalider(): void
    {
        $this->update([
            'validation'   => self::EN_ATTENTE,
            'validated_by' => null,
            'validated_at' => null,
        ]);
    }

    public function couleur(): string
    {
        return $this->isValide() ? '#22c55e' : '#f59e0b';
    }
}

