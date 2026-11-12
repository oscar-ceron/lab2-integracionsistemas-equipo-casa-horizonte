<x-app-layout>
    @php
        $today = today();
        $next = $myReservations->filter(fn ($r) => $r->status !== 'cancelled' && \Carbon\Carbon::parse($r->check_out)->gte($today))
            ->sortBy('check_in')->first();
        $tabs = ['resumen' => 'Resumen', 'reservar' => 'Reservar', 'mis' => 'Mis reservas'];
        $prices = $bookable->pluck('price_per_night', 'id');
        $picked = old('room_id', request('room_id', ''));
        $first = explode(' ', auth()->user()->name)[0];
    @endphp

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8" x-data="dash('{{ array_key_exists($tab, $tabs) ? $tab : 'resumen' }}')">
        <div class="rise flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="display text-4xl text-indigo-900">Buenas, {{ $first }}.</h1>
                <p class="mt-1 text-gray-500">{{ $bookable->count() }} de {{ $rooms->count() }} habitaciones disponibles hoy.</p>
            </div>
            <button type="button" @click="set('reservar')" class="btn btn-primary">Reservar <x-icon name="arrow-right" /></button>
        </div>

        <div class="rise mt-8 overflow-x-auto" style="--i:1">
            <div class="relative inline-flex rounded-xl bg-gray-200/70 p-1">
                <span class="absolute left-0 top-1 h-[calc(100%-0.5rem)] rounded-lg bg-white shadow-sm"
                    :class="ready ? 'transition-[transform,width] duration-300 ease-snap' : ''"
                    :style="`width:${pill.w}px;transform:translateX(${pill.x}px)`"></span>
                @foreach ($tabs as $key => $label)
                    <button type="button" x-ref="tab_{{ $key }}" @click="set('{{ $key }}')"
                        :class="tab === '{{ $key }}' ? 'text-indigo-900' : 'text-gray-500 hover:text-gray-800'"
                        class="relative whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium transition-colors duration-150">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <div class="mt-8">
            {{-- Resumen --}}
            <section x-show="tab === 'resumen'" x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid gap-6 lg:grid-cols-12">
                    <div class="relative overflow-hidden rounded-3xl bg-indigo-900 p-8 text-gray-50 lg:col-span-7">
                        <svg class="pointer-events-none absolute -right-16 -top-16 size-72 text-gold-400/30" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width=".4" aria-hidden="true">
                            <circle cx="50" cy="50" r="12"/><circle cx="50" cy="50" r="24"/><circle cx="50" cy="50" r="36"/><circle cx="50" cy="50" r="48"/>
                        </svg>
                        @if ($next)
                            @php $ni = \Carbon\Carbon::parse($next->check_in)->locale('es'); $no = \Carbon\Carbon::parse($next->check_out)->locale('es'); @endphp
                            <p class="text-sm text-gray-300">Tu próxima estadía</p>
                            <p class="display mt-3 text-5xl">Habitación {{ $next->room->room_number }}</p>
                            <p class="mt-2 text-gray-300">{{ $next->room->type }} · {{ $ni->isoFormat('D MMM') }} → {{ $no->isoFormat('D MMM YYYY') }}</p>
                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="{{ route('reservations.pdf', $next) }}" class="btn btn-gold"><x-icon name="download" /> Comprobante PDF</a>
                            </div>
                        @else
                            <p class="display text-4xl">Aún no tienes estadías próximas.</p>
                            <p class="mt-2 text-gray-300">Elige una habitación y reserva en menos de un minuto.</p>
                            <button type="button" @click="set('reservar')" class="btn btn-gold mt-8">Reservar una habitación</button>
                        @endif
                    </div>

                    <dl class="grid content-start divide-y divide-gray-200 rounded-3xl border border-gray-200 bg-white px-6 lg:col-span-5">
                        @php
                            $figures = [
                                'Disponibles' => $bookable->count().' de '.$rooms->count(),
                                'Mis reservas' => $myReservations->where('status', '!=', 'cancelled')->count(),
                                'Total gastado' => '$'.number_format($mySpent, 2),
                            ];
                        @endphp
                        @foreach ($figures as $k => $v)
                            <div class="flex items-baseline justify-between py-4"><dt class="text-sm text-gray-500">{{ $k }}</dt><dd class="display num text-2xl text-indigo-900">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>

                <h2 class="display mt-12 text-2xl text-indigo-900">Habitaciones</h2>
                <div class="mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
                    @foreach ($rooms as $room) @include('partials.room-row', ['room' => $room]) @endforeach
                </div>
            </section>

            {{-- Reservar --}}
            <section x-show="tab === 'reservar'" x-cloak x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($bookable->isEmpty())
                    <p class="rounded-2xl border border-gray-200 bg-white px-6 py-14 text-center text-gray-500">No hay habitaciones disponibles por ahora.</p>
                @else
                <div class="grid gap-8 lg:grid-cols-12" x-data="booking({{ Js::from($prices) }}, '{{ $picked }}')">
                    <form id="booking" method="POST" action="{{ route('reservations.store') }}" class="space-y-8 lg:col-span-8">
                        @csrf
                        <fieldset>
                            <legend class="display text-2xl text-indigo-900">1. Elige tu habitación</legend>
                            <div class="mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
                                @foreach ($bookable as $room)
                                    <label class="row group flex cursor-pointer items-center gap-4 px-4 py-4">
                                        <input type="radio" name="room_id" value="{{ $room->id }}" x-model="room" class="peer sr-only">
                                        <span class="flex size-5 shrink-0 items-center justify-center rounded-full border border-gray-300 transition-colors duration-150 peer-checked:border-indigo-800 peer-checked:bg-indigo-800 peer-focus-visible:ring-4 peer-focus-visible:ring-indigo-600/20"><span class="size-1.5 rounded-full bg-white"></span></span>
                                        <span class="display num w-14 text-2xl text-indigo-800">{{ $room->room_number }}</span>
                                        <span class="flex-1"><span class="block font-medium text-gray-900">{{ $room->type }}</span><span class="block text-sm text-gray-500">{{ $room->capacity }} personas</span></span>
                                        <span class="num font-semibold text-gray-900">${{ number_format($room->price_per_night, 2) }}<span class="text-xs font-normal text-gray-500"> /noche</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="display text-2xl text-indigo-900">2. Fechas</legend>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div><x-input-label for="check_in" value="Entrada" /><input id="check_in" type="date" name="check_in" x-model="a" :min="new Date().toISOString().slice(0,10)" value="{{ old('check_in') }}" class="field mt-1" required></div>
                                <div><x-input-label for="check_out" value="Salida" /><input id="check_out" type="date" name="check_out" x-model="b" :min="a" :max="a ? new Date(new Date(a).getTime() + 30*864e5).toISOString().slice(0,10) : null" value="{{ old('check_out') }}" class="field mt-1" required></div>
                            </div>
                        </fieldset>
                    </form>

                    <aside class="lg:col-span-4">
                        <div class="sticky top-24 rounded-2xl border border-gray-200 bg-white p-6">
                            <h3 class="display text-xl text-indigo-900">Resumen</h3>
                            <dl class="mt-4 space-y-3 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Tarifa</dt><dd class="num" x-text="rate ? money(rate) : '—'"></dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Noches</dt><dd class="num" x-text="nights || '—'"></dd></div>
                                <div class="flex items-baseline justify-between border-t border-gray-200 pt-3"><dt class="font-medium">Total</dt><dd class="display num text-3xl text-indigo-900" x-text="money(total)"></dd></div>
                            </dl>
                            <button type="submit" form="booking" :disabled="!room || nights < 1" class="btn btn-primary mt-6 w-full disabled:cursor-not-allowed disabled:opacity-40">Confirmar reserva</button>
                            <p class="mt-3 text-xs text-gray-500">Recibirás el comprobante con QR por correo.</p>
                        </div>
                    </aside>
                </div>
                @endif
            </section>

            {{-- Mis reservas --}}
            <section x-show="tab === 'mis'" x-cloak x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <p class="mb-3 flex items-start gap-2 text-sm text-gray-600"><x-icon name="clock" class="mt-0.5 size-4 text-gold-600" /> Puedes cancelar gratis hasta {{ \App\Models\Reservation::FREE_CANCEL_HOURS }} horas antes de la entrada (14:00). Pasado ese plazo, contacta con el hotel.</p>
                <div class="rounded-2xl border border-gray-200 bg-white">@include('partials.reservations-list', ['reservations' => $myReservations])</div>
            </section>

        </div>
    </div>
</x-app-layout>
