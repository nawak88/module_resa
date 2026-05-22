<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Modules\Resa\Traits\HasTablePrefix;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\PosteFormation;

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

    public function formations(): BelongsToMany
    {
        return $this->belongsToMany(
            Formation::class,
            (new PosteFormation)->getTable(),
            'salle_poste_id',
            'formation_id'
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

    public function scoreFormations(array $formationIds): int
    {
        if (empty($formationIds)) return 0;

        return $this->formations()
            ->whereIn('id', $formationIds)
            ->count();
    }

    /* ── Guava Calendar — Resourceable ───────────────────────────────────── */

    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->libelle_court ?? '');
    }
}


