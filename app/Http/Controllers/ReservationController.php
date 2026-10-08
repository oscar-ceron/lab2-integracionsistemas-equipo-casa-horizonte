<?php

namespace App\Http\Controllers;

use App\Mail\ReservationCancelled;
use App\Mail\ReservationConfirmation;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\ReservationDocument;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function create()
    {
        return redirect()->route('dashboard', ['tab' => 'reservar', 'room_id' => request('room_id')]);
    }

    public function store(Request $request)
    {
        $isAdmin = (bool) $request->user()->is_admin;

        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.today()->addYear()->toDateString()],
            'check_out' => ['required', 'date', 'after:check_in', 'before_or_equal:'.Carbon::parse($request->input('check_in') ?: today())->addDays(30)->toDateString()],
            'user_id' => [$isAdmin ? 'nullable' : 'prohibited', 'integer', 'exists:users,id'],
            'guest_name' => [$isAdmin ? 'nullable' : 'prohibited', 'required_with:guest_email', 'string', 'min:3', 'max:100', "regex:/^[\pL\s.'-]+$/u"],
            'guest_email' => [$isAdmin ? 'nullable' : 'prohibited', $isAdmin ? 'required_without:user_id' : 'nullable', 'email:rfc', 'max:255'],
        ], [
            'check_in.after_or_equal' => 'La entrada no puede ser anterior a hoy.',
            'check_in.before_or_equal' => 'Solo se puede reservar con hasta un año de anticipación.',
            'check_out.after' => 'La salida debe ser posterior a la entrada.',
            'check_out.before_or_equal' => 'La estadía máxima es de 30 noches.',
            'guest_name.required_with' => 'Indica el nombre del huésped.',
            'guest_name.regex' => 'El nombre solo puede contener letras y espacios.',
            'guest_email.required_without' => 'Elige un huésped o escribe su correo.',
            'guest_email.email' => 'Escribe un correo válido.',
        ]);

        // Solo un administrador reserva a nombre de otros; un correo nuevo crea la cuenta del huésped.
        if (! $isAdmin) {
            $guest = $request->user();
        } elseif (! empty($data['guest_email'])) {
            $guest = User::firstOrCreate(
                ['email' => mb_strtolower($data['guest_email'])],
                ['name' => $data['guest_name'], 'password' => \Illuminate\Support\Str::random(24)]
            );
        } else {
            $guest = User::findOrFail($data['user_id']);
        }
        $room = Room::findOrFail($data['room_id']);

        if ($room->status !== 'available') {
            throw ValidationException::withMessages(['room_id' => 'La habitación no está disponible.']);
        }

        $conflict = $room->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<', $data['check_out'])
            ->where('check_out', '>', $data['check_in'])
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['room_id' => 'La habitación ya está reservada en esas fechas.']);
        }

        $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));

        $reservation = Reservation::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'total_price' => $nights * $room->price_per_night,
            'status' => 'confirmed',
        ]);

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

    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation = $this->ownedReservation($request, $reservation);
        $user = $request->user();

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

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

    // Un huésped solo alcanza sus propias reservas ($user->reservations()); el admin, todas.
    private function ownedReservation(Request $request, Reservation $reservation): Reservation
    {
        $user = $request->user();

        return $user->is_admin ? $reservation : $user->reservations()->findOrFail($reservation->id);
    }
}
