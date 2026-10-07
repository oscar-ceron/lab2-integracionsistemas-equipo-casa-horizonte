<x-guest-layout>
    @php $ok = $reservation->status !== 'cancelled'; @endphp
    <div class="-mt-4">
        <span class="flex size-12 items-center justify-center rounded-full {{ $ok ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }}">
            <x-icon :name="$ok ? 'check' : 'x'" class="size-6" />
        </span>
        <h1 class="display mt-4 text-3xl text-indigo-900">{{ $ok ? 'Reserva verificada' : 'Reserva cancelada' }}</h1>
        <p class="mt-1 text-gray-500">Comprobante #{{ $reservation->id }} emitido por Casa Horizonte.</p>

        <dl class="mt-6 divide-y divide-gray-200 text-sm">
            @foreach ([
                'Huésped' => $reservation->user->name,
                'Habitación' => $reservation->room->room_number.' · '.$reservation->room->type,
                'Entrada' => \Carbon\Carbon::parse($reservation->check_in)->locale('es')->isoFormat('D MMM YYYY'),
                'Salida' => \Carbon\Carbon::parse($reservation->check_out)->locale('es')->isoFormat('D MMM YYYY'),
                'Total' => '$'.number_format($reservation->total_price, 2),
            ] as $k => $v)
                <div class="flex justify-between py-3"><dt class="text-gray-500">{{ $k }}</dt><dd class="font-medium text-gray-900">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</x-guest-layout>
