<?php
namespace Modules\Resa\Models;

use Illuminate\Database\Eloquent\Model;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\CalendarResource;
use Modules\Resa\Traits\HasTablePrefix;

class Salle extends Model implements Resourceable
{

    use HasTablePrefix;

    protected $fillable = [
        'uuid',
        'code',
        'libelle_court',
        'libelle_long',
        'image',
        'ordre',
        'color',
        'capacite',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function reservations()
    {
        return $this->hasMany(Calendrier::class);
    }

    // Implémentation de Resourceable
    public function toCalendarResource(): CalendarResource
    {
        return CalendarResource::make($this->id)
            ->title($this->libelle_court);
    }
}
