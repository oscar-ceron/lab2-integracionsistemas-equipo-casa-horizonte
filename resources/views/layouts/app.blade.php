<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('layouts.head', ['title' => 'Casa Horizonte'])
    </head>
    <body class="min-h-screen">
        @include('layouts.navigation')

        @isset($header)
            <header class="border-b border-gray-200">
                <div class="mx-auto max-w-6xl px-5 py-8 sm:px-8 [&_h2]:font-display [&_h2]:text-3xl [&_h2]:font-semibold [&_h2]:text-indigo-900">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>{{ $slot }}</main>

        @include('partials.flash')
    </body>
</html>
