<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Prepara la base para la colección de /pruebas (Bloque C).
 *
 * Uso: php artisan db:seed --class=PruebasSeeder
 *
 * - Crea los usuarios juan y ana (normales) y alex (administrador) si no existen.
 *   Contraseña de los que crea: PRUEBAS_PASSWORD del .env, o "password123" si no está definida.
 *   A los que ya existen no se les cambia la contraseña.
 * - Crea las habitaciones 101 (Single, 30.00) y 111 (Family, 140.00), o las deja con esos datos si ya existen.
 * - Borra las reservas de ana, juan y alex en esas dos habitaciones desde ayer en adelante
 *   (lo que dejó una corrida anterior) y la habitación R-999 que puede crear la prueba CL-17.
 *   No toca reservas de otros usuarios ni reservas pasadas.
 */
class PruebasSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('PRUEBAS_PASSWORD', 'password123');

        $usuarios = [
            ['juan@casahorizonte.test', 'Juan', false],
            ['ana@casahorizonte.test', 'Ana', false],
            ['alex@casahorizonte.test', 'Alex', true],
        ];

        foreach ($usuarios as [$email, $nombre, $esAdmin]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $nombre, 'password' => $password],
            );

            if ($user->is_admin !== $esAdmin) {
                $user->forceFill(['is_admin' => $esAdmin])->save();
            }
        }

        $habitaciones = [
            [
                'room_number' => '101',
                'type' => 'Single',
                'price_per_night' => 30.00,
                'status' => 'available',
                'capacity' => 1,
                'description' => 'Habitación individual económica',
            ],
            [
                'room_number' => '111',
                'type' => 'Family',
                'price_per_night' => 140.00,
                'status' => 'available',
                'capacity' => 4,
                'description' => 'Familiar con 2 camas',
            ],
        ];

        foreach ($habitaciones as $datos) {
            Room::updateOrCreate(['room_number' => $datos['room_number']], $datos);
        }

        $idsHabitaciones = Room::whereIn('room_number', ['101', '111'])->pluck('id');
        $idsUsuarios = User::whereIn('email', array_column($usuarios, 0))->pluck('id');

        Reservation::whereIn('room_id', $idsHabitaciones)
            ->whereIn('user_id', $idsUsuarios)
            ->whereDate('check_out', '>=', now()->subDay()->toDateString())
            ->delete();

        // Resto de CL-17 si la API todavía permitía crear habitaciones a un usuario normal.
        Room::where('room_number', 'R-999')->delete();
    }
}
