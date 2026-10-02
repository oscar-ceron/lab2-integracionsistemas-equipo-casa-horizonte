<?php

declare(strict_types=1);

use App\Conexion\Database;
use App\Repositorios\ReservationRepository;
use App\Servicios\ReservationDocument;

require dirname(__DIR__) . '/vendor/autoload.php';

$reservationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($reservationId === false || $reservationId === null || $reservationId < 1) {
    http_response_code(400);
    exit('Reserva no valida.');
}

try {
    $reservation = (new ReservationRepository(Database::connection()))->find($reservationId);
    if ($reservation === null) {
        http_response_code(404);
        exit('Reserva no encontrada.');
    }

    $pdf = (new ReservationDocument())->pdf($reservation);
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="comprobante-reserva-' . $reservationId . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
} catch (Throwable $exception) {
    http_response_code(500);
    exit('No fue posible generar el comprobante.');
}