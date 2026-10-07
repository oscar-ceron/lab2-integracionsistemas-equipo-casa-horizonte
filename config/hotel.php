<?php

return [
    'phone' => env('HOTEL_PHONE') ?: 'Contacto no configurado',
    'email' => env('HOTEL_EMAIL') ?: env('MAIL_FROM_ADDRESS') ?: 'reservas@casahorizonte.com',
    'whatsapp' => env('HOTEL_WHATSAPP', ''),
    'address' => env('HOTEL_ADDRESS') ?: 'Direccion del establecimiento',
    'maps_url' => env('HOTEL_MAPS_URL') ?: 'https://maps.google.com/',
    'facebook' => env('HOTEL_FACEBOOK_URL') ?: 'https://www.facebook.com/',
    'instagram' => env('HOTEL_INSTAGRAM_URL') ?: 'https://www.instagram.com/',
    'tiktok' => env('HOTEL_TIKTOK_URL') ?: 'https://www.tiktok.com/',
];