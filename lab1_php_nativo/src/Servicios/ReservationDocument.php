<?php

declare(strict_types=1);

namespace App\Servicios;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

final class ReservationDocument
{
    public function pdf(array $reservation): string
    {
        // El PDF es el comprobante descargable y contiene un QR firmado para verificarlo.
        $appUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');
        $appKey = getenv('APP_KEY') ?: 'casa-horizonte-local-key';
        $reservationId = (int) $reservation['id'];
        $token = hash_hmac('sha256', (string) $reservationId, $appKey);
        $qrData = $appUrl . '/verify.php?id=' . $reservationId . '&token=' . $token;
        $qr = (new Builder(
            writer: new SvgWriter(),
            data: $qrData,
            size: 180,
            margin: 8
        ))
            ->build()
            ->getDataUri();
        $escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        $html = '<!doctype html><html lang="es"><head><meta charset="UTF-8"><style>'
            . '@page{margin:0}body{font-family:DejaVu Sans,Arial,sans-serif;color:#172a3d;margin:0;background:#fff}'
            . '.header{background:#17324d;color:#fff;padding:22px 34px}.brand{font-size:22px;font-weight:bold;letter-spacing:2px}.sub{color:#cbd8e2;font-size:10px;margin-top:5px;letter-spacing:1px}'
            . '.main{padding:24px 34px}.eyebrow{color:#1f8a83;font-size:9px;font-weight:bold;letter-spacing:2px;text-transform:uppercase}.title{font-size:27px;margin:6px 0 18px;color:#17324d}'
            . '.success{background:#e7f6ef;border-left:4px solid #1f8a83;padding:11px 14px;color:#216b62;font-size:12px;margin-bottom:17px}.grid{border:1px solid #dce3ea;border-radius:5px;padding:14px}.row{padding:8px 0;border-bottom:1px solid #edf0f2;font-size:12px}.row:last-child{border-bottom:0}.label{color:#667085;display:inline-block;width:42%}.value{font-weight:bold}.total{font-size:18px;color:#1f8a83}.qr{text-align:center;border-top:1px solid #dce3ea;margin-top:18px;padding-top:14px}.qr img{width:120px;height:120px}.qr p{font-size:9px;color:#667085;margin:5px 0 0}.footer{background:#f5f7f9;color:#667085;padding:13px 34px;font-size:9px}'
            . '</style></head><body><div class="header"><div class="brand">CASA HORIZONTE</div><div class="sub">HOSPEDAJE · CONFORT · TRANQUILIDAD</div></div><div class="main">'
            . '<div class="eyebrow">Comprobante oficial</div><div class="title">Reserva confirmada</div>'
            . '<div class="success">Tu reserva ha sido registrada correctamente. Presenta este comprobante al llegar.</div><div class="grid">'
            . '<div class="row"><span class="label">Numero de reserva</span><span class="value">#' . $escape($reservation['id']) . '</span></div>'
            . '<div class="row"><span class="label">Huesped</span><span class="value">' . $escape($reservation['guest_name']) . '</span></div>'
            . '<div class="row"><span class="label">Correo</span><span class="value">' . $escape($reservation['guest_email']) . '</span></div>'
            . '<div class="row"><span class="label">Habitacion</span><span class="value">' . $escape($reservation['room_number']) . ' · ' . $escape($reservation['room_type']) . '</span></div>'
            . '<div class="row"><span class="label">Entrada</span><span class="value">' . $escape($reservation['start_date']) . '</span></div>'
            . '<div class="row"><span class="label">Salida</span><span class="value">' . $escape($reservation['end_date']) . '</span></div>'
            . '<div class="row"><span class="label">Estado</span><span class="value">Confirmada</span></div>'
            . '<div class="row"><span class="label">Total</span><span class="value total">$' . number_format((float) $reservation['total'], 2) . '</span></div></div>'
            . '<div class="qr"><img src="' . $qr . '" alt="QR de reserva"><p>Escanea este codigo para verificar los datos de tu reserva</p></div></div><div class="footer">Casa Horizonte · Comprobante generado automaticamente</div></body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
