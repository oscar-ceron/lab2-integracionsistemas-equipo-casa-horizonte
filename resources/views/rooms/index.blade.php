@extends('layouts.app')

@section('title', 'Habitaciones')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <span class="eyebrow">Bienvenido a Casa Horizonte</span>
            <h1>Tu próxima pausa<br>comienza aquí.</h1>
            <p>Descubre espacios pensados para descansar, reconectar y disfrutar cada momento. Encuentra la habitación perfecta para tu estancia.</p>
        </div>
    </section>

    <section class="container section">
        <div class="section-heading">
            <div><span class="eyebrow">Nuestra colección</span><h2>Habitaciones para ti</h2></div>
            <p>{{ $rooms->count() }} {{ $rooms->count() === 1 ? 'habitación disponible' : 'habitaciones disponibles' }}</p>
        </div>

        @if ($rooms->isNotEmpty())
            <div class="room-grid">
                @foreach ($rooms as $room)
                    <article class="room-card">
                        <div class="room-visual visual-{{ ($loop->index % 3) + 1 }}">
                            <span class="visual-label">{{ ucfirst($room->status) }}</span>
                        </div>
                        <div class="room-body">
                            <div class="room-top">
                                <div><h3>{{ $room->type }}</h3><span class="room-number">Habitación {{ $room->room_number }}</span></div>
                                <div class="room-price">${{ number_format((float) $room->price_per_night, 2) }}<small>por noche</small></div>
                            </div>
                            <div class="room-meta"><span>✦ Estancia confortable</span><span>◷ Atención personalizada</span></div>
                            <a class="room-action" href="{{ url('/reservations/create') }}?room_id={{ $room->id }}">
                                <span>Reservar habitación</span><span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="empty-card"><strong>Pronto encontrarás habitaciones aquí.</strong><br>El catálogo todavía no tiene habitaciones registradas.</div>
        @endif
    </section>
@endsection
