<?php

namespace Modules\Resa\Observers;

use Illuminate\Support\Facades\Mail;
use Modules\Resa\Mail\ReservationCree;
use Modules\Resa\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        $salle = $reservation->sallePoste?->salle;

        if (!$salle || empty($salle->email)) {
            return;
        }

        $emails = array_filter(array_map('trim', explode(',', $salle->email)));

        foreach ($emails as $email) {
            Mail::to($email)->send(new ReservationCree($reservation));
        }
    }
}
