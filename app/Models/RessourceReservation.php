<?php

namespace Modules\Resa\Models;

use App\Models\User;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Resa\Traits\HasTablePrefix;

class RessourceReservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'user_id', 'ressource_id', 'libelle', 'debut', 'fin',
        'batiment', 'service', 'emprunteur_user_id', 'officier_responsable_user_id',
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

    public function emprunteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emprunteur_user_id');
    }

    public function officierResponsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officier_responsable_user_id');
    }

    /** Grade RH de l'emprunteur (via son dossier Marin), sinon '—'. */
    public function getEmprunteurGradeAttribute(): string
    {
        $marin = $this->emprunteur ? \Modules\RH\Models\Marin::fromUser($this->emprunteur) : null;

        return $marin?->grade?->libelle_court ?? '—';
    }

    /** Grade RH de l'officier responsable (via son dossier Marin), sinon '—'. */
    public function getOfficierResponsableGradeAttribute(): string
    {
        $marin = $this->officierResponsable ? \Modules\RH\Models\Marin::fromUser($this->officierResponsable) : null;

        return $marin?->grade?->libelle_court ?? '—';
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

    /* ── Guava Calendar ───────────────────────────────────────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        $isAdmin = auth()->user()?->can('resa::admin') ?? false;
        $nom     = $isAdmin ? ($this->user?->nom ?? '') : '';

        $titre = implode("\n", array_filter([
            $this->libelle ?: ($this->user?->nom ?? ''),
            $nom && $this->libelle ? '👤 ' . $nom : '',
            $this->debut?->format('H:i') . ' — ' . $this->fin?->format('H:i'),
        ]));

        return CalendarEvent::make($this)
            ->title($titre)
            ->start($this->debut?->format('Y-m-d H:i:s'))
            ->end($this->fin?->format('Y-m-d H:i:s'))
            ->resourceId($this->ressource_id)
            ->backgroundColor($this->couleur())
            ->editable(! $this->isValide() && auth()->id() === $this->user_id);
    }
}
