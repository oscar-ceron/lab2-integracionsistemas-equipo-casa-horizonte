<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room_number' => $this->room_number,
            'type' => $this->type,
            'price_per_night' => $this->price_per_night,
            'status' => $this->status,

            'reservations' => ReservationResource::collection(
                $this->whenLoaded('reservations')
            ),
        ];
    }
}
