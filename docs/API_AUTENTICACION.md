# Autenticación API con Laravel Sanctum

Documentación de la API REST de autenticación de **Casa Horizonte**. Permite que clientes externos (Postman, aplicaciones móviles, otros sistemas o un frontend separado) se registren, inicien sesión y consuman rutas protegidas mediante **tokens de acceso personal** emitidos por Laravel Sanctum.

La API vive dentro del mismo proyecto Laravel que la aplicación web: comparte modelos, base de datos y servidor. La web usa sesiones/cookies (`routes/web.php`) y la API usa tokens Bearer (`routes/api.php`).

---

## Índice

1. [Resumen de endpoints](#1-resumen-de-endpoints)
2. [Instalación y configuración de Sanctum](#2-instalación-y-configuración-de-sanctum)
3. [Archivos creados y modificados](#3-archivos-creados-y-modificados)
4. [Funcionamiento de los tokens](#4-funcionamiento-de-los-tokens)
5. [Referencia de endpoints](#5-referencia-de-endpoints)
6. [Protección de rutas con `auth:sanctum`](#6-protección-de-rutas-con-authsanctum)
7. [Cómo probar la API](#7-cómo-probar-la-api)
8. [Pruebas automatizadas](#8-pruebas-automatizadas)
9. [Almacenamiento de datos](#9-almacenamiento-de-datos)
10. [Seguridad](#10-seguridad)

---

## 1. Resumen de endpoints

URL base: `http://localhost:8000/api`

| Método | Ruta | Acceso | Descripción |
|---|---|---|---|
| `POST` | `/api/register` | Público | Registra un usuario y devuelve un token |
| `POST` | `/api/login` | Público | Valida credenciales y devuelve un token |
| `GET` | `/api/user` | Protegido (`auth:sanctum`) | Devuelve el usuario autenticado |
| `POST` | `/api/logout` | Protegido (`auth:sanctum`) | Revoca el token usado en la petición |

Todas las peticiones deben incluir la cabecera `Accept: application/json`. Las rutas protegidas requieren además `Authorization: Bearer <token>`.

---

## 2. Instalación y configuración de Sanctum

Pasos realizados sobre el proyecto existente (Laravel 13, PHP 8.3+):

```bash
# 1. Instalar el paquete
composer require laravel/sanctum

# 2. Publicar configuración y migración
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 3. Crear la tabla personal_access_tokens
php artisan migrate
```

Después se realizaron estos cambios de código:

**a) Habilitar tokens en el modelo `User`** — `app/Models/User.php`

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

El trait `HasApiTokens` agrega al usuario los métodos `createToken()`, `tokens()` y `currentAccessToken()`.

**b) Registrar el archivo de rutas API** — `bootstrap/app.php`

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // ← agregado
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

Laravel aplica automáticamente el prefijo `/api` a todas las rutas de `routes/api.php`. El proyecto ya tenía configurado en `bootstrap/app.php` que los errores de `api/*` se devuelvan en formato JSON.

### Configuración opcional (`config/sanctum.php`)

| Opción | Valor actual | Significado |
|---|---|---|
| `expiration` | `null` | Los tokens no expiran. Para que caduquen, indicar minutos (ej. `1440` = 24 h). |
| `guard` | `['web']` | Guard usado para autenticación con cookies (SPA). No afecta a los tokens. |
| `token_prefix` | `''` | Prefijo opcional para los tokens (útil para escáneres de secretos). |

---

## 3. Archivos creados y modificados

| Archivo | Tipo | Descripción |
|---|---|---|
| `composer.json` / `composer.lock` | Modificado | Dependencia `laravel/sanctum` ^4.4 |
| `config/sanctum.php` | Creado | Configuración de Sanctum |
| `database/migrations/2026_11_10_205114_create_personal_access_tokens_table.php` | Creado | Tabla de tokens |
| `app/Models/User.php` | Modificado | Trait `HasApiTokens` |
| `bootstrap/app.php` | Modificado | Registro de `routes/api.php` |
| `routes/api.php` | Creado | Definición de rutas de la API |
| `app/Http/Controllers/Api/AuthController.php` | Creado | Lógica de registro, login, usuario y logout |
| `tests/Feature/Api/SanctumAuthTest.php` | Creado | Pruebas automatizadas de la API |

---

## 4. Funcionamiento de los tokens

```
 Cliente                                   Servidor (Laravel + Sanctum)
   │                                                │
   │  POST /api/login {email, password}             │
   │ ─────────────────────────────────────────────► │ 1. Valida credenciales (Hash::check)
   │                                                │ 2. createToken() → guarda hash SHA-256
   │  200 { token: "5|AbC123..." }                  │    en personal_access_tokens
   │ ◄───────────────────────────────────────────── │
   │                                                │
   │  GET /api/user                                 │
   │  Authorization: Bearer 5|AbC123...             │
   │ ─────────────────────────────────────────────► │ 3. auth:sanctum busca el registro id=5
   │                                                │    y compara el hash del token
   │  200 { id, name, email, ... }                  │
   │ ◄───────────────────────────────────────────── │
   │                                                │
   │  POST /api/logout  (mismo Bearer)              │
   │ ─────────────────────────────────────────────► │ 4. Elimina el registro del token
   │  200 { message: "Sesión cerrada..." }          │
   │ ◄───────────────────────────────────────────── │
   │                                                │
   │  GET /api/user  (mismo Bearer)                 │
   │ ─────────────────────────────────────────────► │ 5. Token inexistente → 401
   │  401 { message: "Unauthenticated." }           │
```

- El token tiene el formato `<id>|<cadena aleatoria>`. El número es el `id` del registro en la base de datos.
- El token en texto plano **solo se muestra una vez**, en la respuesta de registro o login. En la base de datos se almacena únicamente su hash.
- Un usuario puede tener varios tokens activos a la vez (uno por dispositivo). El campo opcional `device_name` permite identificarlos.

---

## 5. Referencia de endpoints

### 5.1 `POST /api/register`

Crea un usuario nuevo y devuelve un token de acceso.

**Cuerpo (JSON):**

| Campo | Tipo | Reglas |
|---|---|---|
| `name` | string | Requerido, máx. 255 |
| `email` | string | Requerido, email válido, en minúsculas, único en `users` |
| `password` | string | Requerido, mín. 8 caracteres, debe coincidir con `password_confirmation` |
| `password_confirmation` | string | Requerido |
| `device_name` | string | Opcional, nombre del token (por defecto `api-token`) |

**Ejemplo:**

```json
{
  "name": "Ana López",
  "email": "ana@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta `201 Created`:**

```json
{
  "message": "Usuario registrado correctamente.",
  "user": { "id": 3, "name": "Ana López", "email": "ana@example.com", "created_at": "...", "updated_at": "..." },
  "token": "3|Xk9aP2...",
  "token_type": "Bearer"
}
```

**Respuesta `422 Unprocessable Content`** (datos inválidos o email repetido):

```json
{
  "message": "The email has already been taken.",
  "errors": { "email": ["The email has already been taken."] }
}
```

---

### 5.2 `POST /api/login`

Valida las credenciales y emite un token nuevo.

**Cuerpo (JSON):**

| Campo | Tipo | Reglas |
|---|---|---|
| `email` | string | Requerido, email válido |
| `password` | string | Requerido |
| `device_name` | string | Opcional |

**Respuesta `200 OK`:**

```json
{
  "message": "Inicio de sesión exitoso.",
  "user": { "id": 3, "name": "Ana López", "email": "ana@example.com", "is_admin": false, "...": "..." },
  "token": "4|Qw7rT1...",
  "token_type": "Bearer"
}
```

**Respuesta `422`** (credenciales incorrectas):

```json
{
  "message": "Las credenciales proporcionadas son incorrectas.",
  "errors": { "email": ["Las credenciales proporcionadas son incorrectas."] }
}
```

---

### 5.3 `GET /api/user` 🔒

Devuelve los datos del usuario dueño del token.

**Cabeceras:** `Authorization: Bearer <token>`

**Respuesta `200 OK`:**

```json
{ "id": 3, "name": "Ana López", "email": "ana@example.com", "email_verified_at": null, "is_admin": false, "created_at": "...", "updated_at": "..." }
```

**Respuesta `401 Unauthorized`** (sin token, token inválido o revocado):

```json
{ "message": "Unauthenticated." }
```

---

### 5.4 `POST /api/logout` 🔒

Revoca **solo** el token usado en la petición. Los demás tokens del usuario (otros dispositivos) siguen activos.

**Cabeceras:** `Authorization: Bearer <token>`

**Respuesta `200 OK`:**

```json
{ "message": "Sesión cerrada correctamente." }
```

---

### Códigos de estado

| Código | Cuándo ocurre |
|---|---|
| `200` | Petición exitosa |
| `201` | Usuario registrado |
| `401` | Falta el token, es inválido o fue revocado |
| `422` | Error de validación o credenciales incorrectas |
| `429` | Demasiados intentos (más de 10 por minuto en register/login) |

---

## 6. Protección de rutas con `auth:sanctum`

Contenido de `routes/api.php`:

```php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas (limitadas a 10 peticiones por minuto)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rutas protegidas: requieren Authorization: Bearer <token>
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

El middleware `auth:sanctum` lee la cabecera `Authorization`, busca el token en la base de datos y, si es válido, deja disponible al usuario en `$request->user()`. Si no lo es, responde `401`.

**Para proteger un endpoint nuevo**, basta con declararlo dentro del grupo:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ejemplo de futuros endpoints
    // Route::get('/reservations', [Api\ReservationController::class, 'index']);
});
```

---

## 7. Cómo probar la API

### 7.1 Levantar el servidor

```bash
php artisan serve
```

La API queda disponible en `http://localhost:8000/api`.

### 7.2 Con Postman

1. Crear una petición `POST http://localhost:8000/api/login`.
2. En **Headers** agregar `Accept: application/json`.
3. En **Body → raw → JSON** enviar `{ "email": "...", "password": "..." }`.
4. Copiar el valor de `token` de la respuesta.
5. Crear `GET http://localhost:8000/api/user`; en **Authorization** elegir **Bearer Token** y pegar el token.
6. Para cerrar sesión, `POST http://localhost:8000/api/logout` con el mismo Bearer Token.

### 7.3 Con PowerShell

```powershell
$api = "http://localhost:8000/api"
$h = @{ Accept = "application/json" }

# Registro
Invoke-RestMethod -Method Post "$api/register" -Headers $h -Body @{
  name = "Ana"; email = "ana@example.com"; password = "password123"; password_confirmation = "password123"
}

# Login y guardar token
$r = Invoke-RestMethod -Method Post "$api/login" -Headers $h -Body @{ email = "ana@example.com"; password = "password123" }
$h.Authorization = "Bearer $($r.token)"

# Ruta protegida
Invoke-RestMethod "$api/user" -Headers $h

# Logout
Invoke-RestMethod -Method Post "$api/logout" -Headers $h
```

### 7.4 Con curl (Linux / macOS / Git Bash)

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -d "email=ana@example.com" -d "password=password123"

# Ruta protegida (reemplazar TOKEN)
curl http://localhost:8000/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer TOKEN"
```

> En PowerShell, `curl` es un alias de `Invoke-WebRequest`; usar `curl.exe` para el curl real.

---

## 8. Pruebas automatizadas

Archivo: `tests/Feature/Api/SanctumAuthTest.php`

| Prueba | Verifica |
|---|---|
| `test_user_can_register_and_receives_token` | El registro crea el usuario, devuelve token y no expone la contraseña |
| `test_register_validates_input` | Datos inválidos devuelven `422` con los errores de cada campo |
| `test_user_can_login_with_valid_credentials` | Login correcto devuelve token |
| `test_login_fails_with_invalid_credentials` | Contraseña incorrecta devuelve `422` |
| `test_protected_route_requires_token` | `/api/user` y `/api/logout` devuelven `401` sin token |
| `test_protected_route_works_with_token` | `/api/user` devuelve el usuario con un token válido |
| `test_logout_revokes_current_token` | Tras el logout el token se elimina y deja de funcionar |

Ejecutar:

```bash
php artisan test --filter=SanctumAuthTest   # solo la API
php artisan test                            # suite completa
```

Resultado: 7 pruebas de la API y 41 en total, todas superadas.

---

## 9. Almacenamiento de datos

La API usa la misma base de datos configurada en `.env` (`DB_CONNECTION`), compartida con la aplicación web. Las tablas involucradas son:

### `users`

Usuarios registrados (tanto por la web como por la API). La contraseña se guarda cifrada con **bcrypt** mediante el cast `'password' => 'hashed'` del modelo.

### `personal_access_tokens`

Un registro por cada token emitido.

| Columna | Descripción |
|---|---|
| `id` | Identificador; es el número antes del `\|` en el token |
| `tokenable_type` / `tokenable_id` | Dueño del token (`App\Models\User` + id del usuario) |
| `name` | Nombre del token (`device_name` o `api-token`) |
| `token` | Hash SHA-256 del token (nunca el valor en texto plano) |
| `abilities` | Permisos del token (por defecto `["*"]`, todos) |
| `last_used_at` | Última vez que se usó |
| `expires_at` | Fecha de expiración (`null` = no expira) |
| `created_at` / `updated_at` | Marcas de tiempo |

Consultar desde la terminal:

```bash
php artisan tinker
>>> App\Models\User::all();
>>> Laravel\Sanctum\PersonalAccessToken::all();
```

---

## 10. Seguridad

- **Contraseñas cifradas** con bcrypt; nunca se devuelven en las respuestas (`#[Hidden(['password', 'remember_token'])]` en el modelo).
- **Tokens almacenados como hash** SHA-256: si la base de datos se filtra, los tokens no son utilizables.
- **Limitación de intentos** (`throttle:10,1`) en registro y login para mitigar ataques de fuerza bruta.
- **Mensaje de error genérico** en login, que no revela si el email existe.
- **Revocación por dispositivo**: el logout elimina solo el token actual.
- **Validación estricta** de entrada (email único, contraseña confirmada con la política `Password::defaults()`).
- Recomendado en producción: servir la API solo por **HTTPS** y definir `expiration` en `config/sanctum.php`.
