# Registro de cambios – Casa Horizonte

## Bloque D – Mantenimiento correctivo y evolutivo

**Proyecto:** Casa Horizonte – Laboratorio II
**Framework:** Laravel 13
**Rama:** `main`
**Fecha de documentación:** 19 de noviembre de 2026

## 1. Mantenimiento correctivo

### MC-001 – Aislamiento de reservas por usuario autenticado

**Problema identificado:**

Era necesario garantizar que cada usuario pudiera consultar y gestionar únicamente sus propias reservas, evitando el acceso no autorizado a información perteneciente a otras personas.

**Cambio realizado:**

Se ajustaron los controladores y las relaciones entre los modelos `User` y `Reservation` para asociar las operaciones de reservas con el usuario autenticado. También se incorporaron controles para diferenciar los permisos de usuarios y administradores.

**Archivos relacionados:**
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/ReservationController.php`
- `app/Models/Reservation.php`
- `app/Models/User.php`
- `tests/Feature/ReservationIsolationTest.php`

**Verificación:**

Se incorporaron pruebas automatizadas para comprobar el aislamiento de reservas, el acceso administrativo y las restricciones sobre las operaciones de cancelación.

**Commit de referencia:** `6135c7a`

## 2. Mantenimiento evolutivo

### ME-001 – Gestión de cancelaciones de reservas

**Mejora incorporada:**

Se amplió la gestión de reservaciones mediante el registro de información relacionada con las cancelaciones, incluyendo datos asociados a su procesamiento y notificaciones por correo electrónico.

**Archivos relacionados:**
- `app/Mail/ReservationCancelled.php`
- `app/Models/Reservation.php`
- `resources/views/emails/cancelled.blade.php`
- `resources/views/partials/reservations-list.blade.php`

### ME-002 – Incorporación de autenticación API con Laravel Sanctum

**Mejora incorporada:**

Se implementó autenticación para los servicios API utilizando Laravel Sanctum, incorporando operaciones de registro, inicio de sesión y administración de tokens de acceso.

**Archivos relacionados:**
- `app/Http/Controllers/Api/AuthController.php`
- `config/sanctum.php`
- `routes/api.php`
- `bootstrap/app.php`
- `database/migrations/2026_11_10_205114_create_personal_access_tokens_table.php`

**Commit de referencia:** `994e360`

### ME-003 – Implementación de API Resources

**Mejora incorporada:**

Se incorporaron recursos API para habitaciones y reservaciones con el propósito de organizar y estandarizar la información devuelta por los endpoints.

**Archivos relacionados:**
- `app/Http/Resources/RoomResource.php`
- `app/Http/Resources/ReservationResource.php`
- `app/Http/Controllers/Api/RoomApiController.php`
- `app/Http/Controllers/Api/ReservationApiController.php`
- `routes/api.php`

**Commit de referencia:** `dda4c57`

## 3. Pruebas y validación

Se utilizaron pruebas automatizadas para verificar el funcionamiento de la autenticación, las operaciones del sistema y el aislamiento de reservas.

En una ejecución de validación del proyecto se obtuvieron los siguientes resultados:

- 41 pruebas aprobadas.
- 117 aserciones satisfactorias.
- Pruebas de autenticación API con Sanctum.
- Pruebas de aislamiento y administración de reservas.

## 4. Documentación técnica

Como parte de la evolución del proyecto se incorporaron documentos para facilitar la comprensión y utilización de los servicios:

- `docs/API_AUTENTICACION.md`: documentación de la autenticación API.
- `docs/Anexo-A.md`: comparación técnica entre Laravel y Django.
- `README.md`: instrucciones generales de instalación, configuración y ejecución.

## 5. Control de versiones

Los cambios documentados se encuentran integrados en la rama `main` del repositorio del equipo.

**Último commit de integración:** `ac2807a` – Merge origin/main: integrar autenticación y API Resources.

---

**Nota:** Este registro consolida cambios identificados en el historial del repositorio. No representa una atribución individual de todas las modificaciones, sino la documentación técnica del trabajo integrado en el proyecto.