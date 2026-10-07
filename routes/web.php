<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SiteController;
use App\Models\Review;
use App\Models\Room;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'featured' => Room::where('status', 'available')->orderBy('room_number')->take(3)->get(),
        'reviews' => Review::with('user')->latest()->take(6)->get(),
        'avg' => Review::avg('rating'),
        'reviewCount' => Review::count(),
    ]);
})->name('home');

// Verificación pública del QR del comprobante (firmado con HMAC).
Route::get('/verify/{id}', [ReservationController::class, 'verify'])->name('reservations.verify');

Route::post('/boletin', [SiteController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter');
Route::post('/contacto', [SiteController::class, 'contact'])->middleware('throttle:3,1')->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'createAdmin'])->name('admin.login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'storeAdmin']);
});

Route::get('/admin', AdminController::class)->middleware(['auth', 'admin'])->name('admin');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    Route::get('/reservations/{reservation}/pdf', [ReservationController::class, 'pdf'])->name('reservations.pdf');

    Route::middleware('admin')->group(function () {
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    });
});

require __DIR__.'/auth.php';
