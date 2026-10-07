<!DOCTYPE html>
<html lang="es">
    <head>
        @include('layouts.head', ['title' => 'Casa Horizonte · Reserva tu habitación'])
    </head>
    <body class="min-h-screen overflow-x-hidden">
        <div id="progress" class="fixed inset-x-0 top-0 z-50 h-0.5 bg-gold-500"></div>
        <header x-data="{ m: false }" class="sticky top-0 z-40 border-b border-gray-200/70 bg-gray-100/85 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-2 sm:px-8">
                <a href="/" aria-label="Casa Horizonte"><x-application-logo class="h-12 w-auto" /></a>
                <nav class="hidden items-center gap-7 text-sm font-medium text-gray-600 md:flex">
                    <a href="#habitaciones" data-spy class="transition-colors hover:text-indigo-900">Habitaciones</a>
                    <a href="#como-funciona" class="transition-colors hover:text-indigo-900">Cómo funciona</a>
                    <a href="#servicios" data-spy class="transition-colors hover:text-indigo-900">Servicios</a>
                    <a href="#galeria" data-spy class="transition-colors hover:text-indigo-900">Galería</a>
                    <a href="#ofertas" data-spy class="transition-colors hover:text-indigo-900">Ofertas</a>
                    <a href="#comentarios" data-spy class="transition-colors hover:text-indigo-900">Opiniones</a>
                    <a href="#cerca" data-spy class="transition-colors hover:text-indigo-900">Cerca</a>
                    <a href="#contacto" data-spy class="transition-colors hover:text-indigo-900">Contacto</a>
                </nav>
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin') : route('dashboard') }}" class="btn btn-primary">Ir al panel</a>
                    @else
                        <a href="{{ route('admin.login') }}" class="btn btn-quiet hidden sm:inline-flex">Administración</a>
                        <a href="{{ route('login') }}" class="btn btn-quiet">Entrar</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Crear cuenta</a>
                    @endauth
                </div>
            </div>
            <nav x-show="m" x-cloak x-transition.opacity.duration.200ms @click="m = false" class="grid gap-1 border-t border-gray-200 bg-gray-50 px-5 py-3 text-gray-700 md:hidden">
                @foreach (['habitaciones' => 'Habitaciones', 'servicios' => 'Servicios', 'galeria' => 'Galería', 'ofertas' => 'Ofertas', 'comentarios' => 'Opiniones', 'cerca' => 'Cerca', 'experiencias' => 'Experiencias', 'preguntas' => 'Preguntas', 'contacto' => 'Contacto'] as $id => $label)
                    <a href="#{{ $id }}" class="rounded-lg px-3 py-2.5 hover:bg-gray-100">{{ $label }}</a>
                @endforeach
            </nav>
        </header>

        <main>
            {{-- Hero --}}
            <section class="relative">
                <div class="hero-glow pointer-events-none absolute inset-0"></div>
                <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-5 py-14 sm:px-8 lg:grid-cols-12 lg:py-24">
                    <div class="lg:col-span-7">
                        <h1 class="display rise text-5xl leading-[1.04] text-indigo-900 sm:text-6xl lg:text-7xl" style="--i:0">Tu próxima pausa <span class="italic text-gold-600">comienza</span> aquí.</h1>
                        <p class="rise mt-6 max-w-lg text-lg text-gray-600" style="--i:2">Elige tu habitación, reserva en minutos y recibe tu comprobante con código QR directo en tu correo.</p>
                        <div class="rise mt-9 flex flex-wrap items-center gap-3" style="--i:3">
                            <a href="{{ auth()->check() ? route('dashboard', ['tab' => 'reservar']) : route('register') }}" class="btn btn-primary !px-6 !py-3.5 text-base">Reservar ahora <x-icon name="arrow-right" /></a>
                            <a href="#habitaciones" class="btn btn-outline !px-6 !py-3.5 text-base">Ver habitaciones</a>
                        </div>
                        <dl class="rise mt-12 flex flex-wrap gap-x-10 gap-y-4 border-t border-gray-200 pt-6" style="--i:4">
                            <div><dt class="text-xs text-gray-500">Desde</dt><dd class="display num text-2xl text-indigo-900">${{ number_format($featured->min('price_per_night') ?? 0, 0) }}<span class="text-sm font-normal text-gray-500"> / noche</span></dd></div>
                            <div><dt class="text-xs text-gray-500">Confirmación</dt><dd class="display text-2xl text-indigo-900">Inmediata</dd></div>
                            <div><dt class="text-xs text-gray-500">Comprobante</dt><dd class="display text-2xl text-indigo-900">PDF + QR</dd></div>
                        </dl>
                    </div>

                    <div class="rise relative lg:col-span-5" style="--i:3">
                        <div class="relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-indigo-900 shadow-[0_30px_60px_-30px_rgba(15,26,69,0.6)]">
                            <x-horizon-art class="absolute inset-0 h-full w-full" />
                        </div>
                        <div class="float absolute -bottom-5 -left-4 flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-[0_16px_40px_-20px_rgba(15,26,69,0.45)] sm:-left-8">
                            <span class="flex size-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-icon name="check" /></span>
                            <div><p class="text-sm font-medium text-gray-900">Reserva confirmada</p><p class="text-xs text-gray-500">Comprobante enviado a tu correo</p></div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Búsqueda rápida --}}
            <section class="relative z-10 mx-auto -mt-6 max-w-5xl px-5 sm:px-8" x-data="{ a: '', b: '', today: new Date().toISOString().slice(0,10), get n() { return this.a && this.b ? Math.max(0, Math.round((new Date(this.b) - new Date(this.a)) / 864e5)) : 0 } }">
                <form method="GET" action="{{ route('dashboard') }}" class="reveal grid items-end gap-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-[0_24px_50px_-30px_rgba(15,26,69,0.45)] sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto]">
                    <input type="hidden" name="tab" value="reservar">
                    <div><label for="q_in" class="text-xs font-medium text-gray-500">Entrada</label><input id="q_in" type="date" name="check_in" x-model="a" :min="today" required class="field mt-1"></div>
                    <div><label for="q_out" class="text-xs font-medium text-gray-500">Salida</label><input id="q_out" type="date" name="check_out" x-model="b" :min="a || today" :max="a ? new Date(new Date(a).getTime() + 30*864e5).toISOString().slice(0,10) : null" required class="field mt-1"></div>
                    <button class="btn btn-primary !py-3 sm:col-span-2 lg:col-span-1">Buscar disponibilidad <x-icon name="arrow-right" /></button>
                    <p x-show="n > 0" x-cloak x-transition class="text-sm text-gray-600 sm:col-span-2 lg:col-span-3"><span class="num font-medium text-indigo-900" x-text="n"></span> <span x-text="n === 1 ? 'noche' : 'noches'"></span> · desde <span class="num font-medium text-indigo-900" x-text="'$' + (n * {{ (float) ($featured->min('price_per_night') ?? 0) }}).toLocaleString('en-US', {minimumFractionDigits: 2})"></span> en total</p>
                </form>
            </section>
            {{-- Cifras --}}
            @php
                $stats = [
                    [\App\Models\Room::count(), '', 'Habitaciones'],
                    [\App\Models\Reservation::count(), '+', 'Reservas realizadas'],
                    [\App\Models\Review::count(), '', 'Opiniones de huéspedes'],
                    [24, ' h', 'Atención al huésped'],
                ];
            @endphp
            <section class="border-y border-gray-200 bg-white">
                <dl class="mx-auto grid max-w-6xl grid-cols-2 gap-y-8 px-5 py-10 sm:px-8 lg:grid-cols-4">
                    @foreach ($stats as $i => [$n, $suffix, $label])
                        <div class="reveal text-center" style="--i:{{ $i }}">
                            <dd class="display num text-4xl text-indigo-900 sm:text-5xl"><span data-count="{{ $n }}">{{ $n }}</span>{{ $suffix }}</dd>
                            <dt class="mt-1 text-sm text-gray-500">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </section>

            {{-- Habitaciones --}}
            <section id="habitaciones" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-16 sm:px-8">
                <div class="reveal flex flex-wrap items-end justify-between gap-4">
                    <h2 class="display text-4xl text-indigo-900">Habitaciones disponibles</h2>
                    <a href="{{ auth()->check() ? route('rooms.index') : route('login') }}" class="btn btn-quiet">Ver todas <x-icon name="arrow-right" /></a>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3" x-data="{ box: null }" @keydown.escape.window="box = null">
                    @forelse ($featured as $k => $room)
                        @php $album = collect(range(1, 3))->map(fn ($n) => asset('img/rooms/h'.((($k * 3 + $n - 1) % 9) + 1).'.jpg'))->all(); @endphp
                        <article class="reveal group overflow-hidden rounded-3xl border border-gray-200 bg-white" style="--i:{{ $k }}"
                                 x-data="{ i: 0, n: {{ count($album) }}, imgs: @js($album), go(d) { this.i = (this.i + d + this.n) % this.n } }">
                            <div class="relative aspect-[4/3] overflow-hidden bg-gray-100">
                                <div class="flex h-full transition-transform duration-500 ease-snap" :style="`transform: translateX(-${i * 100}%)`">
                                    <template x-for="(src, idx) in imgs" :key="idx">
                                        <img :src="src" :alt="'{{ $room->type }} — foto ' + (idx + 1)" loading="lazy" @click="box = { imgs, i }" class="h-full w-full shrink-0 cursor-zoom-in object-cover">
                                    </template>
                                </div>
                                <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-indigo-900 backdrop-blur">Hab. {{ $room->room_number }}</span>
                                <span class="absolute bottom-3 right-3 rounded-full bg-black/55 px-2.5 py-1 text-xs text-white backdrop-blur num" x-text="`${i + 1} / ${n}`"></span>
                                <button type="button" @click="go(-1)" aria-label="Foto anterior" class="absolute left-3 top-1/2 grid size-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 opacity-0 shadow transition duration-200 hover:bg-white active:scale-95 group-hover:opacity-100 max-sm:opacity-100"><x-icon name="arrow-right" class="size-4 rotate-180" /></button>
                                <button type="button" @click="go(1)" aria-label="Foto siguiente" class="absolute right-3 top-1/2 grid size-9 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 opacity-0 shadow transition duration-200 hover:bg-white active:scale-95 group-hover:opacity-100 max-sm:opacity-100"><x-icon name="arrow-right" class="size-4" /></button>
                            </div>
                            <div class="flex gap-2 px-4 pt-3">
                                <template x-for="(src, idx) in imgs" :key="'t' + idx">
                                    <button type="button" @click="i = idx" :aria-label="'Ver foto ' + (idx + 1)" class="h-12 flex-1 overflow-hidden rounded-lg border-2 transition duration-200" :class="i === idx ? 'border-gold-500' : 'border-transparent opacity-60 hover:opacity-100'">
                                        <img :src="src" alt="" loading="lazy" class="h-full w-full object-cover">
                                    </button>
                                </template>
                            </div>
                            <div class="flex items-start justify-between gap-3 px-4 pb-5 pt-3">
                                <div class="min-w-0">
                                    <h3 class="display text-xl text-indigo-900">{{ $room->type }}</h3>
                                    <p class="truncate text-sm text-gray-500">{{ $room->description ?: 'Descanso cómodo en el corazón de la ciudad' }}</p>
                                    <p class="mt-1 flex items-center gap-1 text-xs text-gray-500"><x-icon name="users" class="size-3.5" /> {{ $room->capacity }} {{ $room->capacity == 1 ? 'persona' : 'personas' }}</p>
                                </div>
                                <p class="shrink-0 text-right"><span class="num font-semibold text-gray-900">${{ number_format($room->price_per_night, 2) }}</span><span class="block text-xs text-gray-500">por noche</span></p>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full rounded-3xl border border-gray-200 bg-white px-4 py-12 text-center text-gray-500">Pronto tendremos nuevas habitaciones.</p>
                    @endforelse

                    <div x-show="box" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 grid place-items-center bg-black/85 p-4" @click.self="box = null">
                        <template x-if="box">
                            <div class="relative w-full max-w-4xl">
                                <img :src="box.imgs[box.i]" alt="" class="max-h-[80vh] w-full rounded-2xl object-contain">
                                <button type="button" @click="box.i = (box.i + box.imgs.length - 1) % box.imgs.length" aria-label="Anterior" class="absolute left-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 active:scale-95"><x-icon name="arrow-right" class="size-4 rotate-180" /></button>
                                <button type="button" @click="box.i = (box.i + 1) % box.imgs.length" aria-label="Siguiente" class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 active:scale-95"><x-icon name="arrow-right" class="size-4" /></button>
                                <button type="button" @click="box = null" aria-label="Cerrar" class="absolute -top-3 right-0 rounded-full bg-white/90 px-3 py-1 text-sm text-indigo-900 active:scale-95">Cerrar ✕</button>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            {{-- Servicios --}}
            <section id="servicios" class="mx-auto max-w-6xl scroll-mt-20 px-5 pb-20 pt-6 sm:px-8">
                <div class="reveal max-w-xl">
                    <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Servicios</p>
                    <h2 class="display mt-2 text-4xl text-indigo-900">Pensado para que solo te ocupes de descansar.</h2>
                </div>
                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['wifi', 'Wi‑Fi de alta velocidad', 'Conexión estable en todas las habitaciones y áreas comunes.'],
                        ['coffee', 'Desayuno incluido', 'Café, fruta y panadería fresca cada mañana.'],
                        ['car', 'Estacionamiento', 'Plazas seguras para huéspedes, sin costo adicional.'],
                        ['sparkles', 'Limpieza diaria', 'Habitaciones impecables y ropa de cama renovada.'],
                        ['clock', 'Recepción 24 horas', 'Check‑in flexible y atención a cualquier hora.'],
                        ['shield', 'Reserva segura', 'Comprobante con QR firmado y verificable.'],
                    ] as $i => [$icon, $t, $d])
                        <div class="reveal group rounded-3xl border border-gray-200 bg-white p-6 transition duration-300 ease-snap hover:-translate-y-1 hover:border-gold-400 hover:shadow-[0_20px_40px_-28px_rgba(15,26,69,0.5)]" style="--i:{{ $i % 3 }}">
                            <span class="flex size-11 items-center justify-center rounded-2xl bg-indigo-900 text-gold-300 transition duration-300 ease-snap group-hover:scale-110"><x-icon :name="$icon" class="size-5" /></span>
                            <h3 class="mt-5 text-lg font-medium text-gray-900">{{ $t }}</h3>
                            <p class="mt-1.5 text-sm text-gray-600">{{ $d }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Galería --}}
            <section id="galeria" class="scroll-mt-20 border-y border-gray-200 bg-gray-50">
                <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8" x-data="{ open: null, imgs: @js(array_map(fn ($n) => asset('img/rooms/h'.$n.'.jpg'), range(1, 9))) }" @keydown.escape.window="open = null" @keydown.arrow-right.window="open !== null && (open = (open + 1) % imgs.length)" @keydown.arrow-left.window="open !== null && (open = (open + imgs.length - 1) % imgs.length)">
                    <div class="reveal max-w-xl">
                        <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Galería</p>
                        <h2 class="display mt-2 text-4xl text-indigo-900">Un vistazo a tu próxima estancia.</h2>
                    </div>
                    <div class="mt-10 grid auto-rows-[10rem] grid-cols-2 gap-3 sm:auto-rows-[12rem] md:grid-cols-4">
                        @foreach ([[1, 'md:col-span-2 md:row-span-2'], [2, ''], [3, ''], [4, ''], [5, 'md:row-span-2'], [6, 'md:col-span-2'], [7, ''], [8, ''], [9, 'md:col-span-2']] as $i => [$n, $span])
                            <button type="button" @click="open = {{ $n - 1 }}" aria-label="Ampliar foto {{ $n }}" class="reveal group relative overflow-hidden rounded-2xl {{ $span }}" style="--i:{{ $i % 4 }}">
                                <img src="{{ asset('img/rooms/h'.$n.'.jpg') }}" alt="Habitación Casa Horizonte {{ $n }}" loading="lazy" class="h-full w-full object-cover transition duration-700 ease-snap group-hover:scale-105">
                                <span class="absolute inset-0 bg-gradient-to-t from-indigo-900/40 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                            </button>
                        @endforeach
                    </div>
                    <div x-show="open !== null" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-50 grid place-items-center bg-black/85 p-4" @click.self="open = null">
                        <div class="relative w-full max-w-4xl" x-show="open !== null">
                            <img :src="imgs[open]" alt="" class="max-h-[80vh] w-full rounded-2xl object-contain">
                            <button type="button" @click="open = (open + imgs.length - 1) % imgs.length" aria-label="Anterior" class="absolute left-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 active:scale-95"><x-icon name="arrow-right" class="size-4 rotate-180" /></button>
                            <button type="button" @click="open = (open + 1) % imgs.length" aria-label="Siguiente" class="absolute right-2 top-1/2 grid size-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-indigo-900 active:scale-95"><x-icon name="arrow-right" class="size-4" /></button>
                            <button type="button" @click="open = null" aria-label="Cerrar" class="absolute -top-3 right-0 rounded-full bg-white/90 px-3 py-1 text-sm text-indigo-900 active:scale-95">Cerrar ✕</button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Ofertas --}}
            <section id="ofertas" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-20 sm:px-8">
                <div class="reveal max-w-xl">
                    <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Ofertas</p>
                    <h2 class="display mt-2 text-4xl text-indigo-900">Paquetes para cada ocasión.</h2>
                </div>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ([
                        ['Escapada de fin de semana', 'Dos noches con desayuno incluido y salida tardía.', '10% de descuento', false],
                        ['Estancia larga', 'Más de 7 noches: tarifa preferente y limpieza extra.', '15% de descuento', true],
                        ['Viaje en pareja', 'Habitación con detalle de bienvenida y desayuno en cama.', 'Detalle incluido', false],
                    ] as $i => [$t, $d, $tag, $hot])
                        <div class="reveal relative flex flex-col rounded-3xl border p-7 transition duration-300 ease-snap hover:-translate-y-1 {{ $hot ? 'border-transparent bg-indigo-900 text-gray-50' : 'border-gray-200 bg-white' }}" style="--i:{{ $i }}">
                            @if ($hot)<span class="absolute -top-3 left-7 rounded-full bg-gold-400 px-3 py-1 text-xs font-semibold text-indigo-900">Más elegido</span>@endif
                            <p class="text-sm font-medium {{ $hot ? 'text-gold-300' : 'text-gold-600' }}">{{ $tag }}</p>
                            <h3 class="display mt-2 text-2xl {{ $hot ? 'text-gray-50' : 'text-indigo-900' }}">{{ $t }}</h3>
                            <p class="mt-3 flex-1 text-sm {{ $hot ? 'text-gray-300' : 'text-gray-600' }}">{{ $d }}</p>
                            <a href="{{ auth()->check() ? route('dashboard', ['tab' => 'reservar']) : route('register') }}" class="btn mt-6 {{ $hot ? 'btn-gold' : 'btn-outline' }}">Reservar <x-icon name="arrow-right" /></a>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Experiencias --}}
            <section id="experiencias" class="mx-auto max-w-6xl scroll-mt-20 px-5 pb-20 sm:px-8">
                <div class="reveal max-w-xl">
                    <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Experiencias</p>
                    <h2 class="display mt-2 text-4xl text-indigo-900">Más que una habitación.</h2>
                </div>
                <div class="mt-10 grid gap-5 md:grid-cols-3">
                    @foreach ([[4, 'Desayuno en la terraza', 'Productos locales, café de origen y vistas abiertas al amanecer.'], [6, 'Rincón de descanso', 'Espacios tranquilos para leer, trabajar o simplemente desconectar.'], [8, 'Escapadas guiadas', 'Rutas por la ciudad y los alrededores con recomendaciones de nuestro equipo.']] as $i => [$img, $t1, $d1])
                        <article class="reveal group relative isolate flex aspect-[3/4] flex-col justify-end overflow-hidden rounded-3xl p-6 text-gray-50" style="--i:{{ $i }}">
                            <img src="{{ asset('img/rooms/h'.$img.'.jpg') }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 ease-snap group-hover:scale-105">
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-indigo-900/90 via-indigo-900/30 to-transparent"></span>
                            <h3 class="display text-2xl">{{ $t1 }}</h3>
                            <p class="mt-2 max-h-0 overflow-hidden text-sm text-gray-200 opacity-0 transition-all duration-500 ease-snap group-hover:max-h-24 group-hover:opacity-100 max-md:max-h-24 max-md:opacity-100">{{ $d1 }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Tu estancia --}}
            <section class="border-y border-gray-200 bg-white">
                <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                    <h2 class="reveal display max-w-xl text-4xl text-indigo-900">Tu estancia, paso a paso.</h2>
                    <ol class="mt-12 grid gap-8 md:grid-cols-4">
                        @foreach ([['calendar', 'Antes', 'Reservas y recibes tu comprobante QR por correo.'], ['pin', 'Llegada', 'Presentas el QR y el hotel lo verifica al instante.'], ['sparkles', 'Durante', 'Desayuno, Wi‑Fi y atención 24 horas.'], ['star', 'Después', 'Cuéntanos tu experiencia en las opiniones.']] as $i => [$ic, $t1, $d1])
                            <li class="reveal relative" style="--i:{{ $i }}">
                                <span class="flex size-12 items-center justify-center rounded-full border border-gold-400 bg-gray-50 text-gold-600"><x-icon :name="$ic" class="size-5" /></span>
                                @if ($i < 3)<span class="absolute left-14 top-6 hidden h-px w-[calc(100%-3.5rem)] bg-gray-200 md:block"></span>@endif
                                <p class="mt-4 text-xs font-medium uppercase tracking-widest text-gold-600">Paso {{ $i + 1 }}</p>
                                <h3 class="display text-xl text-indigo-900">{{ $t1 }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $d1 }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Cómo funciona --}}
            <section id="como-funciona" class="scroll-mt-20 border-y border-gray-200 bg-gray-50">
                <div class="mx-auto grid max-w-6xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12">
                    <h2 class="reveal display text-4xl text-indigo-900 lg:col-span-4">Reservar es cuestión de minutos.</h2>
                    <ol class="divide-y divide-gray-200 lg:col-span-8">
                        @foreach ([
                            ['Crea tu cuenta', 'Solo nombre, correo y contraseña. Sin trámites.'],
                            ['Elige habitación y fechas', 'Ves el total al instante y evitamos cruces con otras reservas.'],
                            ['Recibe tu comprobante', 'PDF con código QR por correo, verificable por el hotel al llegar.'],
                        ] as $i => [$t, $d])
                            <li class="reveal flex gap-6 py-6 first:pt-0" style="--i:{{ $i }}">
                                <span class="display num text-5xl text-gold-500">{{ $i + 1 }}</span>
                                <div><h3 class="text-xl font-semibold text-gray-900">{{ $t }}</h3><p class="mt-1 text-gray-600">{{ $d }}</p></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            {{-- Incluye --}}
            <section id="incluye" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-20 sm:px-8">
                <h2 class="reveal display max-w-xl text-4xl text-indigo-900">Todo lo que necesitas, sin letra pequeña.</h2>
                <ul class="mt-10 grid gap-x-12 gap-y-5 sm:grid-cols-2">
                    @foreach (['Confirmación inmediata de tu reserva', 'Comprobante PDF con código QR firmado', 'Correo de confirmación automático', 'Cancelación desde tu panel en un clic', 'Disponibilidad en tiempo real', 'Total calculado antes de confirmar'] as $i => $item)
                        <li class="reveal flex items-center gap-3 border-b border-gray-200 pb-5 text-gray-800" style="--i:{{ $i % 2 }}"><span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-indigo-800 text-gray-50"><x-icon name="check" class="size-3.5" /></span>{{ $item }}</li>
                    @endforeach
                </ul>
            </section>


            {{-- Comentarios --}}
            <section id="comentarios" class="scroll-mt-20 border-t border-gray-200 bg-gray-50">
                <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                    <div class="reveal flex flex-wrap items-end justify-between gap-6">
                        <h2 class="display max-w-lg text-4xl text-indigo-900">Lo que dicen nuestros huéspedes.</h2>
                        @if ($reviewCount)
                            <p class="flex items-center gap-3"><span class="display num text-5xl text-indigo-900">{{ number_format($avg, 1) }}</span>
                                <span><span class="flex text-gold-500">@for ($s = 1; $s <= 5; $s++)<svg viewBox="0 0 24 24" class="size-4 {{ $s <= round($avg) ? '' : 'opacity-25' }}" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3 6.1 20.6l1.3-6.6L2.5 9.4l6.6-.8z" fill="currentColor"/></svg>@endfor</span>
                                <span class="text-sm text-gray-500">{{ $reviewCount }} {{ $reviewCount == 1 ? 'opinión' : 'opiniones' }}</span></span></p>
                        @endif
                    </div>

                    <div class="mt-10 grid gap-10 lg:grid-cols-12">
                        <div class="lg:col-span-7">
                            @forelse ($reviews as $rv)
                                <article class="reveal border-b border-gray-200 py-6 first:pt-0" style="--i:{{ $loop->index % 3 }}">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-10 items-center justify-center rounded-full bg-indigo-800 text-sm font-semibold text-gray-50">{{ mb_strtoupper(mb_substr($rv->user->name, 0, 1)) }}</span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900">{{ $rv->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $rv->created_at->locale('es')->diffForHumans() }}</p>
                                        </div>
                                        <span class="flex text-gold-500" aria-label="{{ $rv->rating }} de 5">@for ($s = 1; $s <= 5; $s++)<svg viewBox="0 0 24 24" class="size-4 {{ $s <= $rv->rating ? '' : 'opacity-25' }}" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3 6.1 20.6l1.3-6.6L2.5 9.4l6.6-.8z" fill="currentColor"/></svg>@endfor</span>
                                        @auth
                                            @if (auth()->user()->is_admin || auth()->id() === $rv->user_id)
                                                <form method="POST" action="{{ route('reviews.destroy', $rv) }}" data-confirm="Se eliminará de la página." data-confirm-title="¿Eliminar el comentario?" data-confirm-button="Sí, eliminar">@csrf @method('DELETE')
                                                    <button class="btn btn-quiet !p-2 hover:!text-red-700" aria-label="Eliminar comentario"><x-icon name="trash" /></button></form>
                                            @endif
                                        @endauth
                                    </div>
                                    <p class="mt-3 text-gray-700">{{ $rv->comment }}</p>
                                </article>
                            @empty
                                <p class="rounded-2xl border border-dashed border-gray-300 px-6 py-12 text-center text-gray-500">Aún no hay comentarios. ¡Sé la primera persona en opinar!</p>
                            @endforelse
                        </div>

                        <aside class="lg:col-span-5">
                            <div class="reveal sticky top-24 rounded-3xl border border-gray-200 bg-white p-6">
                                <h3 class="display text-2xl text-indigo-900">Deja tu comentario</h3>
                                @auth
                                    <form method="POST" action="{{ route('reviews.store') }}" class="mt-5 space-y-4" x-data="{ r: {{ (int) old('rating', 5) }}, h: 0 }">
                                        @csrf
                                        <div>
                                            <x-input-label value="Tu valoración" />
                                            <div class="mt-1 flex gap-1" @mouseleave="h = 0">
                                                @for ($s = 1; $s <= 5; $s++)
                                                    <button type="button" @click="r = {{ $s }}" @mouseenter="h = {{ $s }}" aria-label="{{ $s }} estrellas"
                                                        :class="(h || r) >= {{ $s }} ? 'text-gold-500 scale-110' : 'text-gray-300'" class="transition duration-150 ease-snap active:scale-95">
                                                        <svg viewBox="0 0 24 24" class="size-8" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3 6.1 20.6l1.3-6.6L2.5 9.4l6.6-.8z" fill="currentColor"/></svg>
                                                    </button>
                                                @endfor
                                            </div>
                                            <input type="hidden" name="rating" :value="r">
                                        </div>
                                        <div>
                                            <x-input-label for="comment" value="Comentario" />
                                            <textarea id="comment" name="comment" rows="4" maxlength="500" required class="field mt-1" placeholder="Cuéntanos cómo fue tu experiencia">{{ old('comment') }}</textarea>
                                        </div>
                                        <button class="btn btn-primary w-full">Publicar comentario</button>
                                    </form>
                                @else
                                    <p class="mt-2 text-gray-600">Inicia sesión para compartir tu experiencia con otros huéspedes.</p>
                                    <div class="mt-5 flex gap-2"><a href="{{ route('login') }}" class="btn btn-primary">Entrar</a><a href="{{ route('register') }}" class="btn btn-outline">Crear cuenta</a></div>
                                @endauth
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            {{-- Cerca de nosotros --}}
            <section id="cerca" class="mx-auto max-w-6xl scroll-mt-20 px-5 py-20 sm:px-8" x-data="{ tab: 'comer' }">
                <div class="reveal flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-xl">
                        <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Cerca de nosotros</p>
                        <h2 class="display mt-2 text-4xl text-indigo-900">Descubre los alrededores.</h2>
                    </div>
                    <div class="flex gap-1 rounded-full border border-gray-200 bg-white p-1" role="tablist">
                        @foreach (['comer' => 'Comer', 'cultura' => 'Cultura', 'naturaleza' => 'Naturaleza'] as $k => $l)
                            <button type="button" role="tab" @click="tab = '{{ $k }}'" :aria-selected="tab === '{{ $k }}'" :class="tab === '{{ $k }}' ? 'bg-indigo-900 text-gray-50' : 'text-gray-600 hover:bg-gray-100'" class="rounded-full px-4 py-1.5 text-sm font-medium transition duration-200 active:scale-95">{{ $l }}</button>
                        @endforeach
                    </div>
                </div>
                @foreach ([
                    'comer' => [['Mercado central', 'Cocina local y productos frescos', '5 min a pie'], ['Café del Parque', 'Desayunos y repostería artesanal', '3 min a pie'], ['Restaurante Mirador', 'Cena con vista a la ciudad', '8 min en auto']],
                    'cultura' => [['Museo de la Ciudad', 'Historia y arte regional', '10 min a pie'], ['Plaza Mayor', 'Centro histórico y eventos', '7 min a pie'], ['Teatro Municipal', 'Música y espectáculos en vivo', '12 min en auto']],
                    'naturaleza' => [['Parque Horizonte', 'Senderos y zonas de picnic', '6 min a pie'], ['Mirador del Valle', 'Atardeceres panorámicos', '15 min en auto'], ['Jardín Botánico', 'Más de 300 especies', '12 min en auto']],
                ] as $k => $items)
                    <div x-show="tab === '{{ $k }}'" x-cloak x-transition.opacity.duration.250ms class="mt-8 grid gap-4 md:grid-cols-3">
                        @foreach ($items as [$n, $d, $dist])
                            <div class="rounded-3xl border border-gray-200 bg-white p-6 transition duration-300 ease-snap hover:-translate-y-1 hover:border-gold-400">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-600"><x-icon name="pin" class="size-3.5" /> {{ $dist }}</span>
                                <h3 class="display mt-4 text-xl text-indigo-900">{{ $n }}</h3>
                                <p class="mt-1 text-sm text-gray-600">{{ $d }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </section>

            {{-- Políticas --}}
            <section class="border-y border-gray-200 bg-white">
                <div class="mx-auto grid max-w-6xl gap-px bg-gray-200 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([['clock', 'Check‑in / Check‑out', 'Entrada desde las 14:00 y salida hasta las 12:00.'], ['calendar', 'Cancelación gratuita', 'Cancela desde tu panel cuando lo necesites.'], ['users', 'Niños bienvenidos', 'Menores de 6 años se alojan sin costo.'], ['shield', 'Pago en el hotel', 'Sin cargos por adelantado al reservar.']] as [$ic, $tt, $dd])
                        <div class="reveal bg-white px-6 py-8">
                            <x-icon :name="$ic" class="size-6 text-gold-600" />
                            <p class="mt-3 font-medium text-gray-900">{{ $tt }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $dd }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
            {{-- Preguntas frecuentes --}}
            <section id="preguntas" class="mx-auto grid max-w-6xl scroll-mt-20 gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12">
                <h2 class="reveal display text-4xl text-indigo-900 lg:col-span-4">Preguntas frecuentes.</h2>
                <div class="divide-y divide-gray-200 border-y border-gray-200 lg:col-span-8">
                    @foreach ([
                        ['¿Puedo cancelar mi reserva?', 'Sí. Desde "Mis reservas" en tu panel puedes cancelarla en un clic y la habitación vuelve a quedar disponible.'],
                        ['¿Cómo recibo mi comprobante?', 'Al confirmar te enviamos un correo con el PDF adjunto, que incluye un código QR firmado. También puedes descargarlo desde tu panel.'],
                        ['¿Qué pasa si la habitación ya está ocupada?', 'El sistema valida las fechas y no permite reservas cruzadas: verás un aviso para elegir otras fechas u otra habitación.'],
                        ['¿Necesito cuenta para reservar?', 'Sí, crear una cuenta es gratis y toma menos de un minuto. Así puedes ver y gestionar todas tus reservas.'],
                    ] as $i => [$q, $a])
                        <details class="reveal group py-5" style="--i:{{ $i }}">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-medium text-gray-900 [&::-webkit-details-marker]:hidden">
                                {{ $q }}
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full border border-gray-300 text-gray-600 transition-transform duration-300 ease-snap group-open:rotate-45"><x-icon name="plus" /></span>
                            </summary>
                            <p class="mt-3 max-w-xl text-gray-600">{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
            {{-- Contacto --}}
            <section id="contacto" class="scroll-mt-20 border-t border-gray-200 bg-gray-50">
                <div class="mx-auto grid max-w-6xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12">
                    <div class="reveal lg:col-span-5">
                        <p class="text-sm font-medium uppercase tracking-widest text-gold-600">Contacto</p>
                        <h2 class="display mt-2 text-4xl text-indigo-900">Encuéntranos y escríbenos.</h2>
                        <ul class="mt-8 space-y-5 text-gray-700">
                            <li class="flex gap-3"><x-icon name="pin" class="mt-0.5 size-5 text-gold-600" /><span>{{ config('hotel.address') }}</span></li>
                            <li class="flex gap-3"><x-icon name="phone" class="mt-0.5 size-5 text-gold-600" /><span>{{ config('hotel.phone') }}</span></li>
                            <li class="flex gap-3"><x-icon name="mail" class="mt-0.5 size-5 text-gold-600" /><a href="mailto:{{ config('hotel.email') }}" class="hover:text-indigo-900">{{ config('hotel.email') }}</a></li>
                            <li class="flex gap-3"><x-icon name="clock" class="mt-0.5 size-5 text-gold-600" /><span>Check‑in desde las 14:00 · Check‑out hasta las 12:00</span></li>
                        </ul>
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="{{ config('hotel.maps_url') }}" target="_blank" rel="noopener" class="btn btn-outline">Cómo llegar <x-icon name="arrow-right" /></a>
                            <div class="flex gap-2.5">@include('partials.socials')</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('contact') }}" class="reveal space-y-4 rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 lg:col-span-7" style="--i:1" x-data="{ n: {{ strlen(old('message', '')) }} }">
                        @csrf
                        <h3 class="display text-2xl text-indigo-900">Envíanos un mensaje</h3>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label for="c_name" class="text-sm font-medium text-gray-700">Nombre</label><input id="c_name" name="name" value="{{ old('name') }}" required minlength="3" maxlength="100" autocomplete="name" class="field mt-1"></div>
                            <div><label for="c_email" class="text-sm font-medium text-gray-700">Correo</label><input id="c_email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="field mt-1"></div>
                        </div>
                        <div>
                            <label for="c_msg" class="text-sm font-medium text-gray-700">Mensaje</label>
                            <textarea id="c_msg" name="message" rows="4" required minlength="10" maxlength="1000" @input="n = $event.target.value.length" class="field mt-1" placeholder="¿En qué podemos ayudarte?">{{ old('message') }}</textarea>
                            <p class="mt-1 text-right text-xs text-gray-500"><span class="num" x-text="n"></span>/1000</p>
                        </div>
                        <button class="btn btn-primary w-full sm:w-auto">Enviar mensaje <x-icon name="arrow-right" /></button>
                    </form>
                </div>
            </section>

            {{-- Llamado final --}}
            <section class="mx-auto max-w-6xl px-5 pb-20 sm:px-8">
                <div class="reveal relative overflow-hidden rounded-[2rem] bg-indigo-900 px-8 py-16 text-center sm:px-16">
                    <svg class="pointer-events-none absolute left-1/2 top-full size-[34rem] -translate-x-1/2 -translate-y-1/2 text-gold-400/30" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width=".25" aria-hidden="true"><circle cx="50" cy="50" r="12"/><circle cx="50" cy="50" r="24"/><circle cx="50" cy="50" r="36"/><circle cx="50" cy="50" r="48"/></svg>
                    <h2 class="display relative text-4xl text-gray-50 sm:text-5xl">Descansa. Reconecta. Vuelve.</h2>
                    <p class="relative mx-auto mt-4 max-w-md text-gray-300">Tu habitación te espera en Casa Horizonte.</p>
                    <a href="{{ auth()->check() ? route('dashboard', ['tab' => 'reservar']) : route('register') }}" class="btn btn-gold relative mt-8 !px-7 !py-3.5 text-base">Reservar ahora <x-icon name="arrow-right" /></a>
                </div>
            </section>
<section id="boletin" class="mx-auto max-w-6xl scroll-mt-20 px-5 pb-20 sm:px-8">
                <form method="POST" action="{{ route('newsletter') }}" class="reveal grid items-center gap-6 rounded-3xl border border-gray-200 bg-white p-8 md:grid-cols-2">
                    @csrf
                    <div>
                        <h2 class="display text-3xl text-indigo-900">Recibe ofertas y novedades.</h2>
                        <p class="mt-2 text-sm text-gray-600">Un correo ocasional con promociones. Sin spam, cancela cuando quieras.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <label for="nl" class="sr-only">Correo</label>
                        <input id="nl" type="email" name="email" required placeholder="tu@correo.com" autocomplete="email" class="field flex-1">
                        <button class="btn btn-gold">Suscribirme</button>
                    </div>
                </form>
            </section>
        </main>

        <footer class="border-t border-gray-200">
            <div class="mx-auto grid max-w-6xl gap-10 px-5 py-12 text-sm text-gray-500 sm:px-8 md:grid-cols-4">
                <div class="md:col-span-2">
                    <x-application-logo class="h-12 w-auto" />
                    <p class="mt-4 max-w-xs">Reserva tu habitación en minutos y recibe tu comprobante con código QR.</p>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Explora</p>
                    <ul class="mt-3 space-y-2">
                        <li><a href="#habitaciones" class="hover:text-indigo-900">Habitaciones</a></li>
                        <li><a href="#servicios" class="hover:text-indigo-900">Servicios</a></li>
                        <li><a href="#galeria" class="hover:text-indigo-900">Galería</a></li>
                        <li><a href="#preguntas" class="hover:text-indigo-900">Preguntas</a></li>
                    </ul>
                </div>
                <div>
                    <p class="font-medium text-gray-900">Síguenos</p>
                    <div class="mt-3 flex gap-2.5">@include('partials.socials')</div>
                </div>
            </div>
            <div class="border-t border-gray-200 py-5 text-center text-xs text-gray-500">© {{ date('Y') }} Casa Horizonte · Todos los derechos reservados</div>
        </footer>
        @php $wa = preg_replace('/\D/', '', config('hotel.whatsapp')); @endphp
        <a href="{{ $wa ? 'https://wa.me/'.$wa.'?text='.rawurlencode('Hola, quisiera información sobre una reserva en Casa Horizonte.') : 'mailto:'.config('hotel.email') }}" target="_blank" rel="noopener" aria-label="{{ $wa ? 'Escribir por WhatsApp' : 'Escribir por correo' }}" class="wa-pulse fixed bottom-5 left-5 z-40 grid size-12 place-items-center rounded-full bg-[#25d366] text-white shadow-lg transition duration-200 hover:scale-110 active:scale-95"><x-social name="whatsapp" class="size-6" /></a>        <a href="#" x-data="{ s: false }" @scroll.window="s = window.scrollY > 600" x-show="s" x-cloak x-transition.opacity aria-label="Volver arriba" class="fixed bottom-5 right-5 z-40 grid size-11 place-items-center rounded-full bg-indigo-900 text-gray-50 shadow-lg transition active:scale-95"><x-icon name="arrow-up" class="size-5" /></a>
        <div x-data="{ show: !localStorage.getItem('ch_cookies') }" x-show="show" x-cloak x-transition.opacity class="fixed inset-x-4 bottom-4 z-50 mx-auto flex max-w-xl flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-[0_20px_50px_-20px_rgba(15,26,69,0.5)] sm:bottom-5">
            <p class="text-sm text-gray-600">Usamos cookies esenciales para que tu sesión y tu reserva funcionen.</p>
            <button type="button" @click="localStorage.setItem('ch_cookies', '1'); show = false" class="btn btn-primary !py-2">Entendido</button>
        </div>
        @include('partials.flash')
    </body>
</html>
