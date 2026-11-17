<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationApiController extends Controller
{
    /** Mostrar las reservaciones del usuario autenticado; el admin puede consultar todas. */
    public function index(Request $request)
    {
        $user = $request->user();

        $reservations = $user->is_admin
            ? Reservation::with(['room', 'user'])->get()
            : $user->reservations()->with(['room', 'user'])->get();

        return ReservationResource::collection($reservations);
    }

    /** Crear una reservación para el usuario autenticado. */
    public function store(StoreReservationRequest $request, ReservationService $reservations)
    {
        $reservation = $reservations->createForApi($request->user(), $request->validated());
        $reservation->load(['room', 'user']);

        return (new ReservationResource($reservation))
            ->response()
            ->setStatusCode(201);
    }

    /** Mostrar una reservación propia o cualquiera si es administrador. */
    public function show(Request $request, string $id)
    {
        $reservation = $this->findReservation($request, $id);
        $reservation->load(['room', 'user']);

        return new ReservationResource($reservation);
    }

    /** Actualizar fechas o habitación conservando el precio calculado por el servidor. */
    public function update(StoreReservationRequest $request, string $id, ReservationService $reservations)
    {
        $reservation = $this->findReservation($request, $id);
        $reservation = $reservations->update($reservation, $request->validated());
        $reservation->load(['room', 'user']);

        return new ReservationResource($reservation);
    }

    /** Eliminar una reservación propia o cualquiera si es administrador. */
    public function destroy(Request $request, string $id)
    {
        $reservation = $this->findReservation($request, $id);
        $reservation->delete();

        return response()->noContent();
    }

    /** Buscar una reservación respetando el aislamiento por usuario. */
    private function findReservation(Request $request, string $id): Reservation
    {
        $user = $request->user();
        $query = $user->is_admin ? Reservation::query() : $user->reservations();

        return $query->findOrFail($id);
    }
}
