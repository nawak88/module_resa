<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Modules\Resa\Traits\HasTablePrefix;

class SallePoste extends Model implements Resourceable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'salle_id',
        'type_poste_id',
        'libelle_court',
        'libelle_long',
        'ordre',
        'system',
    ];

    protected $casts = [
        'system' => 'boolean',
    ];

    /* ── Relations ────────────────────────────────────────────────────────── */

    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class, 'salle_id');
    }

    public function typePoste(): BelongsTo
    {
        return $this->belongsTo(TypePoste::class, 'type_poste_id');
    }

    public function typeCapacites(): BelongsToMany
    {
        return $this->belongsToMany(
            TypeCapacite::class,
            (new PosteCapacite)->getTable(),
            'salle_poste_id',
            'type_capacite_id'
        )->orderBy('ordre');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'salle_poste_id');
    }

    /* ── Methodes ─────────────────────────────────────────────────────────── */

    public function isSystem(): bool
    {
        return (bool) $this->system;
    }

    /* ── Disponibilite ────────────────────────────────────────────────────── */

    public function isDisponible(\DateTime $debut, \DateTime $fin, ?int $excludeId = null): bool
    {
        return ! $this->reservations()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($query) use ($debut, $fin) {
                $query->where(fn ($q) => $q->where('debut', '<', $fin)->where('fin', '>', $debut));
            })
            ->exists();
    }

    public function scoreCapacites(array $typeCapaciteIds): int
    {
        if (empty($typeCapaciteIds)) return 0;

        return $this->typeCapacites()
            ->whereIn('id', $typeCapaciteIds)
            ->count();
    }

    /* ── Guava Calendar — Resourceable ───────────────────────────────────── */

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->libelle_court ?? '');
    }
}
