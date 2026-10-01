# Casa Horizonte - Laboratorio II (Migración a Laravel)

Aplicación web de hospedaje para administrar habitaciones y reservas, migrada al framework Laravel. Este proyecto implementa un patrón MVC, modelo de persistencia con Eloquent ORM, vistas dinámicas con Blade, validación de datos a través de Form Requests y un sistema de autenticación de usuarios para garantizar el aislamiento de datos.

## Equipo y evaluación
- **Nombre del equipo:** Equipo Casa Horizonte
- **Integrantes y porcentaje de participación individual:**
  - Allison Gabriela Garcia Ponce - Carnet: GP-64074-23 - Participación: 100%
  - Brandon Misael Rodríguez Ayala - Carnet: RA-60677-21 - Participación: 100%
  - Joseline Abigail Torres Jurado - Carnet: TJ-64893-23 - Participación: 100%
  - María de los Ángeles Acosta Ardón - Carnet: AA-64964-23 - Participación: 100%
  - Oscar Alexander Cerón Hernandez - Carnet: CH-64069-23 - Participación: 100%


## Requisitos del entorno

- PHP 8.2 o superior.
- Composer.
- MySQL 8 o MariaDB.

## Instalación y Configuración Local

1. Clonar el repositorio y acceder al directorio del proyecto:
   ```bash
   git clone https://github.com/oscar-ceron/lab2-integracionsistemas-equipo-casa-horizonte.git
   cd lab2-integracionsistemas-equipo-casa-horizonte
   ```

2. Instalar las dependencias del proyecto:
   ```bash
   composer install
   ```

3. Configurar las variables de entorno:
   Copiar el archivo de ejemplo para crear tu propio `.env`:
   ```bash
   cp .env.example .env
   ```
   Generar la clave secreta de la aplicación:
   ```bash
   php artisan key:generate
   ```

4. Configurar la base de datos:
   Abre el archivo `.env` recién creado y actualiza las credenciales de tu conexión local a MySQL:
   ```text
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=taskboard
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Ejecutar las migraciones para crear el esquema en la base de datos:
   ```bash
   php artisan migrate
   ```

6. Levantar el servidor de desarrollo local:
   ```bash
   php artisan serve
   ```
   La aplicación estará disponible en `http://localhost:8000`.

## Funcionalidades Principales

- **Autenticación y Sesiones:** Sistema de registro e inicio de sesión. Cada usuario visualiza y gestiona únicamente las reservas e información que le pertenece.
- **CRUD Completo:** Operaciones de crear, listar, editar y eliminar aplicadas sobre las habitaciones y las reservas mediante Eloquent ORM.
- **Validación de Formularios:** Uso de *Form Requests* para rechazar registros incompletos, capacidades menores a uno, precios negativos y prevención de reservas superpuestas, además de incluir protección CSRF en todos los envíos.
- **Vistas con Blade:** Uso de layouts base (`@extends`, `@yield`) para renderizar dinámicamente el catálogo de habitaciones y el historial de reservas de cada huésped.

## Arquitectura y Modelo de Datos (Migrado)

El proyecto evoluciona de PHP nativo a la estructura estándar de Laravel:
- **Modelos (`app/Models/`):** Clases `Room`, `Reservation` y `User` utilizando Eloquent para gestionar relaciones (ej. `Room` hasMany `Reservation`).
- **Controladores (`app/Http/Controllers/`):** Gestionan el flujo de datos.
- **Form Requests (`app/Http/Requests/`):** Aíslan la lógica de validación (ej. `StoreReservationRequest`).
- **Vistas (`resources/views/`):** Plantillas Blade para el área pública y el panel administrativo protegido por autenticación.