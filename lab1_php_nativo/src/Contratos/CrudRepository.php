<?php

declare(strict_types=1);

namespace App\Contratos;

interface CrudRepository
{
    // P: ¿Por que interfaz en vez de solo herencia? R: obliga a cumplir el contrato
    // CRUD, permite varias implementaciones y evita acoplar el controlador a una clase.
    // Un repositorio nuevo solo debe cumplir estas operaciones CRUD.
    public function all(): array;

    public function find(int $id): ?array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
