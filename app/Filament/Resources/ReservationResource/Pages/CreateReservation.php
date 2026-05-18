<?php

namespace Modules\Resa\Filament\Resources\ReservationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Resa\Filament\Resources\ReservationResource;
use Modules\Resa\Models\Reservation;

class CreateReservation extends CreateRecord
{
    protected static string $resource = ReservationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $valideDirectement = $user?->can('resa::admin');

        // Gestionnaire : valide directement si la salle lui est assignée
        if (!$valideDirectement && $user?->can('resa::gestionnaire_salle') && isset($data['salle_poste_id'])) {
            $salle = \Modules\Resa\Models\SallePoste::find($data['salle_poste_id'])?->salle;
            $valideDirectement = $salle && $salle->isGestionnaire($user);
        }

        if ($valideDirectement) {
            $data['validation']   = Reservation::VALIDE;
            $data['validated_by'] = auth()->id();
            $data['validated_at'] = now();
        } else {
            $data['validation'] = Reservation::EN_ATTENTE;
        }

        return $data;
    }
}
