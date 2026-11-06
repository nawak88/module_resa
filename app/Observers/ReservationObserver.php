<?php

namespace Modules\Resa\Observers;

use Illuminate\Support\Facades\Mail;
use Modules\Resa\Mail\ReservationCree;
use Modules\Resa\Mail\ReservationSupprimee;
use Modules\Resa\Mail\ReservationValidee;
use Modules\Resa\Models\Reservation;

class ReservationObserver
{
    public function created(Reservation $reservation): void
    {
        foreach ($reservation->destinataires() as $email) {
            Mail::to($email)->send(new ReservationCree($reservation));
        }

        // Réservation née déjà validée (admin, ou formation à validation automatique
        // type "FPV Entraînement") : l'email "updated" ne se déclenchera jamais pour
        // elle (pas de transition de statut), donc on envoie l'email de validation ici.
        // C'est aussi systématiquement le cas d'auto-validation (aucun gestionnaire n'a
        // eu à agir) : on ne les notifie pas, pour éviter de les spammer.
        if ($reservation->isValide()) {
            $this->envoyerEmailValidation($reservation, inclureGestionnaires: false);
        }
    }

    public function updated(Reservation $reservation): void
    {
        if (! $reservation->wasChanged('validation')) {
            return;
        }

        if (! $reservation->isValide()) {
            return;
        }

        // Ici, par contre, un gestionnaire vient d'agir manuellement (toggleValidation) :
        // les destinataires habituels (dont gestionnaires) sont notifiés normalement.
        $this->envoyerEmailValidation($reservation, inclureGestionnaires: true);
    }

    public function deleted(Reservation $reservation): void
    {
        foreach ($reservation->destinataires() as $email) {
            Mail::to($email)->send(new ReservationSupprimee($reservation));
        }
    }

    private function envoyerEmailValidation(Reservation $reservation, bool $inclureGestionnaires): void
    {
        foreach ($reservation->destinataires($inclureGestionnaires) as $email) {
            Mail::to($email)->send(new ReservationValidee($reservation));
        }
    }
}
