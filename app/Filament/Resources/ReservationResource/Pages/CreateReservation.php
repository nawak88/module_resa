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
        // Creation par admin = directement validee
        if (auth()->user()?->can('resa::validation_salle')) {
            $data['validation']   = Reservation::VALIDE;
            $data['validated_by'] = auth()->id();
            $data['validated_at'] = now();
        } else {
            $data['validation'] = Reservation::EN_ATTENTE;
        }

        return $data;
    }
}
