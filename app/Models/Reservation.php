<?php
namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Modules\Resa\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\RH\Models\Marin as RHMarin;


class Reservation extends Model implements Eventable
{

    use HasTablePrefix;

    protected $fillable = [
        'libelle_court', 
        'salle_id', 
        'debut', 
        'fin', 
        'libelle_long', 
        'couleur', 
        'validation',
        'rh_marin_id', 
        'data', 
        'display_name',
        'email',
        'nid'
    ];

    protected $casts = [
        'debut' => 'datetime',
        'fin' => 'datetime',
        'data' => 'array',
    ];

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }

    public function rhMarin(): BelongsTo
    {
        return $this->belongsTo(RHMarin::class, 'rh_marin_id');
    }

    // Implémentation de Eventable
    public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->title($this->libelle_court)
            ->start($this->debut)
            ->end($this->fin)
            ->resourceId($this->salle_id) // Lien avec la ressource (salle)
            ->backgroundColor($this->couleur ?? '#3b82f6')
            ->extendedProp('description', $this->libelle_long)
            //->extendedProp('marin', $this->rhMarin?->nom)
            ->action('view'); // Action au clic (view, edit, etc.)
    }
}
