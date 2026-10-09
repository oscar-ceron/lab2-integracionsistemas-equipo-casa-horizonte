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

Para ejecutar este proyecto de forma correcta en un entorno local, se recomienda contar con lo siguiente:

- PHP 8.3 o superior.
- Composer 2.x.
- Node.js 18 o superior y npm.
- Un motor de base de datos compatible con Laravel:
  - MySQL 8 o MariaDB

> Este proyecto está basado en Laravel 13 y utiliza Vite para compilar los assets front-end, por lo que además de Composer se requiere Node.js para instalar y compilar los recursos del cliente.

## Instalación y Configuración Local

1. Clonar el repositorio y entrar al directorio del proyecto:
   ```bash
   git clone https://github.com/oscar-ceron/lab2-integracionsistemas-equipo-casa-horizonte.git
   cd lab2-integracionsistemas-equipo-casa-horizonte
   ```

2. Instalar las dependencias de PHP y del frontend:
   ```bash
   composer install
   npm install
   ```

3. Configurar las variables de entorno:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurar la base de datos:

   En ese caso, para usar MySQL, edita el archivo `.env` con tus credenciales locales:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Ejecutar las migraciones para crear el esquema de la base de datos:
   ```bash
   php artisan migrate
   ```

6. Compilar los assets del proyecto:
   ```bash
   npm run build
   ```

7. Levantar la aplicación en modo local:

   Opción 1: ejecutar el servidor de Laravel:
   ```bash
   php artisan serve
   ```

   Opción 2: iniciar el entorno de desarrollo completo con Vite y Laravel:
   ```bash
   composer run dev
   ```

   La aplicación quedará disponible en `http://localhost:8000`.

8. (Opcional) Si deseas verificar la configuración inicial con la base de datos y los assets listos, puedes ejecutar:
   ```bash
   php artisan test
   ```

## Funcionalidades Principales

- **Autenticación y Sesiones:** Sistema de registro e inicio de sesión. Cada usuario visualiza y gestiona únicamente las reservas e información que le pertenece.
- **CRUD Completo:** Operaciones de crear, listar, editar y eliminar aplicadas sobre las habitaciones y las reservas mediante Eloquent ORM.
- **Validación de Formularios:** Uso de *Form Requests* para rechazar registros incompletos, capacidades menores a uno, precios negativos y prevención de reservas superpuestas, además de incluir protección CSRF en todos los envíos.
- **Vistas con Blade:** Uso de layouts base (`@extends`, `@yield`) para renderizar dinámicamente el catálogo de habitaciones y el historial de reservas de cada huésped.

## Notas de IA

Este proyecto fue desarrollado con apoyo de herramientas de IA para reforzar la productividad en tareas de documentación, revisión de código y apoyo en la organización de la estructura del proyecto. La IA se utilizó principalmente como asistente para:

- proponer mejoras en la estructura de archivos y organización del código;
- redactar y ajustar documentación técnica, incluyendo este README;
- revisar consistencia en la lógica de validación y flujo de autenticación;
- apoyar la identificación de posibles errores y sugerencias de refactorización;

Sin embargo, la toma de decisiones finales, la validación funcional del sistema y la aprobación del resultado corresponden al equipo. El código, la lógica de negocio y la configuración final del proyecto fueron revisados y ajustados manualmente para garantizar que el comportamiento del sistema sea consistente con los requisitos del laboratorio.

## Arquitectura y Modelo de Datos (Migrado)

El proyecto evoluciona de PHP nativo a la estructura estándar de Laravel:
- **Modelos (`app/Models/`):** Clases `Room`, `Reservation` y `User` utilizando Eloquent para gestionar relaciones (ej. `Room` hasMany `Reservation`).
- **Controladores (`app/Http/Controllers/`):** Gestionan el flujo de datos.
- **Form Requests (`app/Http/Requests/`):** Aíslan la lógica de validación (ej. `StoreReservationRequest`).
- **Vistas (`resources/views/`):** Plantillas Blade para el área pública y el panel administrativo protegido por autenticación.