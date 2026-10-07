@props(['active'])

@php
$classes = 'block rounded-lg px-3 py-2.5 text-base font-medium transition-colors duration-150 '
    . (($active ?? false) ? 'bg-indigo-50 text-indigo-900' : 'text-gray-600 hover:bg-gray-200/60');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>