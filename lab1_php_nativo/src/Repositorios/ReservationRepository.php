<?php

declare(strict_types=1);

namespace App\Repositorios;

use PDO;
use RuntimeException;

final class ReservationRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function availableRooms(): array
    {
        $statement = $this->database->query(
            'SELECT id, number, type, capacity, price FROM rooms ORDER BY number'
        );

        return $statement->fetchAll();
    }

    public function all(): array
    {
        $statement = $this->database->query(
            'SELECT r.id, r.start_date, r.end_date, r.status, r.total,
                    rm.number AS room_number, rm.type AS room_type,
                    u.name AS guest_name, u.email AS guest_email
             FROM reservations r
             INNER JOIN rooms rm ON rm.id = r.room_id
             LEFT JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC, r.id DESC'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare(
            'SELECT r.id, r.room_id, r.start_date, r.end_date, r.status, r.total,
                    rm.number AS room_number, rm.type AS room_type,
                    u.name AS guest_name, u.email AS guest_email
             FROM reservations r
             INNER JOIN rooms rm ON rm.id = r.room_id
             LEFT JOIN users u ON u.id = r.user_id
             WHERE r.id = :id'
        );
        $statement->execute(['id' => $id]);
        $reservation = $statement->fetch();

        return $reservation ?: null;
    }

    public function findRoom(int $roomId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id, number, type, price FROM rooms WHERE id = :id'
        );
        $statement->execute(['id' => $roomId]);
        $room = $statement->fetch();

        return $room ?: null;
    }

    public function hasConflict(int $roomId, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*) FROM reservations
             WHERE room_id = :room_id AND status <> :cancelled
               AND start_date < :end_date AND end_date > :start_date
               AND (:exclude_id_check IS NULL OR id <> :exclude_id)'
        );
        $statement->execute([
            'room_id' => $roomId,
            'cancelled' => 'cancelled',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'exclude_id_check' => $excludeId,
            'exclude_id' => $excludeId,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data, array $guest): int
    {
        // La transaccion mantiene consistentes el usuario y su reserva: si una operacion
        // falla, rollBack() revierte ambas y se informa el error al controlador.
        $this->database->beginTransaction();

        try {
            $userStatement = $this->database->prepare(
                'INSERT INTO users (name, email, created_at, updated_at)
                 VALUES (:name, :email, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id), name = VALUES(name), updated_at = NOW()'
            );
            $userStatement->execute($guest);
            $userId = (int) $this->database->lastInsertId();

            $reservationStatement = $this->database->prepare(
                'INSERT INTO reservations (room_id, user_id, start_date, end_date, status, total, created_at, updated_at)
                 VALUES (:room_id, :user_id, :start_date, :end_date, :status, :total, NOW(), NOW())'
            );
            $reservationStatement->execute([
                'room_id' => $data['room_id'],
                'user_id' => $userId,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'confirmed',
                'total' => $data['total'],
            ]);
            $reservationId = (int) $this->database->lastInsertId();
            $this->database->commit();

            return $reservationId;
        } catch (\Throwable $exception) {
            $this->database->rollBack();
            throw new RuntimeException('No fue posible guardar la reserva.', 0, $exception);
        }
    }

    public function cancel(int $id): bool
    {
        // Cancelar conserva el registro para mantener el historial de la reservacion.
        $statement = $this->database->prepare(
            'UPDATE reservations SET status = :status, updated_at = NOW() WHERE id = :id'
        );

        return $statement->execute(['status' => 'cancelled', 'id' => $id]);
    }

    public function update(int $id, array $data): bool
    {
        // La edicion solo cambia datos propios de la reserva; el huesped se mantiene intacto.
        $data['id'] = $id;
        $statement = $this->database->prepare(
            'UPDATE reservations
             SET room_id = :room_id, start_date = :start_date, end_date = :end_date,
                 status = :status, total = :total, updated_at = NOW()
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $data['id'],
            'room_id' => $data['room_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'] ?? 'pending',
            'total' => $data['total'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        // DELETE elimina definitivamente; para la operacion habitual se recomienda cancel().
        $statement = $this->database->prepare('DELETE FROM reservations WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }
}
