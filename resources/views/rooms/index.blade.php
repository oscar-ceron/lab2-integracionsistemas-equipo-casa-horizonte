<x-app-layout>
    <x-slot name="header"><h2>Habitaciones</h2></x-slot>

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8">
        <div class="divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
            @forelse ($rooms as $room)
                <div class="rise" style="--i:{{ $loop->index }}">@include('partials.room-row', ['room' => $room])</div>
            @empty
                <p class="px-4 py-14 text-center text-gray-500">No hay habitaciones registradas.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
