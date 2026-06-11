<?php

namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Modules\Resa\Traits\HasTablePrefix;
use App\Models\User;
use Modules\Resa\Models\Formation;

class Reservation extends Model implements Eventable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid', 'user_id', 'salle_poste_id', 'libelle_court', 'libelle_long',
        'debut', 'fin', 'couleur', 'validation', 'formations_utilisees',
        'validated_by', 'validated_at', 'data',
    ];

    protected $casts = [
        'debut'               => 'datetime',
        'fin'                 => 'datetime',
        'validated_at'        => 'datetime',
        'validation'          => 'integer',
        'formations_utilisees' => 'array',
        'data'                => 'array',
    ];

    const EN_ATTENTE = 0;
    const VALIDE     = 1;

    /* ── Relations ────────────────────────────────────────────────────────── */

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

    /* ── Scopes ───────────────────────────────────────────────────────────── */

    public function scopeValide($query)
    {
        return $query->where('validation', self::VALIDE);
    }

    public function scopeEnAttente($query)
    {
        return $query->where('validation', self::EN_ATTENTE);
    }

    /* ── Methodes metier ──────────────────────────────────────────────────── */

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

    /* ── Guava Calendar — Eventable ───────────────────────────────────────── */

    public function toCalendarEvent(): CalendarEvent
    {
        // ── Couleur formation ────────────────────────────────────────────────
        $formationColor = null;
        if (! empty($this->formations_utilisees)) {
            $couleur = Formation::whereIn('id', $this->formations_utilisees)->value('couleur');
            $formationColor = match ($couleur) {
                'primary'   => '#6366f1',
                'secondary' => '#64748b',
                'success'   => '#22c55e',
                'danger'    => '#ef4444',
                'warning'   => '#f59e0b',
                'info'      => '#3b82f6',
                'gray'      => '#6b7280',
                default     => null,
            };
        }
        $backgroundColor = $this->isValide()
            ? ($formationColor ?? '#22c55e')
            : '#9ca3af';

        // ── Label formations ─────────────────────────────────────────────────
        $capacitesLabel = '';
        if (! empty($this->formations_utilisees)) {
            $capacitesLabel = Formation::whereIn('id', $this->formations_utilisees)
                ->orderBy('ordre')
                ->pluck('libelle_court')
                ->join(', ');
        }

        // ── Noms des utilisateurs (si connecté) ──────────────────────────────
        $isConnecte   = auth()->check();
        $isSimulateur = ! empty($this->formations_utilisees);

        $nomsUsers = [];
        if ($isConnecte) {
            // Tous les user IDs : data.users en priorité, sinon user_id seul
            $userIds = collect($this->data['users'] ?? [])->filter()->values();
            if ($userIds->isEmpty() && $this->user_id) {
                $userIds = collect([$this->user_id]);
            }
            if ($userIds->isNotEmpty()) {
                $nomsUsers = \App\Models\User::whereIn('id', $userIds)
                    ->get()
                    ->map(fn ($u) => trim("{$u->nom} {$u->prenom}"))
                    ->filter()
                    ->values()
                    ->toArray();
            }
        }

        // ── Titre affiché ────────────────────────────────────────────────────
        $labelTitre = $isSimulateur
            ? ($capacitesLabel ?: ($this->libelle_court ?? ''))
            : ($this->libelle_court ?? '');

        $lignes = array_filter([
            $labelTitre,
            ...array_map(fn ($n) => '👤 ' . $n, $nomsUsers),
        ]);

        return CalendarEvent::make($this)
            ->title(implode("\n", $lignes))
            ->start($this->debut?->format('Y-m-d H:i:s'))
            ->end($this->fin?->format('Y-m-d H:i:s'))
            ->resourceId($this->salle_poste_id)
            ->backgroundColor($backgroundColor)
            ->extendedProp('validation', $this->validation)
            ->extendedProp('description', $this->libelle_long)
            ->extendedProp('formations', $this->formations_utilisees ?? [])
            ->extendedProp('noms', $nomsUsers)
            ->extendedProp('capacites_label', $capacitesLabel)
            ->extendedProp('poste', $this->sallePoste?->libelle_court ?? '')
            ->extendedProp('heure_debut', $this->debut?->format('H:i') ?? '')
            ->extendedProp('heure_fin',   $this->fin?->format('H:i') ?? '')
            ->editable(! $this->isValide() && auth()->id() === $this->user_id);
    }
}


