<!doctype html><html lang="es"><head><meta charset="UTF-8"><style>
@page{margin:0}body{font-family:DejaVu Sans,Arial,sans-serif;color:#172a3d;margin:0}
.h{background:#17324d;color:#fff;padding:22px 34px}.b{font-size:22px;font-weight:bold;letter-spacing:2px}.s{color:#cbd8e2;font-size:10px;margin-top:5px}
.m{padding:24px 34px}table{width:100%;border-collapse:collapse;margin-top:14px}td{padding:9px 6px;border-bottom:1px solid #e3e8ee;font-size:12px}td.l{color:#6b7a89;width:35%}
.t{font-size:18px;font-weight:bold}
</style></head><body>
<div class="h"><div class="b">CASA HORIZONTE</div><div class="s">COMPROBANTE DE RESERVA #{{ $r->id }}</div></div>
<div class="m">
<table>
<tr><td class="l">Huésped</td><td>{{ $r->user->name }} ({{ $r->user->email }})</td></tr>
<tr><td class="l">Habitación</td><td>{{ $r->room->room_number }} · {{ $r->room->type }}</td></tr>
<tr><td class="l">Entrada</td><td>{{ $r->check_in }}</td></tr>
<tr><td class="l">Salida</td><td>{{ $r->check_out }}</td></tr>
<tr><td class="l">Noches</td><td>{{ $nights }}</td></tr>
<tr><td class="l">Estado</td><td>{{ ucfirst($r->status) }}</td></tr>
<tr><td class="l">Total</td><td class="t">${{ number_format((float) $r->total_price, 2) }}</td></tr>
</table>
<p style="margin-top:24px;font-size:11px;color:#6b7a89">Escanea el código QR para verificar la autenticidad de esta reserva.</p>
<img src="{{ $qr }}" width="150" height="150">
</div></body></html>
