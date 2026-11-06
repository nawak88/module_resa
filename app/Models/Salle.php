<?php
namespace Modules\Resa\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Modules\RH\Models\Unite;
use Modules\Resa\Traits\HasTablePrefix;

class Salle extends Model implements Resourceable
{
    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'code',
        'libelle_court',
        'libelle_long',
        'type_salle_id',
        'unite_id',
        'email',
        'capacite',
        'image',
        'color',
        'ordre',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function typeSalle(): BelongsTo
    {
        return $this->belongsTo(TypeSalle::class, 'type_salle_id');
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'unite_id');
    }

    public function gestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resa_salle_gestionnaires', 'salle_id', 'user_id');
    }

    /**
     * Gestionnaire de la salle elle-même, OU — pour les salles simulateur, où la
     * gestion se fait par formation plutôt que par salle (cf. SalleResource : le champ
     * « Gestionnaires » n'est affiché que pour les salles classiques) — gestionnaire
     * d'au moins une formation disponible sur un poste de cette salle.
     */
    public function isGestionnaire(User $user): bool
    {
        if ($this->gestionnaires()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($this->typeSalle?->mode !== TypeSalle::MODE_SIMULATEUR) {
            return false;
        }

        return $this->sallePostes()
            ->whereHas('formations.gestionnaires', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }

    /**
     * IDs des salles gérées par cet utilisateur (salle directe, ou formation pour les
     * salles simulateur). Voir isGestionnaire() pour le détail des deux cas.
     */
    public static function idsGereesPar(?User $user): \Illuminate\Support\Collection
    {
        if (! $user) {
            return collect();
        }

        $directes = static::whereHas('gestionnaires', fn ($q) => $q->where('user_id', $user->id))->pluck('id');

        $viaFormation = static::whereHas(
            'sallePostes.formations.gestionnaires',
            fn ($q) => $q->where('user_id', $user->id),
        )->pluck('id');

        return $directes->merge($viaFormation)->unique()->values();
    }

    public function sallePostes(): HasMany
    {
        return $this->hasMany(SallePoste::class, 'salle_id')->orderBy('ordre');
    }

    public function typePostes(): BelongsToMany
    {
        return $this->belongsToMany(
            TypePoste::class,
            (new SallePoste)->getTable(),
            'salle_id',
            'type_poste_id'
        )->withPivot('libelle_court', 'libelle_long', 'ordre')->withTimestamps();
    }

    // Guava Calendar — Resourceable
    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->libelle_court ?? '');
    }
}
