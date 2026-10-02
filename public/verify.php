<?php

declare(strict_types=1);

use App\Conexion\Database;
use App\Repositorios\ReservationRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

$reservationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$token = (string) ($_GET['token'] ?? '');
$appKey = getenv('APP_KEY') ?: 'casa-horizonte-local-key';
$validToken = $reservationId !== false && $reservationId !== null
    ? hash_hmac('sha256', (string) $reservationId, $appKey)
    : '';
$reservation = null;
$error = null;

if ($reservationId === false || $reservationId === null || !hash_equals($validToken, $token)) {
    http_response_code(403);
    $error = 'El enlace de verificacion no es valido.';
} else {
    try {
        $reservation = (new ReservationRepository(Database::connection()))->find($reservationId);
        if ($reservation === null) {
            http_response_code(404);
            $error = 'Reserva no encontrada.';
        }
    } catch (Throwable $exception) {
        http_response_code(500);
        $error = 'No fue posible consultar la reserva.';
    }
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?><!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Verificacion | Casa Horizonte</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body style="background:#f4f6f8;font-family:Arial,sans-serif;color:#172a3d"><main class="container py-5" style="max-width:680px"><div class="text-center mb-4"><div style="font-size:24px;font-weight:bold;letter-spacing:2px;color:#17324d">CASA HORIZONTE</div><div style="color:#1f8a83;font-size:12px;letter-spacing:1px">VERIFICACION DE RESERVA</div></div>
<?php if ($error !== null): ?><div class="alert alert-danger text-center"><?= $escape($error) ?></div><?php else: ?><div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5"><div class="text-center mb-4"><span class="badge rounded-pill text-bg-success px-3 py-2">RESERVA CONFIRMADA</span><h1 class="h3 mt-3">Datos verificados</h1><p class="text-secondary">Esta información corresponde a una reserva registrada en Casa Horizonte.</p></div><div class="list-group list-group-flush"><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Reserva</span><strong>#<?= $escape($reservation['id']) ?></strong></div><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Huésped</span><strong><?= $escape($reservation['guest_name']) ?></strong></div><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Habitación</span><strong><?= $escape($reservation['room_number']) ?> · <?= $escape($reservation['room_type']) ?></strong></div><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Entrada</span><strong><?= $escape($reservation['start_date']) ?></strong></div><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Salida</span><strong><?= $escape($reservation['end_date']) ?></strong></div><div class="list-group-item d-flex justify-content-between"><span class="text-secondary">Total</span><strong class="text-success fs-5">$<?= number_format((float) $reservation['total'], 2) ?></strong></div></div></div></div><?php endif; ?><p class="text-center text-secondary small mt-4">Comprobante digital generado automáticamente.</p></main></body></html>