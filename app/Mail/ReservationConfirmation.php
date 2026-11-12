<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Services\ReservationDocument;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReservationConfirmation extends Mailable
{
    public function __construct(public Reservation $reservation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reserva confirmada - Casa Horizonte');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reservation');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => app(ReservationDocument::class)->pdf($this->reservation),
                'reserva-'.$this->reservation->id.'.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
