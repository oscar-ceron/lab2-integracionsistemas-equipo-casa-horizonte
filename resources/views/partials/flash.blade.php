{{-- Alerta SweetAlert2 (como en lab1): éxito, error o primer error de validación. --}}
@php
    $flash = session('success') ? ['success', 'Éxito', session('success')]
        : (session('error') ? ['error', 'Error', session('error')]
        : ($errors->any() ? ['error', 'Revisa los datos', $errors->first()] : null));
@endphp
@if ($flash)
    <div id="flash-alert" hidden data-icon="{{ $flash[0] }}" data-title="{{ $flash[1] }}" data-text="{{ $flash[2] }}"></div>
@endif