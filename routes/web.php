
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('rooms', RoomController::class);
Route::resource('reservations', ReservationController::class);