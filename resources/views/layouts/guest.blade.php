@php
    $copy = match (true) {
        request()->routeIs('admin.login') => ['Acceso administradores', 'Gestiona habitaciones y reservas del hotel.'],
        request()->routeIs('login') => ['Bienvenido de nuevo', 'Entra para gestionar tus reservas.'],
        request()->routeIs('register') => ['Crea tu cuenta', 'Reserva en minutos y recibe tu comprobante con QR.'],
        request()->routeIs('password.request') => ['Recupera tu acceso', null],
        default => [null, null],
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head', ['title' => 'Casa Horizonte'])
    </head>
    <body class="min-h-screen">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-indigo-900 lg:block">
                <x-horizon-art class="absolute inset-0 h-full w-full" />
                <div class="absolute inset-x-0 bottom-0 p-12">
                    <p class="display max-w-xs text-4xl leading-tight text-gray-50">Descansa.<br>Reconecta.<br>Vuelve.</p>
                </div>
            </aside>

            <div class="flex flex-col justify-center px-6 py-12 sm:px-12">
                <div class="mx-auto w-full max-w-sm">
                    <a href="/" aria-label="Casa Horizonte"><x-application-logo class="-ml-3 h-20 w-auto" /></a>

                    @if ($copy[0])
                        <div class="rise mt-8" style="--i:1">
                            <h1 class="display text-3xl text-indigo-900">{{ $copy[0] }}</h1>
                            @if ($copy[1])<p class="mt-2 text-gray-500">{{ $copy[1] }}</p>@endif
                        </div>
                    @endif

                    <div class="rise mt-8" style="--i:2">{{ $slot }}</div>
                </div>
            </div>
        </div>
        @include('partials.flash')
    </body>
</html>
