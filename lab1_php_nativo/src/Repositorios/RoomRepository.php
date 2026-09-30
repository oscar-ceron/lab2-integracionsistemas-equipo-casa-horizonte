<?php

declare(strict_types=1);

namespace App\Repositorios;

use App\Contratos\CrudRepository;
use App\Modelos\Room;
use PDO;

final class RoomRepository implements CrudRepository
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function all(): array
    {
        $statement = $this->database->query(
            'SELECT id, number, type, capacity, price, description FROM rooms ORDER BY number'
        );

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        // P: ¿Que pasaria sin parametros? Un id manipulado podria cambiar la condicion
        // WHERE y exponer registros. El parametro nombrado lo trata como dato.
        $statement = $this->database->prepare(
            'SELECT id, number, type, capacity, price, description FROM rooms WHERE id = :id'
        );
        $statement->execute(['id' => $id]);
        $room = $statement->fetch();

        return $room ?: null;
    }

    public function create(array $data): int
    {
        // INSERT usa prepare/execute: los valores del formulario no forman parte del SQL.
        $statement = $this->database->prepare(
            'INSERT INTO rooms (number, type, capacity, price, description, created_at, updated_at)
             VALUES (:number, :type, :capacity, :price, :description, NOW(), NOW())'
        );
        $statement->execute($data);

        return (int) $this->database->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        // La misma estrategia protege la edicion y mantiene separado el SQL de los datos.
        $data['id'] = $id;
        $statement = $this->database->prepare(
            'UPDATE rooms
             SET number = :number, type = :type, capacity = :capacity, price = :price,
                 description = :description, updated_at = NOW()
             WHERE id = :id'
        );

        return $statement->execute($data);
    }

    public function delete(int $id): bool
    {
        // DELETE tambien recibe el id como parametro, nunca mediante concatenacion de texto.
        $statement = $this->database->prepare('DELETE FROM rooms WHERE id = :id');

        return $statement->execute(['id' => $id]);
    }

    public function toEntity(array $room): Room
    {
        return new Room(
            $room['number'],
            $room['type'] ?? 'Sin tipo',
            (int) $room['capacity'],
            (float) $room['price'],
            $room['description'] ?? ''
        );
    }
}
