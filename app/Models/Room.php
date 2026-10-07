<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Reservation;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'type',
        'price_per_night',
        'status',
        'capacity',
        'description',
    ];

    /**
     * RelaciÃ³n uno a muchos: Una habitaciÃ³n tiene muchas reservaciones.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}

