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
                @if ($actions)
                    @php $canCancel = auth()->check() && $r->canBeCancelledBy(auth()->user()); @endphp
                    <div class="flex items-center gap-1">
                        @unless ($r->isCancelled())
                            <a href="{{ route('reservations.pdf', $r) }}" class="btn btn-quiet !p-2.5" title="Descargar PDF" aria-label="Descargar PDF"><x-icon name="download" /></a>
                            @if ($canCancel)
                                <form method="POST" action="{{ route('reservations.cancel', $r) }}" data-confirm="La habitación se liberará y te enviaremos un aviso por correo." data-confirm-title="¿Cancelar la reserva #{{ $r->id }}?" data-confirm-button="Sí, cancelar" data-reasons='@json(\App\Http\Controllers\ReservationController::CANCEL_REASONS)'>
                                    @csrf @method('PATCH')
                                    <button class="btn btn-quiet !p-2.5 hover:!text-red-700" title="Cancelar reserva" aria-label="Cancelar reserva"><x-icon name="x" /></button>
                                </form>
                            @else
                                <span class="btn btn-quiet pointer-events-none !p-2.5 opacity-40" title="Fuera del plazo de cancelación ({{ \App\Models\Reservation::FREE_CANCEL_HOURS }} h antes de la entrada)" aria-label="Cancelación no disponible"><x-icon name="x" /></span>
                            @endif
                        @endunless
                    </div>
                @endif
                @if ($r->isCancelled() && $r->cancelled_at)
                    <p class="basis-full pl-16 text-xs text-gray-500">
                        Cancelada el {{ $r->cancelled_at->locale('es')->isoFormat('D MMM YYYY, HH:mm') }}
                        @if ($r->canceller && $r->cancelled_by !== $r->user_id) por el hotel @endif
                        @if ($r->cancel_reason) · Motivo: {{ $r->cancel_reason }} @endif
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif
