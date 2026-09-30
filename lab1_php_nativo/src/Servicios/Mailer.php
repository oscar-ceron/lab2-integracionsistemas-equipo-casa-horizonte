<?php

declare(strict_types=1);

namespace App\Servicios;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

final class Mailer
{
    public function sendReservationConfirmation(
        string $recipient,
        string $guestName,
        string $roomNumber,
        string $roomType,
        string $startDate,
        string $endDate,
        float $total,
        int $reservationId
    ): void {
        // Las credenciales se leen del entorno para no guardar secretos en el repositorio.
        $username = getenv('MAIL_USERNAME') ?: '';
        $password = getenv('MAIL_PASSWORD') ?: '';
        $from = getenv('MAIL_FROM') ?: $username;

        if ($username === '' || $password === '' || $from === '') {
            throw new RuntimeException('La reserva fue guardada, pero falta configurar MAIL_USERNAME, MAIL_PASSWORD y MAIL_FROM.');
        }

        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $mailer->SMTPAuth = true;
            $mailer->Username = $username;
            $mailer->Password = $password;
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mailer->Port = (int) (getenv('MAIL_PORT') ?: 587);
            $mailer->CharSet = 'UTF-8';
            $mailer->setFrom($from, 'Casa Horizonte');
            $mailer->addAddress($recipient, $guestName);
            $reservation = [
                'id' => $reservationId,
                'guest_name' => $guestName,
                'guest_email' => $recipient,
                'room_number' => $roomNumber,
                'room_type' => $roomType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'confirmed',
                'total' => $total,
            ];
            // Se adjunta el mismo comprobante PDF que el usuario puede descargar desde la web.
            $pdf = (new ReservationDocument())->pdf($reservation);
            $mailer->addStringAttachment(
                $pdf,
                'comprobante-reserva-' . $reservationId . '.pdf',
                PHPMailer::ENCODING_BASE64,
                'application/pdf'
            );
            $mailer->isHTML(true);
            $mailer->Subject = 'Reserva confirmada - Casa Horizonte';
            $mailer->Body = $this->buildConfirmationEmail(
                $guestName,
                $roomNumber,
                $roomType,
                $startDate,
                $endDate,
                $total,
                $reservationId
            );
            $mailer->AltBody = "Hola {$guestName}, tu reserva de la habitacion {$roomNumber} del {$startDate} al {$endDate} ha sido confirmada. Total: $" . number_format($total, 2);
            $mailer->send();
        } catch (Exception $exception) {
            throw new RuntimeException('La reserva fue guardada, pero Gmail no pudo enviar el correo: ' . $exception->getMessage(), 0, $exception);
        }
    }

    private function buildConfirmationEmail(
        string $guestName,
        string $roomNumber,
        string $roomType,
        string $startDate,
        string $endDate,
        float $total,
        int $reservationId
    ): string {
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $appUrl = rtrim(getenv('APP_URL') ?: 'http://localhost:8000', '/');
        $pdfUrl = $appUrl . '/confirmation.php?id=' . $reservationId;
        $whatsapp = preg_replace('/\D+/', '', getenv('HOTEL_WHATSAPP') ?: '') ?: '';
        $phone = $escape(getenv('HOTEL_PHONE') ?: 'Contacto no configurado');
        $contactEmail = $escape(getenv('HOTEL_EMAIL') ?: getenv('MAIL_FROM') ?: 'reservas@casahorizonte.com');
        $address = nl2br($escape(getenv('HOTEL_ADDRESS') ?: 'Direccion del establecimiento'));
        $mapsUrl = $escape(getenv('HOTEL_MAPS_URL') ?: 'https://maps.google.com/');
        $whatsappLink = $whatsapp !== '' ? 'https://wa.me/' . $whatsapp : 'https://wa.me/';

        $template = <<<'HTML'
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Reserva confirmada - Casa Horizonte</title></head>
<body style="margin:0;padding:30px 12px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#333"><table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8"><tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:16px;overflow:hidden">
<tr><td style="background:#1f2937;padding:32px 30px;text-align:center"><div style="font-size:28px;font-weight:bold;color:#fff;letter-spacing:1px">CASA HORIZONTE</div><div style="margin-top:8px;font-size:14px;color:#d1d5db">Hospedaje &nbsp;•&nbsp; Confort &nbsp;•&nbsp; Tranquilidad</div></td></tr>
<tr><td style="padding:35px 30px 20px;text-align:center"><div style="width:64px;height:64px;line-height:64px;background:#dcfce7;border-radius:50%;font-size:32px;color:#16a34a;margin:auto">✓</div><h1 style="margin:18px 0 8px;font-size:26px;color:#111827">¡Reserva confirmada!</h1><p style="margin:0;font-size:15px;color:#6b7280;line-height:1.6">Hola <strong>{{NAME}}</strong>, tu reserva en <strong>Casa Horizonte</strong> ha sido confirmada correctamente.</p></td></tr>
<tr><td style="padding:10px 30px 25px"><div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:24px"><h2 style="margin:0 0 18px;font-size:18px;color:#111827">🏨 Detalles de tu reserva</h2><p style="margin:0;padding:11px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:14px">Habitación <strong style="float:right;color:#111827">{{ROOM}} · {{TYPE}}</strong></p><p style="margin:0;padding:11px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:14px">📅 Fecha de entrada <strong style="float:right;color:#111827">{{START}}</strong></p><p style="margin:0;padding:11px 0;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:14px">📅 Fecha de salida <strong style="float:right;color:#111827">{{END}}</strong></p><p style="margin:0;padding:18px 0 5px;color:#111827;font-size:16px;font-weight:bold">Total <strong style="float:right;color:#16a34a;font-size:23px">${{TOTAL}}</strong></p></div></td></tr>
<tr><td style="padding:0 30px 25px"><div style="background:#eff6ff;border-left:4px solid #2563eb;padding:18px 20px;border-radius:8px"><p style="margin:0 0 8px;font-size:15px;color:#1e3a8a;font-weight:bold">🧳 Información importante</p><p style="margin:0;font-size:14px;color:#374151;line-height:1.7">Hemos registrado tu reserva correctamente. Te recomendamos guardar este correo y presentarlo al momento de tu llegada.</p></div></td></tr>
<tr><td style="padding:0 30px 25px"><table width="100%" cellpadding="0" cellspacing="0"><tr><td width="48%" valign="top" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px"><div style="font-size:13px;color:#6b7280">🕐 CHECK-IN</div><div style="margin-top:7px;font-size:16px;font-weight:bold;color:#111827">Desde las 2:00 PM</div></td><td width="4%"></td><td width="48%" valign="top" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:18px"><div style="font-size:13px;color:#6b7280">🕐 CHECK-OUT</div><div style="margin-top:7px;font-size:16px;font-weight:bold;color:#111827">Hasta las 12:00 PM</div></td></tr></table></td></tr>
<tr><td style="padding:0 30px 25px"><div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:22px;text-align:center"><h2 style="margin:0 0 8px;font-size:18px;color:#111827">¿Necesitas ayuda?</h2><p style="margin:0 0 18px;font-size:14px;color:#6b7280">Estamos disponibles para ayudarte con cualquier consulta.</p><a href="{{WHATSAPP_LINK}}" style="display:inline-block;background:#25D366;color:#fff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 22px;border-radius:7px">💬 Contactar por WhatsApp</a><div style="margin-top:14px;font-size:13px;color:#6b7280">📞 {{PHONE}}</div><div style="margin-top:5px;font-size:13px;color:#6b7280">✉️ {{EMAIL}}</div></div></td></tr>
<tr><td style="padding:0 30px 25px"><div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:18px 20px"><div style="font-size:15px;font-weight:bold;color:#9a3412">📍 ¿Cómo llegar?</div><p style="margin:7px 0 14px;font-size:14px;color:#374151;line-height:1.5">Casa Horizonte<br>{{ADDRESS}}</p><a href="{{MAPS_URL}}" style="color:#2563eb;font-size:13px;font-weight:bold;text-decoration:none">📍 Ver ubicación en Google Maps →</a></div></td></tr>
<tr><td style="padding:0 30px 30px;text-align:center"><h2 style="margin:0 0 8px;font-size:17px;color:#111827">Síguenos en nuestras redes</h2><p style="margin:0 0 18px;font-size:13px;color:#9ca3af">Conoce nuestras instalaciones, promociones y novedades.</p><a href="{{FACEBOOK}}" style="display:inline-block;margin:0 5px;width:40px;height:40px;line-height:40px;background:#1877F2;color:#fff;border-radius:50%;text-decoration:none;font-weight:bold;font-size:18px">f</a><a href="{{INSTAGRAM}}" style="display:inline-block;margin:0 5px;width:40px;height:40px;line-height:40px;background:#E1306C;color:#fff;border-radius:50%;text-decoration:none;font-weight:bold;font-size:18px">◎</a><a href="{{TIKTOK}}" style="display:inline-block;margin:0 5px;width:40px;height:40px;line-height:40px;background:#111;color:#fff;border-radius:50%;text-decoration:none;font-weight:bold;font-size:18px">♪</a></td></tr>
<tr><td style="padding:0 30px"><div style="border-top:1px solid #e5e7eb"></div></td></tr><tr><td style="padding:25px 30px 30px;text-align:center"><div style="font-size:15px;color:#374151;line-height:1.6"><strong>¡Estamos deseando recibirte! 🏨</strong></div><p style="margin:8px 0 0;font-size:13px;color:#9ca3af;line-height:1.5">Gracias por elegir Casa Horizonte. Esperamos que disfrutes de una estancia cómoda, tranquila y agradable.</p><a href="{{PDF_URL}}" style="display:inline-block;margin-top:18px;background:#2563eb;color:#fff;text-decoration:none;padding:13px 22px;border-radius:7px;font-weight:bold">Descargar comprobante PDF</a></td></tr><tr><td style="background:#111827;padding:25px 30px;text-align:center"><div style="font-size:17px;font-weight:bold;color:#fff">CASA HORIZONTE</div><div style="margin-top:7px;font-size:12px;color:#9ca3af">Hospedaje • Confort • Tranquilidad</div><div style="margin-top:15px;font-size:11px;color:#6b7280;line-height:1.5">Este correo fue generado automáticamente.<br>Por favor, no respondas directamente a este mensaje.</div></td></tr></table></td></tr></table></body></html>
HTML;

        return strtr($template, [
            '{{NAME}}' => $escape($guestName), '{{ROOM}}' => $escape($roomNumber), '{{TYPE}}' => $escape($roomType),
            '{{START}}' => $escape($startDate), '{{END}}' => $escape($endDate), '{{TOTAL}}' => number_format($total, 2),
            '{{PDF_URL}}' => $escape($pdfUrl), '{{WHATSAPP_LINK}}' => $escape($whatsappLink), '{{PHONE}}' => $phone,
            '{{EMAIL}}' => $contactEmail, '{{ADDRESS}}' => $address, '{{MAPS_URL}}' => $mapsUrl,
            '{{FACEBOOK}}' => $escape(getenv('HOTEL_FACEBOOK_URL') ?: 'https://www.facebook.com/'),
            '{{INSTAGRAM}}' => $escape(getenv('HOTEL_INSTAGRAM_URL') ?: 'https://www.instagram.com/'),
            '{{TIKTOK}}' => $escape(getenv('HOTEL_TIKTOK_URL') ?: 'https://www.tiktok.com/'),
        ]);
    }
}
