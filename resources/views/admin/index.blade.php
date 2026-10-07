<x-app-layout>
    @php
        $tabs = ['resumen' => 'Resumen', 'reservar' => 'Reservar', 'habitaciones' => 'Habitaciones', 'historial' => 'Historial'];
    @endphp

    <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8" x-data="dash('{{ array_key_exists($tab, $tabs) ? $tab : 'resumen' }}')">
        <div class="rise">
            <h1 class="display text-4xl text-indigo-900">Administración</h1>
            <p class="mt-1 text-gray-500">Gestiona habitaciones y consulta todas las reservas del hotel.</p>
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
            <section x-show="tab === 'resumen'" x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <dl class="grid divide-y divide-gray-200 rounded-3xl border border-gray-200 bg-white px-6 sm:grid-cols-2 sm:gap-x-12">
                    @foreach ([
                        'Habitaciones' => $rooms->count(),
                        'Capacidad total' => $totalCapacity.' personas',
                        'Tarifa promedio' => '$'.number_format($avgPrice, 2),
                        'Reservas activas' => $reservations->where('status', '!=', 'cancelled')->count(),
                        'Ingresos' => '$'.number_format($revenue, 2),
                        'Usuarios registrados' => $users,
                    ] as $k => $v)
                        <div class="flex items-baseline justify-between py-4"><dt class="text-sm text-gray-500">{{ $k }}</dt><dd class="display num text-2xl text-indigo-900">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </section>

            <section x-show="tab === 'reservar'" x-cloak x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                @if ($bookable->isEmpty())
                    <p class="rounded-2xl border border-gray-200 bg-white px-6 py-14 text-center text-gray-500">No hay habitaciones disponibles.</p>
                @else
                <div class="grid gap-8 lg:grid-cols-12" x-data="booking({{ Js::from($bookable->pluck('price_per_night', 'id')) }}, '{{ old('room_id', '') }}')">
                    <form id="booking" method="POST" action="{{ route('reservations.store') }}" class="space-y-8 lg:col-span-8">
                        @csrf
                        <fieldset x-data="{ who: '{{ old('guest_email') ? 'new' : 'existing' }}' }">
                            <legend class="display text-2xl text-indigo-900">1. Huésped</legend>
                            <div class="mt-4 inline-flex rounded-xl bg-gray-200/70 p-1 text-sm font-medium">
                                <button type="button" @click="who = 'existing'" :class="who === 'existing' ? 'bg-white text-indigo-900 shadow-sm' : 'text-gray-500'" class="rounded-lg px-4 py-2 transition-colors duration-150">Usuario registrado</button>
                                <button type="button" @click="who = 'new'" :class="who === 'new' ? 'bg-white text-indigo-900 shadow-sm' : 'text-gray-500'" class="rounded-lg px-4 py-2 transition-colors duration-150">Otro correo</button>
                            </div>
                            <div x-show="who === 'existing'" class="mt-4">
                                <select name="user_id" :disabled="who !== 'existing'" class="field">
                                    @foreach ($guests as $g)
                                        <option value="{{ $g->id }}" @selected(old('user_id', auth()->id()) == $g->id)>{{ $g->name }} · {{ $g->email }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div x-show="who === 'new'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div><x-input-label for="guest_name" value="Nombre completo" /><input id="guest_name" name="guest_name" :disabled="who !== 'new'" value="{{ old('guest_name') }}" minlength="3" maxlength="100" autocomplete="off" class="field mt-1" required></div>
                                <div><x-input-label for="guest_email" value="Correo del huésped" /><input id="guest_email" type="email" name="guest_email" :disabled="who !== 'new'" value="{{ old('guest_email') }}" maxlength="255" autocomplete="off" class="field mt-1" placeholder="correo@ejemplo.com" required></div>
                                <p class="text-xs text-gray-500 sm:col-span-2">Si el correo no tiene cuenta, se crea una y el comprobante se envía a esa dirección.</p>
                            </div>
                        </fieldset>
                        <fieldset>
                            <legend class="display text-2xl text-indigo-900">2. Habitación</legend>
                            <div class="mt-4 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
                                @foreach ($bookable as $room)
                                    <label class="row flex cursor-pointer items-center gap-4 px-4 py-4">
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
                            <legend class="display text-2xl text-indigo-900">3. Fechas</legend>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div><x-input-label for="check_in" value="Entrada" /><input id="check_in" type="date" name="check_in" x-model="a" :min="new Date().toISOString().slice(0,10)" class="field mt-1" required></div>
                                <div><x-input-label for="check_out" value="Salida" /><input id="check_out" type="date" name="check_out" x-model="b" :min="a" :max="a ? new Date(new Date(a).getTime() + 30*864e5).toISOString().slice(0,10) : null" class="field mt-1" required></div>
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
                            <p class="mt-3 text-xs text-gray-500">Se enviará el comprobante al correo del huésped.</p>
                        </div>
                    </aside>
                </div>
                @endif
            </section>
            {{-- Admin: habitaciones --}}
            <section x-show="tab === 'habitaciones'" x-cloak x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid gap-8 lg:grid-cols-12">
                    <div class="divide-y divide-gray-200 self-start rounded-2xl border border-gray-200 bg-white lg:col-span-7">
                        @foreach ($rooms as $room)
                            <div class="row flex items-center gap-4 px-4 py-4">
                                <span class="display num w-14 text-2xl text-indigo-800">{{ $room->room_number }}</span>
                                <div class="flex-1"><p class="font-medium">{{ $room->type }}</p><p class="text-sm text-gray-500">{{ $room->capacity }} pers. · ${{ number_format($room->price_per_night, 2) }} · {{ $room->status === 'available' ? 'Disponible' : 'No disponible' }}</p></div>
                                <a href="{{ route('admin', ['tab' => 'habitaciones', 'edit' => $room->id]) }}" class="btn btn-quiet !p-2.5" aria-label="Editar"><x-icon name="pencil" /></a>
                                <form method="POST" action="{{ route('rooms.destroy', $room) }}" data-confirm="No se podrá deshacer." data-confirm-title="¿Eliminar la habitación?" data-confirm-button="Sí, eliminar">@csrf @method('DELETE')
                                    <button class="btn btn-quiet !p-2.5 hover:!text-red-700" aria-label="Eliminar"><x-icon name="trash" /></button></form>
                            </div>
                        @endforeach
                    </div>

                    @php $e = $editRoom; @endphp
                    <form method="POST" action="{{ $e ? route('rooms.update', $e) : route('rooms.store') }}" class="space-y-4 rounded-2xl border border-gray-200 bg-white p-6 lg:col-span-5">
                        @csrf @if ($e) @method('PUT') @endif
                        <h3 class="display text-xl text-indigo-900">{{ $e ? 'Editar habitación '.$e->room_number : 'Nueva habitación' }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><x-input-label value="Número" /><input name="room_number" value="{{ old('room_number', $e?->room_number) }}" class="field mt-1" required></div>
                            <div><x-input-label value="Tipo" /><input name="type" value="{{ old('type', $e?->type) }}" class="field mt-1" required></div>
                            <div><x-input-label value="Capacidad" /><input type="number" min="1" name="capacity" value="{{ old('capacity', $e?->capacity ?? 2) }}" class="field mt-1" required></div>
                            <div><x-input-label value="Precio / noche" /><input type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night', $e?->price_per_night) }}" class="field mt-1" required></div>
                        </div>
                        <div><x-input-label value="Estado" />
                            <select name="status" class="field mt-1">
                                <option value="available" @selected(old('status', $e?->status) !== 'unavailable')>Disponible</option>
                                <option value="unavailable" @selected(old('status', $e?->status) === 'unavailable')>No disponible</option>
                            </select></div>
                        <div><x-input-label value="Descripción" /><textarea name="description" rows="3" class="field mt-1">{{ old('description', $e?->description) }}</textarea></div>
                        <div class="flex gap-2">
                            <button class="btn btn-primary"><x-icon name="{{ $e ? 'check' : 'plus' }}" /> {{ $e ? 'Guardar cambios' : 'Crear habitación' }}</button>
                            @if ($e)<a href="{{ route('admin', ['tab' => 'habitaciones']) }}" class="btn btn-outline">Cancelar</a>@endif
                        </div>
                    </form>
                </div>
            </section>

            {{-- Admin: historial --}}
            <section x-show="tab === 'historial'" x-cloak x-transition:enter="transition duration-300 ease-snap" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="rounded-2xl border border-gray-200 bg-white">@include('partials.reservations-list', ['reservations' => $reservations, 'showUser' => true, 'actions' => true])</div>
            </section>
        </div>
    </div>
</x-app-layout>
