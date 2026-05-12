<?php

namespace Modules\Resa\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Resa\Models\Reservation;

class ReservationCreee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouvelle réservation : ' . ($this->reservation->libelle_court ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'resa::mail.reservation-creee',
        );
    }
}
