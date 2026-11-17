<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReservationService
{
    /** Create a web reservation, optionally on behalf of a guest for an admin. */
    public function createForWeb(User $actor, array $data): Reservation
    {
        $guest = $actor;

        if ($actor->is_admin) {
            if (! empty($data['guest_email'])) {
                $guest = User::firstOrCreate(
                    ['email' => mb_strtolower($data['guest_email'])],
                    ['name' => $data['guest_name'], 'password' => Str::random(24)]
                );
            } elseif (! empty($data['user_id'])) {
                $guest = User::findOrFail($data['user_id']);
            }
        }

        return $this->create($guest, $data);
    }

    /** Create an API reservation for the authenticated user. */
    public function createForApi(User $user, array $data): Reservation
    {
        return $this->create($user, $data);
    }

    /** Update the room or dates while recalculating price on the server. */
    public function update(Reservation $reservation, array $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data): Reservation {
            $room = Room::query()->lockForUpdate()->findOrFail($data['room_id'] ?? $reservation->room_id);
            $checkIn = Carbon::parse($data['check_in'] ?? $reservation->check_in)->toDateString();
            $checkOut = Carbon::parse($data['check_out'] ?? $reservation->check_out)->toDateString();

            $this->assertDateRange($checkIn, $checkOut);

            $this->assertRoomCanBeReserved($room, $checkIn, $checkOut, $reservation->id);

            $reservation->update([
                'room_id' => $room->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $this->priceFor($room, $checkIn, $checkOut),
            ]);

            return $reservation->refresh();
        });
    }

    private function create(User $guest, array $data): Reservation
    {
        return DB::transaction(function () use ($guest, $data): Reservation {
            $room = Room::query()->lockForUpdate()->findOrFail($data['room_id']);
            $checkIn = Carbon::parse($data['check_in'])->toDateString();
            $checkOut = Carbon::parse($data['check_out'])->toDateString();
            $this->assertDateRange($checkIn, $checkOut);
            $this->assertRoomCanBeReserved($room, $checkIn, $checkOut);

            return Reservation::create([
                'user_id' => $guest->id,
                'room_id' => $room->id,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_price' => $this->priceFor($room, $checkIn, $checkOut),
                'status' => 'confirmed',
            ]);
        });
    }

    private function assertRoomCanBeReserved(Room $room, string $checkIn, string $checkOut, ?int $exceptId = null): void
    {
        if ($room->status !== 'available') {
            throw ValidationException::withMessages(['room_id' => 'La habitación no está disponible.']);
        }

        $conflict = $room->reservations()
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['room_id' => 'La habitación ya está reservada en esas fechas.']);
        }
    }

    private function assertDateRange(string $checkIn, string $checkOut): void
    {
        if (Carbon::parse($checkOut)->lessThanOrEqualTo(Carbon::parse($checkIn))) {
            throw ValidationException::withMessages(['check_out' => 'La fecha de salida debe ser posterior a la fecha de entrada.']);
        }
    }

    private function priceFor(Room $room, string $checkIn, string $checkOut): float
    {
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        return round($nights * (float) $room->price_per_night, 2);
    }
}
