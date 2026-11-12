@props(['room', 'cta' => true])
@php $free = $room->status === 'available'; @endphp
<div class="row grid grid-cols-[4.5rem_1fr_auto] items-center gap-x-4 gap-y-1 px-4 py-4 sm:grid-cols-[5rem_1fr_auto_auto] sm:gap-x-6">
    <span class="display num text-3xl text-indigo-800">{{ $room->room_number }}</span>
    <div class="min-w-0">
        <p class="font-medium text-gray-900">{{ $room->type }}</p>
        <p class="truncate text-sm text-gray-500">{{ $room->description ?: 'Sin descripción' }}</p>
        <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500"><x-icon name="users" class="size-3.5" /> {{ $room->capacity }} {{ $room->capacity == 1 ? 'persona' : 'personas' }}
            @unless ($free)<span class="ml-2 text-gray-400">· No disponible</span>@endunless</p>
    </div>
    <p class="text-right"><span class="num font-semibold text-gray-900">${{ number_format($room->price_per_night, 2) }}</span><span class="block text-xs text-gray-500">por noche</span></p>
    @if ($cta && $free)
        <a href="{{ route('dashboard', ['tab' => 'reservar', 'room_id' => $room->id]) }}" class="btn btn-outline col-span-3 sm:col-span-1">Reservar</a>
    @endif
</div>
