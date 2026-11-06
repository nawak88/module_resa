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
 * web qui crée/valide la réservation (ex. serveur mail local indisponible en dev).
 */
class ReservationCree extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        // Préfixe "[Resa #id]" stable (même sur l'email de validation et les rappels) :
        // permet de créer une règle de classement Outlook par réservation.
        return new Envelope(
            subject: $this->reservation->objetMail() . ' — Demande envoyée',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'resa::mail.reservation-cree',
        );
    }
}
