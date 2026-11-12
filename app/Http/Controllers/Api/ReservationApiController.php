<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
class ReservationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $reservations = Reservation::with(['room', 'user'])->get();

    return ReservationResource::collection($reservations);
}

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|integer|exists:users,id',
        'room_id' => 'required|integer|exists:rooms,id',
        'check_in' => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'total_price' => 'required|numeric|min:0|max:999999.99',
        'status' => 'sometimes|string|max:255',
    ]);

    $reservation = Reservation::create($validated);

    $reservation->load(['room', 'user']);

    return (new ReservationResource($reservation))
        ->response()
        ->setStatusCode(201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
{
    $reservation = Reservation::with(['room', 'user'])->findOrFail($id);

    return new ReservationResource($reservation);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $reservation = Reservation::findOrFail($id);

    $validated = $request->validate([
        'user_id' => 'sometimes|required|integer|exists:users,id',
        'room_id' => 'sometimes|required|integer|exists:rooms,id',
        'check_in' => 'sometimes|required|date',
        'check_out' => 'sometimes|required|date',
        'total_price' => 'sometimes|required|numeric|min:0|max:999999.99',
        'status' => 'sometimes|required|string|max:255',
    ]);

    $checkIn = $validated['check_in'] ?? (string) $reservation->check_in;
    $checkOut = $validated['check_out'] ?? (string) $reservation->check_out;

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
     * Remove the specified resource from storage.
     */
   public function destroy(string $id)
{
    $reservation = Reservation::findOrFail($id);

    $reservation->delete();

    return response()->json([
        'message' => 'Reservacion eliminada correctamente.'
    ], 200);
}
}
