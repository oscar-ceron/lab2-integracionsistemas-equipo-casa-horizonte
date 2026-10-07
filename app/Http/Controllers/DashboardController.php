<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->route('admin');
        }

        $mine = Reservation::where('user_id', $user->id);
        $rooms = Room::orderBy('room_number')->get();

        return view('dashboard', [
            'tab' => $request->query('tab', 'resumen'),
            'rooms' => $rooms,
            'bookable' => $rooms->where('status', 'available'),
            'myReservations' => (clone $mine)->with('room')->latest()->get(),
            'mySpent' => (clone $mine)->where('status', '!=', 'cancelled')->sum('total_price'),
        ]);
    }
}