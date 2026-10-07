<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        return view('rooms.index', ['rooms' => Room::orderBy('room_number')->get()]);
    }

    public function store(Request $request)
    {
        Room::create($this->validated($request));

        return redirect()->route('admin', ['tab' => 'habitaciones'])->with('success', 'Habitación creada.');
    }

    public function update(Request $request, Room $room)
    {
        $room->update($this->validated($request, $room));

        return redirect()->route('admin', ['tab' => 'habitaciones'])->with('success', 'Habitación actualizada.');
    }

    public function destroy(Room $room)
    {
        if ($room->reservations()->where('status', '!=', 'cancelled')->exists()) {
            return redirect()->route('admin', ['tab' => 'habitaciones'])
                ->with('error', 'No se puede eliminar: tiene reservas activas.');
        }

        $room->delete();

        return redirect()->route('admin', ['tab' => 'habitaciones'])->with('success', 'Habitación eliminada.');
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'room_number' => ['required', 'string', 'max:50', 'unique:rooms,room_number'.($room ? ','.$room->id : '')],
            'type' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,unavailable'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
