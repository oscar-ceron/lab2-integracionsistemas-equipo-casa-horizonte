@props(['reservations', 'showUser' => false, 'actions' => true])
@php
    $states = [
        'confirmed' => ['Confirmada', 'bg-emerald-500'],
        'pending' => ['Pendiente', 'bg-gold-400'],
        'cancelled' => ['Cancelada', 'bg-gray-400'],
    ];
@endphp
@if ($reservations->isEmpty())
    <div class="px-4 py-14 text-center text-gray-500">
        <p class="display text-2xl text-gray-700">Aún no hay reservas.</p>
        <p class="mt-1 text-sm">Cuando reserves una habitación aparecerá aquí.</p>
    </div>
@else
    <div class="divide-y divide-gray-200">
        @foreach ($reservations as $r)
            @php
                $in = \Carbon\Carbon::parse($r->check_in)->locale('es');
                $out = \Carbon\Carbon::parse($r->check_out)->locale('es');
                [$label, $dot] = $states[$r->status] ?? [$r->status, 'bg-gray-400'];
            @endphp
            <div class="row flex flex-wrap items-center gap-x-6 gap-y-2 px-4 py-4">
                <span class="num w-10 text-sm text-gray-400">#{{ $r->id }}</span>
                <div class="min-w-40 flex-1">
                    <p class="font-medium text-gray-900">Hab. {{ $r->room->room_number }} <span class="font-normal text-gray-500">· {{ $r->room->type }}</span></p>
                    <p class="text-sm text-gray-500">
                        @if ($showUser){{ $r->user->name }} · @endif
                        {{ $in->isoFormat('D MMM') }} → {{ $out->isoFormat('D MMM YYYY') }} · {{ $in->diffInDays($out) }} {{ $in->diffInDays($out) == 1 ? 'noche' : 'noches' }}
                    </p>
                </div>
                <span class="inline-flex items-center gap-2 text-sm text-gray-700"><span class="size-2 rounded-full {{ $dot }}"></span>{{ $label }}</span>
                <span class="num w-24 text-right font-semibold {{ $r->status === 'cancelled' ? 'text-gray-400 line-through' : 'text-gray-900' }}">${{ number_format($r->total_price, 2) }}</span>
                @if ($actions && $r->status !== 'cancelled')
                    <div class="flex items-center gap-1">
                        <a href="{{ route('reservations.pdf', $r) }}" class="btn btn-quiet !p-2.5" title="Descargar PDF" aria-label="Descargar PDF"><x-icon name="download" /></a>
                        <form method="POST" action="{{ route('reservations.cancel', $r) }}" data-confirm="Esta acción liberará la habitación." data-confirm-title="¿Cancelar la reserva?" data-confirm-button="Sí, cancelar">
                            @csrf @method('PATCH')
                            <button class="btn btn-quiet !p-2.5 hover:!text-red-700" title="Cancelar" aria-label="Cancelar reserva"><x-icon name="x" /></button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
