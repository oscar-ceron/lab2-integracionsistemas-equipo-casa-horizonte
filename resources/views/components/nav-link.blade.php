@props(['active'])

@php
$classes = 'relative inline-flex h-full items-center text-sm font-medium transition-colors duration-150 ease-snap after:absolute after:inset-x-0 after:bottom-0 after:h-0.5 after:bg-indigo-800 after:transition-transform after:duration-200 after:ease-snap '
    . (($active ?? false)
        ? 'text-indigo-900 after:scale-x-100'
        : 'text-gray-500 hover:text-indigo-900 after:scale-x-0 hover:after:scale-x-50');
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>