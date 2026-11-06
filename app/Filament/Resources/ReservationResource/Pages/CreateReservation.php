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
        // Résoudre participants → user_id + data.users
        $participants = collect($data['participants'] ?? [])
            ->pluck('user_id')->filter()->unique()->values()->toArray();

        if (! empty($participants)) {
            $data['user_id'] = $participants[0];
            $data['data']    = array_merge($data['data'] ?? [], ['users' => $participants]);
        } elseif (empty($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }
        unset($data['participants']);

        $data['created_by'] = auth()->id();

        $user = auth()->user();

        $valideDirectement = $user?->can('resa::admin');

        // Gestionnaire : valide directement si la salle lui est assignée (directement,
        // ou via la formation pour les salles simulateur — cf. Salle::isGestionnaire()).
        if (
            !$valideDirectement
            && ($user?->can('resa::gestionnaire_salle') || $user?->can('resa::gestionnaire_formation'))
            && isset($data['salle_poste_id'])
        ) {
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
