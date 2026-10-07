<?php

namespace App\Services;

use App\Models\Reservation;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class ReservationDocument
{
    public static function token(int $id): string
    {
        return hash_hmac('sha256', (string) $id, config('app.key'));
    }

    public static function verifyUrl(Reservation $r): string
    {
        return route('reservations.verify', ['id' => $r->id, 'token' => self::token($r->id)]);
    }

    public function pdf(Reservation $r): string
    {
        $r->loadMissing(['room', 'user']);
        $qr = (new Builder(writer: new SvgWriter(), data: self::verifyUrl($r), size: 180, margin: 8))
            ->build()->getDataUri();
        $nights = (int) \Carbon\Carbon::parse($r->check_in)->diffInDays(\Carbon\Carbon::parse($r->check_out));

        $html = view('reservations.pdf', compact('r', 'qr', 'nights'))->render();
        $dompdf = new Dompdf((new Options())->set('isRemoteEnabled', false));
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
