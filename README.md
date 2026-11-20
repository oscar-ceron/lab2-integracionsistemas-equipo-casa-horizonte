# GUÍA DE LABORATORIO III
## API REST protegida, probada, mantenible y con uso crítico de IA

**Proyecto:** Casa Horizonte
# Casa Horizonte - GUÍA DE LABORATORIO III

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

## Funcionalidades Principales

- **Autenticación y sesiones:** Sistema de registro e inicio de sesión. Cada usuario visualiza y gestiona únicamente las reservas que le pertenecen.
- **CRUD de habitaciones y reservas:** Operaciones de creación, consulta, actualización y eliminación mediante Eloquent ORM.
- **Validación de formularios:** Uso de Form Requests para validar los datos recibidos, fechas, habitaciones y disponibilidad de reservas, con mensajes en español.
- **Gestión de reservas:** Uso de `ReservationService` para centralizar la lógica de negocio relacionada con la creación, actualización y disponibilidad de las reservas.
- **Vistas con Blade:** Uso de layouts y plantillas dinámicas para el catálogo de habitaciones y el historial de reservas.
- **API REST:** Endpoints para autenticación, consulta y administración de habitaciones y reservas.
- **Autenticación API con Laravel Sanctum:** Registro, inicio y cierre de sesión mediante tokens Bearer, además de rutas protegidas con `auth:sanctum`.
- **API Resources:** Uso de `RoomResource` y `ReservationResource` para estructurar las respuestas JSON.
- **Seguridad y autorización:** Aislamiento de reservas por usuario autenticado, protección frente a accesos IDOR y restricciones de operaciones administrativas.
- **Manejo de errores:** Respuestas JSON estandarizadas para errores de validación, autenticación, autorización y otros casos de la API.

La documentación de autenticación se encuentra en [docs/API_AUTENTICACION.md](docs/API_AUTENTICACION.md).

## Notas de IA

## Uso crítico y responsable de inteligencia artificial

Durante el desarrollo de Casa Horizonte se utilizaron herramientas de inteligencia artificial como apoyo en actividades de programación, comprensión de conceptos, revisión de código, identificación de errores y elaboración de documentación técnica.

La IA contribuyó a:

- Proponer mejoras en la organización y estructura del código.
- Apoyar la comprensión de Laravel, Sanctum y API Resources.
- Analizar errores y sugerir posibles soluciones.
- Revisar la lógica de validación y los controles de seguridad.
- Elaborar y mejorar documentación técnica.

Las respuestas y recomendaciones generadas por IA no se incorporaron automáticamente. El equipo revisó su pertinencia, contrastó las sugerencias con la documentación técnica y verificó los resultados mediante pruebas.

Se reconoce que la inteligencia artificial puede generar respuestas incorrectas, incompletas o incompatibles con el proyecto. Por ello, las decisiones finales, la revisión del código y la validación funcional corresponden a los integrantes del equipo.

Para ampliar la información sobre los criterios de uso, beneficios, riesgos y validación de las recomendaciones, consultar [docs/FICHA_USO_CRITICO_IA.md](docs/FICHA_USO_CRITICO_IA.md).

## Arquitectura y Modelo de Datos (Migrado)

El proyecto evoluciona de PHP nativo a la estructura estándar de Laravel:
- **Modelos (`app/Models/`):** Clases `Room`, `Reservation` y `User` utilizando Eloquent para gestionar relaciones (ej. `Room` hasMany `Reservation`).
- **Controladores (`app/Http/Controllers/`):** Gestionan el flujo de datos.
- **Form Requests (`app/Http/Requests/`):** Aíslan la lógica de validación (ej. `StoreReservationRequest`).
- **Vistas (`resources/views/`):** Plantillas Blade para el área pública y el panel administrativo protegido por autenticación.
## API REST y recursos

El proyecto incorpora servicios API para la administración de habitaciones y reservaciones. Para mantener respuestas estructuradas y uniformes, se utilizan Laravel API Resources.

**Recursos implementados:**

- `RoomResource`: transforma la información de las habitaciones.
- `ReservationResource`: transforma la información de las reservaciones.

**Controladores API:**

- `RoomApiController`: administra las operaciones relacionadas con habitaciones.
- `ReservationApiController`: administra las operaciones relacionadas con reservaciones, considerando los permisos del usuario autenticado.

La autenticación de los servicios API se realiza mediante Laravel Sanctum.

## Documentación del proyecto

La documentación técnica complementaria se encuentra organizada en los siguientes archivos:

| Documento | Descripción |
|---|---|
| [API_AUTENTICACION.md](docs/API_AUTENTICACION.md) | Explica la autenticación API mediante Laravel Sanctum. |
| [Anexo-A.md](docs/Anexo-A.md) | Presenta la comparación técnica entre Laravel y Django. |
| [FICHA_USO_CRITICO_IA.md](docs/FICHA_USO_CRITICO_IA.md) | Documenta el uso, beneficios, limitaciones y validación de las recomendaciones de IA. |
| [CAMBIOS.md](CAMBIOS.md) | Registra el mantenimiento correctivo y evolutivo del proyecto. |

## Mantenimiento y control de calidad

## Mantenimiento y control de calidad

El proyecto contempla actividades de mantenimiento correctivo y evolutivo para mejorar la seguridad, confiabilidad y capacidad de mantenimiento de la API REST.

Entre las mejoras implementadas se encuentran:

- Aislamiento de las reservas según el usuario autenticado.
- Autenticación mediante Laravel Sanctum y tokens Bearer.
- Validación de solicitudes mediante Form Requests.
- Centralización de la lógica de reservas mediante `ReservationService`.
- Uso de API Resources para estandarizar las respuestas JSON.
- Protección de rutas y operaciones administrativas.
- Manejo global de errores y respuestas JSON.
- Uso de `DB::transaction()` para mantener la consistencia de las operaciones en la base de datos.

### Pruebas automatizadas

Se utiliza PHPUnit mediante las herramientas de pruebas integradas en Laravel.

Para ejecutar las pruebas:

```bash
php artisan test
```

**Último resultado registrado:**
- 45 pruebas aprobadas.
- 140 aserciones.
- 0 pruebas fallidas.

También se realizaron pruebas de los endpoints mediante Bruno, incluyendo autenticación, operaciones CRUD, validaciones, permisos, seguridad IDOR y casos límite.

## Uso responsable de inteligencia artificial

Se utilizaron herramientas de inteligencia artificial como apoyo para la comprensión de conceptos, revisión de código, identificación de errores y elaboración de documentación. Las recomendaciones se revisaron y contrastaron con la implementación y las pruebas del sistema.

La ficha de uso crítico de IA contiene información complementaria sobre los beneficios, riesgos y criterios de validación aplicados.