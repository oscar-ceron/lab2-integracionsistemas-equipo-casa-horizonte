<?php

declare(strict_types=1);

namespace App\Controladores;

use App\Repositorios\RoomRepository;
use InvalidArgumentException;

final class RoomController
{
    public function __construct(private readonly RoomRepository $rooms)
    {
    }

    public function index(): array
    {
        return $this->rooms->all();
    }

    public function save(array $input, ?int $id = null): int|bool
    {
        $data = $this->validatedData($input);

        return $id === null
            ? $this->rooms->create($data)
            : $this->rooms->update($id, $data);
    }

    public function edit(int $id): ?array
    {
        return $this->rooms->find($id);
    }

    public function remove(int $id): bool
    {
        return $this->rooms->delete($id);
    }

    private function validatedData(array $input): array
    {
        // P: ¿Que pasa con un campo vacio? Se lanza InvalidArgumentException y no se
        // llama al repositorio. Tambien se rechazan capacidad menor que uno y precio negativo.
        $number = trim((string) ($input['number'] ?? ''));
        $type = trim((string) ($input['type'] ?? ''));
        $capacity = filter_var($input['capacity'] ?? null, FILTER_VALIDATE_INT);
        $price = filter_var($input['price'] ?? null, FILTER_VALIDATE_FLOAT);
        $description = trim((string) ($input['description'] ?? ''));

        if ($number === '' || $type === '' || $capacity === false || $capacity < 1 || $price === false || $price < 0) {
            throw new InvalidArgumentException('Completa los campos obligatorios con valores validos.');
        }

        return [
            'number' => $number,
            'type' => $type,
            'capacity' => $capacity,
            'price' => $price,
            'description' => $description,
        ];
    }
}
