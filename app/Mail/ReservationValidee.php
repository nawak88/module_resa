<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Formation;
use Modules\Resa\Models\Reservation;

/**
 * En file d'attente : l'envoi (SMTP) ne doit jamais bloquer ni faire planter la requête
 * web qui valide la réservation (ex. serveur mail local indisponible en dev).
 */
class ReservationValidee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Validée',
        );
    }

    public function content(): Content
    {
        // Calculé ici plutôt qu'en @php inline dans le template : interaction étrange
        // observée entre @php/@endphp et @foreach dans ce projet (compilation Blade
        // Livewire) qui laissait la variable non définie dans la vue.
        //
        // Priorité à la sous-catégorie de la réservation (ex. "FPV" > "Entraînement")
        // quand il y en a une ; sinon la formation elle-même (cas sans sous-catégorie).
        $sousCategorie = $this->reservation->sousCategorie;

        if ($sousCategorie) {
            $consignes = $sousCategorie->validation_auto && $sousCategorie->notice
                ? collect([$sousCategorie->formation?->libelle_court . ' — ' . $sousCategorie->libelle_court => $sousCategorie->notice])
                : collect();
        } else {
            $consignes = Formation::whereIn('id', $this->reservation->formations_utilisees ?? [])
                ->where('validation_auto', true)
                ->whereNotNull('notice')
                ->pluck('notice', 'libelle_court');
        }

        return new Content(
            view: 'resa::mail.reservation-validee',
            with: ['consignes' => $consignes],
        );
    }
}
