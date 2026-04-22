<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Modules\Resa\Traits\HasTablePrefix;
use Modules\RH\Models\User;

class Reservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'user_id',
        'salle_poste_id',
        'libelle_court',
        'libelle_long',
        'debut',
        'fin',
        'couleur',
        'validation',
        'capacites_utilisees',
        'validated_by',
        'validated_at',
        'data',
    ];

    protected $casts = [
        'debut'               => 'datetime',
        'fin'                 => 'datetime',
        'validated_at'        => 'datetime',
        'validation'          => 'integer',
        'capacites_utilisees' => 'array',
        'data'                => 'array',
    ];

    /* ── Constantes ───────────────────────────────────── */

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    /* ── Relations ────────────────────────────────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sallePoste(): BelongsTo
    {
        return $this->belongsTo(SallePoste::class, 'salle_poste_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /* ── Scopes ───────────────────────────────────────── */

    public function scopeValide($query)
    {
        return $query->where('validation', self::VALIDE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('validation', self::EN_ATTENTE);
    }

    /* ── Méthodes métier ──────────────────────────────── */

    public function isValide(): bool
    {
        return $this->validation === self::VALIDE;
    }

    public function isEnAttente(): bool
    {
        return $this->validation === self::EN_ATTENTE;
    }

    public function valider($admin): void
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

    /* ── Guava Calendar — Eventable ───────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        $backgroundColor = $this->isValide() ? '#22c55e' : '#9ca3af';

        return CalendarEvent::make($this)
            ->title($this->libelle_court ?? '')
            ->start($this->debut?->format('Y-m-d H:i:s'))
            ->end($this->fin?->format('Y-m-d H:i:s'))
            ->resourceId($this->salle_poste_id)
            ->backgroundColor($backgroundColor)
            ->extendedProp('validation', $this->validation)
            ->extendedProp('description', $this->libelle_long)
            ->extendedProp('capacites', $this->capacites_utilisees ?? [])
            ->editable(! $this->isValide() && auth()->id() === $this->user_id)
            ;
    }
}
