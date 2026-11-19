<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\RoomResource;
use App\Models\Room;
class RoomApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $rooms = Room::with($this->visibleReservations($request))->get();

    return RoomResource::collection($rooms);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'room_number' => 'required|string|max:255|unique:rooms,room_number',
        'type' => 'required|string|max:255',
        'price_per_night' => 'required|numeric|min:0',
        'status' => 'sometimes|string|max:255',
    ]);

    $room = Room::create($validated);

    return (new RoomResource($room))
        ->response()
        ->setStatusCode(201);
}

    /**
     * Display the specified resource.
     */
   public function show(Request $request, string $id)
{
    $room = Room::with($this->visibleReservations($request))->findOrFail($id);

    return new RoomResource($room);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $room = Room::findOrFail($id);

    $validated = $request->validate([
        'room_number' => 'sometimes|required|string|max:255|unique:rooms,room_number,' . $room->id,
        'type' => 'sometimes|required|string|max:255',
        'price_per_night' => 'sometimes|required|numeric|min:0',
        'status' => 'sometimes|required|string|max:255',
    ]);

    $room->update($validated);

    return new RoomResource($room);
}

    /**
     * Remove the specified resource from storage.
     */
  public function destroy(string $id)
{
    $room = Room::findOrFail($id);

    $room->delete();

    return response()->noContent();
}

    /**
     * Reservas que se cargan junto a la habitación: el administrador ve todas;
     * un usuario normal solo las suyas (nunca las de otros huéspedes).
     */
    private function visibleReservations(Request $request): array
    {
        $user = $request->user();

        return ['reservations' => function ($query) use ($user) {
            if (! $user->is_admin) {
                $query->where('user_id', $user->id);
            }
        }];
    }
}
