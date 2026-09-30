<?php

declare(strict_types=1);

namespace App\Controladores;

use App\Repositorios\ReservationRepository;
use App\Servicios\Mailer;
use DateTimeImmutable;
use InvalidArgumentException;

final class ReservationController
{
    public function __construct(
        private readonly ReservationRepository $reservations,
        private readonly Mailer $mailer
    )
    {
    }

    public function rooms(): array
    {
        return $this->reservations->availableRooms();
    }

    public function index(): array
    {
        return $this->reservations->all();
    }

    public function save(array $input): int
    {
        $roomId = filter_var($input['room_id'] ?? null, FILTER_VALIDATE_INT);
        $guestName = trim((string) ($input['guest_name'] ?? ''));
        $guestEmail = trim((string) ($input['guest_email'] ?? ''));
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));

        // Antes de insertar se validan obligatorios, correo, fechas, existencia y disponibilidad;
        // un dato invalido detiene el proceso antes de tocar la base de datos.
        if ($roomId === false || $roomId < 1 || $guestName === '' || !filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ingresa los datos del huesped y selecciona una habitacion.');
        }

        $start = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);
        $today = new DateTimeImmutable('today');

        if (!$start || !$end || $start->format('Y-m-d') !== $startDate || $end->format('Y-m-d') !== $endDate || $start < $today || $end <= $start) {
            throw new InvalidArgumentException('Las fechas deben ser validas, futuras y tener salida posterior a la entrada.');
        }

        $room = $this->reservations->findRoom($roomId);
        if ($room === null) {
            throw new InvalidArgumentException('La habitacion seleccionada no existe.');
        }

        if ($this->reservations->hasConflict($roomId, $startDate, $endDate)) {
            throw new InvalidArgumentException('La habitacion ya esta reservada en esas fechas.');
        }

        $nights = (int) $start->diff($end)->days;

        $reservationId = $this->reservations->create(
            [
                'room_id' => $roomId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total' => $nights * (float) $room['price'],
            ],
            ['name' => $guestName, 'email' => $guestEmail]
        );

        try {
            $this->mailer->sendReservationConfirmation(
                $guestEmail,
                $guestName,
                (string) $room['number'],
                (string) $room['type'],
                $startDate,
                $endDate,
                $nights * (float) $room['price'],
                $reservationId
            );
        } catch (\RuntimeException $exception) {
        }

        return $reservationId;
    }

    public function cancel(int $id): bool
    {
        return $this->reservations->cancel($id);
    }

    public function update(int $id, array $input): bool
    {
        // La actualizacion reutiliza las mismas reglas para evitar reservas invalidas.
        $reservation = $this->reservations->find($id);
        if ($reservation === null) {
            throw new InvalidArgumentException('La reserva seleccionada no existe.');
        }

        $roomId = filter_var($input['room_id'] ?? null, FILTER_VALIDATE_INT);
        $startDate = trim((string) ($input['start_date'] ?? ''));
        $endDate = trim((string) ($input['end_date'] ?? ''));
        $status = trim((string) ($input['status'] ?? $reservation['status']));
        $start = DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
        $end = DateTimeImmutable::createFromFormat('Y-m-d', $endDate);

        if ($roomId === false || $roomId < 1 || !$start || !$end
            || $start->format('Y-m-d') !== $startDate || $end->format('Y-m-d') !== $endDate
            || $start < new DateTimeImmutable('today') || $end <= $start
            || !in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            throw new InvalidArgumentException('Los datos de la reserva no son validos.');
        }

        $room = $this->reservations->findRoom($roomId);
        if ($room === null) {
            throw new InvalidArgumentException('La habitacion seleccionada no existe.');
        }

        if ($this->reservations->hasConflict($roomId, $startDate, $endDate, $id)) {
            throw new InvalidArgumentException('La habitacion ya esta reservada en esas fechas.');
        }

        $nights = (int) $start->diff($end)->days;
        return $this->reservations->update($id, [
            'room_id' => $roomId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'total' => $nights * (float) $room['price'],
        ]);
    }

    public function delete(int $id): bool
    {
        return $this->reservations->delete($id);
    }
}
