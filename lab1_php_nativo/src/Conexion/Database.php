<?php

declare(strict_types=1);

namespace App\Conexion;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        self::loadEnvironment();
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $name = getenv('DB_NAME') ?: 'org_chart';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            // P: ¿Como se maneja un error de conexion? PDO lanza una excepcion y este
            // bloque la transforma en un RuntimeException sin mostrar credenciales.
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // P: ¿Que riesgo se evita? Con prepare() y emulacion desactivada,
                // un valor como '1 OR 1=1' queda como dato y no altera el SQL.
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('No fue posible conectar con la base de datos.', 0, $exception);
        }

        return self::$connection;
    }

    private static function loadEnvironment(): void
    {
        $environmentFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($environmentFile)) {
            return;
        }

        foreach (file($environmentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\\\"'");
            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }
}
