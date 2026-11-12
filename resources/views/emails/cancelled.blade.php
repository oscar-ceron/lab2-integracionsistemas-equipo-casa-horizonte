@php
    $r = $reservation->loadMissing(['room', 'user']);
    $in = \Carbon\Carbon::parse($r->check_in)->locale('es')->isoFormat('D [de] MMMM YYYY');
    $out = \Carbon\Carbon::parse($r->check_out)->locale('es')->isoFormat('D [de] MMMM YYYY');
@endphp
<!DOCTYPE html>
<html lang="es">
<body style="margin:0;background:#f6f5f1;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;">
            <h1 style="margin:0 0 8px;font-size:22px;color:#0f1a45;">Tu reserva fue cancelada</h1>
            <p style="margin:0 0 20px;color:#4b5563;">Hola {{ $r->user->name }}, confirmamos la cancelación de tu reserva <strong>#{{ $r->id }}</strong>. La habitación quedó liberada.</p>
            <table style="width:100%;font-size:14px;border-collapse:collapse;">
                <tr><td style="padding:8px 0;color:#6b7280;">Habitación</td><td style="padding:8px 0;text-align:right;">{{ $r->room->room_number }} · {{ $r->room->type }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;border-top:1px solid #eee;">Entrada</td><td style="padding:8px 0;text-align:right;border-top:1px solid #eee;">{{ $in }}</td></tr>
                <tr><td style="padding:8px 0;color:#6b7280;border-top:1px solid #eee;">Salida</td><td style="padding:8px 0;text-align:right;border-top:1px solid #eee;">{{ $out }}</td></tr>
                @if ($r->cancel_reason)
                    <tr><td style="padding:8px 0;color:#6b7280;border-top:1px solid #eee;">Motivo</td><td style="padding:8px 0;text-align:right;border-top:1px solid #eee;">{{ $r->cancel_reason }}</td></tr>
                @endif
            </table>
            <p style="margin:20px 0 0;font-size:13px;color:#6b7280;">No se realizó ningún cargo. Cuando quieras, puedes volver a reservar en nuestra web.</p>
        </div>
        <p style="text-align:center;font-size:12px;color:#9ca3af;">Casa Horizonte · {{ config('hotel.email') }}</p>
    </div>
</body>
</html>