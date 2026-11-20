# Registro de cambios – Casa Horizonte

## Bloque D – Mantenimiento correctivo y evolutivo

**Proyecto:** Casa Horizonte – GUÍA DE LABORATORIO III
**Framework:** Laravel 13
**Rama:** `main`
**Fecha de documentación:** 19 de noviembre de 2026

## 1. Mantenimiento correctivo

### MC-001 – Aislamiento de reservas por usuario autenticado

**Problema identificado:**
Era necesario garantizar que cada usuario pudiera consultar y administrar únicamente sus propias reservas, evitando el acceso no autorizado a información de otros usuarios.

**Cambio realizado:**
Se incorporaron controles de acceso basados en el usuario autenticado y sus relaciones con las reservas. También se contemplaron los permisos correspondientes para los administradores.

**Archivos relacionados:**
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/ReservationController.php`
- `app/Models/Reservation.php`
- `app/Models/User.php`
- Pruebas de aislamiento de reservas.

**Verificación:**
Se realizaron pruebas para comprobar el aislamiento de reservas, los permisos administrativos y las operaciones sobre las reservas.

**Commit relacionado:** `6135c7a`.

### MC-002 – Corrección de respuestas y controles de seguridad en la API

**Problema identificado:**
Se necesitaba mejorar el comportamiento de los endpoints de la API, las respuestas HTTP y la protección frente al acceso a recursos de otros usuarios.

**Cambio realizado:**
Se ajustaron los controladores para aplicar restricciones de acceso, validar operaciones y utilizar respuestas HTTP apropiadas, incluyendo la respuesta `204 No Content` para operaciones de eliminación.

**Archivos relacionados:**
- `app/Http/Controllers/Api/RoomApiController.php`
- `app/Http/Controllers/Api/ReservationApiController.php`
- Modelos y rutas de la API.

**Commit relacionado:** `2989aa0`.

### MC-003 – Manejo de errores y validación de respuestas JSON

**Problema identificado:**
Era necesario estandarizar el tratamiento de errores de la API y evitar respuestas con información técnica innecesaria.

**Cambio realizado:**
Se incorporó manejo global de errores en la aplicación y se realizaron pruebas para comprobar las respuestas ante errores de validación, autorización y otros casos límite.

**Archivos relacionados:**
- `bootstrap/app.php`
- Controladores y clases de validación.
- Pruebas automatizadas.

**Commit relacionado:** `8cd0e16`.

## 2. Mantenimiento evolutivo

### ME-001 – Gestión de cancelaciones de reservas

Se incorporaron funcionalidades relacionadas con la cancelación de reservas, incluyendo los cambios correspondientes en el modelo, eventos, notificaciones y vistas de la aplicación.

**Elementos relacionados:**
- Evento `ReservationCancelled.php`.
- Modelo `Reservation`.
- Plantilla de correo electrónico.
- Componentes de la lista de reservas.

### ME-002 – Autenticación mediante Laravel Sanctum

Se incorporó autenticación por tokens para permitir el acceso seguro a los endpoints de la API.

**Funcionalidades:**
- Registro de usuarios.
- Inicio de sesión.
- Generación de tokens.
- Consulta del usuario autenticado.
- Cierre de sesión y revocación del token.

**Archivos relacionados:**
- Controlador de autenticación.
- `routes/api.php`
- `bootstrap/app.php`
- Modelo `User`.
- Configuración y migraciones de Sanctum.

**Commit relacionado:** `994e360`.

### ME-003 – Implementación de API Resources

Se incorporaron recursos para estructurar y estandarizar las respuestas JSON de habitaciones y reservas.

**Archivos relacionados:**
- `app/Http/Resources/RoomResource.php`
- `app/Http/Resources/ReservationResource.php`
- Controladores de la API.
- `routes/api.php`

**Commit relacionado:** `dda4c57`.

### ME-004 – Validación mediante Form Requests y ReservationService

Se centralizó la validación de las solicitudes relacionadas con las reservas y se incorporó un servicio para gestionar la lógica de negocio.

**Elementos relacionados:**
- `StoreReservationRequest`
- `CancelReservationRequest`
- `ReservationService`

Se incluyeron validaciones de fechas, habitaciones, disponibilidad y datos de las reservas.

**Commit relacionado:** `7038c72`.

### ME-005 – Operaciones transaccionales en la creación de reservas

Se incorporó una operación mediante `DB::transaction()` para mantener la consistencia de los datos durante la creación de reservas que involucran más de una operación en la base de datos.

**Archivo relacionado:**
- `app/Services/ReservationService.php`

**Commit relacionado:** `a94b9f9`.

## 3. Pruebas y validación

Se ejecutaron pruebas automatizadas para verificar el funcionamiento general de la aplicación y sus funcionalidades.

**Comando utilizado:**

```bash
php artisan test
```

**Último resultado registrado:**
- 45 pruebas aprobadas.
- 140 aserciones.
- 0 pruebas fallidas.

También se realizaron pruebas de API mediante Bruno para comprobar:
- Autenticación y autorización.
- Operaciones CRUD.
- Validación de datos.
- Respuestas HTTP.
- Aislamiento de información entre usuarios.
- Casos límite y manejo de errores.

Se cuenta con una bitácora de pruebas y evidencias asociadas a los casos documentados.

## 4. Documentación técnica

Se consolidó la documentación del proyecto para facilitar su instalación, comprensión, mantenimiento y verificación.

| Archivo | Descripción |
|---|---|
| `README.md` | Información general, instalación, configuración y funcionalidades del proyecto. |
| `docs/API_AUTENTICACION.md` | Documentación de la autenticación mediante Laravel Sanctum. |
| `docs/Anexo-A.md` | Comparación de Laravel con otro framework. |
| `docs/FICHA_USO_CRITICO_IA.md` | Registro y evaluación del uso de inteligencia artificial. |
| `docs/Bitacora Pruebas CasaHorizonte.xlsx` | Registro de casos de prueba y resultados. |
| `CAMBIOS.md` | Registro de mantenimiento correctivo y evolutivo. |

## 5. Control de versiones

Los cambios se integraron en la rama `main` mediante commits y procesos de integración del equipo.

Entre los commits relacionados con las mejoras documentadas se encuentran:

- `2989aa0` – Ajustes de controladores API y respuestas HTTP.
- `7038c72` – Form Requests y ReservationService.
- `45eb3b5` – Integración de Form Requests y ReservationService.
- `8cd0e16` – Pruebas y manejo de errores.
- `a94b9f9` – Implementación de una operación con `DB::transaction()`.
- `0e95837` – Documentación del Bloque D.

**Nota:** Este documento consolida los cambios técnicos y documentales integrados en el proyecto. La relación de cambios no implica que todas las modificaciones hayan sido realizadas por una sola persona; se reconocen las responsabilidades y aportes de los integrantes del equipo.