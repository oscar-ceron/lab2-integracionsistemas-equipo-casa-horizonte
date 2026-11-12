<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    /**
     * Mostrar las reservaciones del usuario autenticado.
     * Los administradores pueden consultar todas.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $reservations = $user->is_admin
            ? Reservation::with(['room', 'user'])->get()
            : $user->reservations()->with(['room', 'user'])->get();

        return ReservationResource::collection($reservations);
    }

    /**
     * Crear una reservacion para el usuario autenticado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'total_price' => 'required|numeric|min:0|max:999999.99',
            'status' => 'sometimes|string|max:255',
        ]);

        $reservation = $request->user()
            ->reservations()
            ->create($validated);

        $reservation->load(['room', 'user']);

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Mostrar una reservacion propia o cualquiera si es administrador.
     */
    public function show(Request $request, string $id)
    {
        $reservation = $this->findReservation($request, $id);

        $reservation->load(['room', 'user']);

        return new ReservationResource($reservation);
    }

    /**
     * Actualizar una reservacion propia o cualquiera si es administrador.
     */
    public function update(Request $request, string $id)
    {
        $reservation = $this->findReservation($request, $id);

        $validated = $request->validate([
            'room_id' => 'sometimes|required|integer|exists:rooms,id',
            'check_in' => 'sometimes|required|date',
            'check_out' => 'sometimes|required|date',
            'total_price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'status' => 'sometimes|required|string|max:255',
        ]);

        $checkIn = $validated['check_in'] ?? $reservation->check_in->toDateString();
        $checkOut = $validated['check_out'] ?? $reservation->check_out->toDateString();

        if ($checkOut <= $checkIn) {
            return response()->json([
                'message' => 'La fecha de salida debe ser posterior a la fecha de entrada.',
                'errors' => [
                    'check_out' => [
                        'La fecha de salida debe ser posterior a la fecha de entrada.'
                    ]
                ]
            ], 422);
        }

        $reservation->update($validated);
        $reservation->load(['room', 'user']);

        return new ReservationResource($reservation);
    }

    /**
     * Eliminar una reservacion propia o cualquiera si es administrador.
     */
    public function destroy(Request $request, string $id)
    {
        $reservation = $this->findReservation($request, $id);

        $reservation->delete();

        return response()->json([
            'message' => 'Reservacion eliminada correctamente.'
        ], 200);
    }

    /**
     * Buscar una reservacion respetando el aislamiento por usuario.
     */
    private function findReservation(Request $request, string $id): Reservation
    {
        $user = $request->user();

        $query = $user->is_admin
            ? Reservation::query()
            : $user->reservations();

        return $query->findOrFail($id);
    }
}
