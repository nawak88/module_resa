<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Reservation;

/**
 * En file d'attente : l'envoi (SMTP) ne doit jamais bloquer ni faire planter la requête
 * web qui supprime la réservation.
 */
class ReservationSupprimee extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Annulée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'resa::mail.reservation-supprimee',
        );
    }
}
