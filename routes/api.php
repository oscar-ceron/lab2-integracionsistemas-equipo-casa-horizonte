<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomApiController;
use App\Http\Controllers\Api\ReservationApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
| Autenticacion mediante Laravel Sanctum.
| Enviar el token en la cabecera:
| Authorization: Bearer <token>
*/

// Rutas publicas de autenticacion
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {

    // Autenticacion
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Habitaciones: cualquier usuario autenticado puede consultar; solo el administrador escribe (E-04).
    Route::apiResource('rooms', RoomApiController::class)->only(['index', 'show']);
    Route::apiResource('rooms', RoomApiController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('admin');

    // Reservaciones
    Route::apiResource('reservations', ReservationApiController::class);
});
