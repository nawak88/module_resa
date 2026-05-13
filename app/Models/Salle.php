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

    public function isGestionnaire(User $user): bool
    {
        return $this->gestionnaires()->where('user_id', $user->id)->exists();
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


