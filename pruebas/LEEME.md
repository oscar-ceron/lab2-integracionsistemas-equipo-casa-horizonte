# Colección de pruebas · API de Casa Horizonte (Bruno)

Bloque C · Pruebas y manejo de errores. La bitácora con los esperados está en `docs/` (Bitacora_Pruebas_CasaHorizonte.xlsx).

## Antes de la primera corrida

1. Levantar la app: `php artisan serve` (la colección usa `http://127.0.0.1:8000/api`).
2. Preparar los datos de prueba: `php artisan db:seed --class=PruebasSeeder`
   (crea, si faltan, los usuarios ana, juan y alex@casahorizonte.test y las habitaciones 101 y 111).
3. En Bruno: Open Collection → esta carpeta → entorno **Local** → escribir el valor de la variable secreta `password`
   (contraseña de los usuarios de prueba; no se guarda en el repositorio).

## Cada corrida completa

1. Repetir el paso 2 (el seeder borra las reservas de prueba de ana, juan y alex en las habitaciones 101 y 111).
2. Run de la colección completa, sin modificar ninguna prueba.
   - Si aparece 429 en un login: esperar un minuto (límite de 10 intentos por minuto).

## Orden y contenido

0 Preparación (logins, ubicar habitaciones, reserva base) · 1 Casos exitosos · 2 Valores límite y particiones ·
3 Casos negativos · 4 Seguridad entre usuarios · 5 Idempotencia · 6 Extras (sesión y token).
Las fechas se calculan solas relativas a hoy (UTC).
