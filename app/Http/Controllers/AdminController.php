<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __invoke(Request $request)
    {
        $rooms = Room::orderBy('room_number')->get();

        return view('admin.index', [
            'tab' => $request->query('tab', 'resumen'),
            'rooms' => $rooms,
            'editRoom' => $request->query('edit') ? Room::find($request->query('edit')) : null,
            'reservations' => Reservation::with(['room', 'user'])->latest()->get(),
            'avgPrice' => $rooms->avg('price_per_night') ?? 0,
            'totalCapacity' => $rooms->sum('capacity'),
            'revenue' => Reservation::where('status', '!=', 'cancelled')->sum('total_price'),
            'users' => User::count(),
            'guests' => User::orderBy('name')->get(),
            'bookable' => $rooms->where('status', 'available'),
        ]);
    }
}