<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelReservationRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmation;
use App\Models\Reservation;
use App\Services\ReservationDocument;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function create()
    {
        return redirect()->route('dashboard', ['tab' => 'reservar', 'room_id' => request('room_id')]);
    }

    public function store(StoreReservationRequest $request, ReservationService $reservations)
    {
        $reservation = $reservations->createForWeb($request->user(), $request->validated());
        $guest = $reservation->user;

        // La reserva se conserva aunque el correo falle.
        try {
            Mail::to($guest->email)->send(new ReservationConfirmation($reservation));
            $note = ' Te enviamos la confirmación por correo.';
        } catch (\Throwable $e) {
            report($e);
            $note = ' No se pudo enviar el correo, pero puedes descargar el comprobante.';
        }

        return redirect()->route($request->user()->is_admin ? 'admin' : 'dashboard', ['tab' => $request->user()->is_admin ? 'historial' : 'mis'])
            ->with('success', 'Reserva #'.$reservation->id.' creada.'.$note);
    }

    public const CANCEL_REASONS = [
        'Cambio de planes',
        'Encontré otra opción',
        'Error en las fechas',
        'Motivo personal o de salud',
        'Otro',
    ];

    public function cancel(CancelReservationRequest $request, Reservation $reservation)
    {
        $reservation = $this->ownedReservation($request, $reservation);
        $user = $request->user();
        $data = $request->validated();

        if ($reservation->isCancelled()) {
            return back()->with('error', 'La reserva #'.$reservation->id.' ya estaba cancelada.');
        }

        if (! $reservation->canBeCancelledBy($user)) {
            $msg = $user->is_admin
                ? 'No se puede cancelar una estancia que ya terminó.'
                : 'El plazo de cancelación terminó el '.$reservation->cancelDeadline()->locale('es')->isoFormat('D MMM [a las] HH:mm').'. Contacta con el hotel.';

            return back()->with('error', $msg);
        }

        $reservation->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $user->id,
            'cancel_reason' => $data['reason'] ?? null,
        ]);

        $note = '';
        try {
            Mail::to($reservation->user->email)->send(new ReservationCancelled($reservation));
            $note = ' Enviamos el aviso por correo.';
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Reserva #'.$reservation->id.' cancelada; la habitación quedó libre.'.$note);
    }

    public function pdf(Request $request, Reservation $reservation)
    {
        $reservation = $this->ownedReservation($request, $reservation);

        return response(app(ReservationDocument::class)->pdf($reservation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="reserva-'.$reservation->id.'.pdf"',
        ]);
    }

    public function verify(Request $request, int $id)
    {
        if (! hash_equals(ReservationDocument::token($id), (string) $request->query('token'))) {
            abort(403, 'Token inválido.');
        }

        return view('reservations.verify', ['reservation' => Reservation::with(['room', 'user'])->findOrFail($id)]);
    }

    // Un huésped solo alcanza sus propias reservas; el administrador puede acceder a todas.
    private function ownedReservation(Request $request, Reservation $reservation): Reservation
    {
        $user = $request->user();

        return $user->is_admin ? $reservation : $user->reservations()->findOrFail($reservation->id);
    }
}
