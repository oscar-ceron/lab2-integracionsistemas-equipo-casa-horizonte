<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomApiController;
use App\Http\Controllers\Api\ReservationApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('rooms', RoomApiController::class);

Route::apiResource('reservations', ReservationApiController::class);