@extends('layouts.app')

@section('title', 'Crear reserva')

@section('content')
    <div class="container form-wrap">
        <a class="back-link" href="{{ route('rooms.index') }}">← Volver al catálogo</a>
        <div class="form-intro">
            <span class="eyebrow">Casa Horizonte · Tu estancia</span>
            <h1>Prepara tu reserva</h1>
            <p>Completa los datos de tu visita. La información marcada con * es obligatoria.</p>
        </div>

        @if ($errors->any())
            <div class="alert" role="alert"><strong>Revisa los datos del formulario.</strong> Corrige los campos marcados para continuar.</div>
        @endif

        <form class="form-card" method="POST" action="{{ url('/reservations') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label for="user_id">ID del huésped <span aria-hidden="true">*</span></label>
                    <input id="user_id" name="user_id" type="number" min="1" value="{{ old('user_id') }}" placeholder="Ej. 1" required>
                    @error('user_id')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="room_id">ID de habitación <span aria-hidden="true">*</span></label>
                    <input id="room_id" name="room_id" type="number" min="1" value="{{ old('room_id', request('room_id')) }}" placeholder="Ej. 1" required>
                    @error('room_id')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="check_in">Fecha de entrada <span aria-hidden="true">*</span></label>
                    <input id="check_in" name="check_in" type="date" value="{{ old('check_in') }}" required>
                    @error('check_in')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="check_out">Fecha de salida <span aria-hidden="true">*</span></label>
                    <input id="check_out" name="check_out" type="date" value="{{ old('check_out') }}" required>
                    @error('check_out')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="total_price">Total de la estancia (USD) <span aria-hidden="true">*</span></label>
                    <input id="total_price" name="total_price" type="number" min="0" step="0.01" value="{{ old('total_price') }}" placeholder="0.00" required>
                    @error('total_price')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="status">Estado <span aria-hidden="true">*</span></label>
                    <select id="status" name="status" required>
                        <option value="">Selecciona un estado</option>
                        <option value="pending" @selected(old('status') === 'pending')>Pendiente</option>
                        <option value="confirmed" @selected(old('status') === 'confirmed')>Confirmada</option>
                        <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelada</option>
                    </select>
                    @error('status')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <hr class="form-divider">
            <div class="form-actions">
                <span class="help">Verifica las fechas y los identificadores antes de enviar.</span>
                <button class="button" type="submit">Enviar solicitud <span aria-hidden="true">→</span></button>
            </div>
        </form>
    </div>
@endsection
