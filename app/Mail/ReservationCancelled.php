<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ReservationCancelled extends Mailable
{
    public function __construct(public Reservation $reservation)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Reserva cancelada #'.$this->reservation->id.' - Casa Horizonte');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.cancelled');
    }
}