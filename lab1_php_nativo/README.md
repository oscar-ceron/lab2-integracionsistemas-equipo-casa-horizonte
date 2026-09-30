si responde el codifo las  preguntas  # Casa Horizonte - Laboratorio I

Aplicación web de hospedaje desarrollada en PHP nativo. Permite administrar habitaciones, registrar reservas, verificar disponibilidad, enviar confirmaciones por correo y generar comprobantes PDF con código QR. La aplicación usa MySQL, PDO, Composer y una organización por capas basada en modelos, controladores, repositorios y servicios.

## Funcionalidades

### Vista del huésped

- Consultar habitaciones con número, tipo y tarifa por noche.
- Crear una reserva indicando nombre, correo, habitación y fechas.
- Calcular automáticamente el total según noches y precio.
- Evitar reservas cruzadas para una misma habitación.
- Mostrar confirmación visual y enviar un correo con el PDF adjunto.

### Vista administrativa

- Ver habitaciones, capacidad acumulada y tarifa promedio.
- Registrar, editar y eliminar habitaciones.
- Consultar el historial de reservas y su estado.
- Cancelar reservas conservando el registro histórico.
- Descargar el comprobante PDF de cada reserva.

### Comprobantes y verificación

- `confirmation.php` genera un PDF tamaño A4 con los datos de la reserva.
- El PDF incluye un QR con un enlace firmado mediante HMAC.
- `verify.php` valida el token y consulta nuevamente la reserva en MySQL.
- Un token inválido devuelve HTTP 403 y una reserva inexistente devuelve HTTP 404.

## Flujo principal

1. `public/index.php` carga Composer, conecta con MySQL y consulta habitaciones y reservas.
2. El huésped envía el formulario con la acción `create-reservation`.
3. `ReservationController` valida datos, fechas, existencia y conflictos.
4. `ReservationRepository` guarda el usuario y la reserva dentro de una transacción.
5. `Mailer` intenta enviar el correo de confirmación mediante SMTP.
6. La reserva permanece guardada aunque el correo falle.
7. El administrador puede cancelar la reserva o descargar su comprobante.

Para habitaciones, `index.php` utiliza las acciones `save`, `edit` y `delete`. Después de cada operación redirige a la página principal para evitar reenvíos del formulario.

## Reglas de negocio y validaciones

- Los campos obligatorios no pueden estar vacíos y el correo debe ser válido.
- La habitación seleccionada debe existir.
- La entrada no puede ser anterior al día actual y la salida debe ser posterior.
- No se permiten reservas superpuestas, excepto las que están canceladas.
- La capacidad debe ser un entero mayor que cero y el precio no puede ser negativo.
- El total se calcula como `noches * precio`; no se recibe del formulario.
- Los identificadores se validan y se envían como parámetros SQL.

## Equipo

- Nombre: Equipo Casa Horizonte
- Integrantes: completar con los 4 nombres y usuarios de GitHub antes de entregar.

## Requisitos

- PHP 8.1 o superior con las extensiones `pdo` y `pdo_mysql`.
- MySQL 8 o MariaDB.
- Composer 2.

## Instalación

1. Clonar el repositorio y entrar a su carpeta.
2. Crear la base de datos ejecutando `database/create_full_schema.sql` en MySQL.
3. Instalar el autoload PSR-4:

```bash
composer install
```

4. Copiar `.env.example` como `.env` y configurar las variables:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=org_chart
DB_USER=root
DB_PASS=
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-correo@gmail.com
MAIL_PASSWORD=tu-contrasena-de-aplicacion
MAIL_FROM=tu-correo@gmail.com
APP_URL=http://localhost:8000
APP_KEY=cambia-esta-clave-secreta
```

En Windows PowerShell, por ejemplo:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_NAME="org_chart"
$env:DB_USER="root"
$env:DB_PASS=""
$env:MAIL_HOST="smtp.gmail.com"
$env:MAIL_PORT="587"
$env:MAIL_USERNAME="tu-correo@gmail.com"
$env:MAIL_PASSWORD="tu-contrasena-de-aplicacion"
$env:MAIL_FROM="tu-correo@gmail.com"
$env:APP_URL="http://localhost:8000"
$env:APP_KEY="cambia-esta-clave-secreta"
```

No guardes las credenciales SMTP en el repositorio ni las incluyas en capturas de pantalla. Define estas variables en la misma sesión donde iniciarás PHP. Si usas otro proveedor, sustituye `MAIL_HOST`, `MAIL_PORT` y el tipo de cifrado configurado en `src/Servicios/Mailer.php`.

5. Iniciar el servidor local:

```bash
php -S localhost:8000 -t public
```

Abrir `http://localhost:8000`.

## Uso de la aplicación

1. En la pestaña de reservas, selecciona una habitación, completa los datos del huésped y define fechas futuras.
2. Presiona **Confirmar reserva**. El sistema valida disponibilidad y calcula el total.
3. Consulta la confirmación visual y el correo recibido. El correo contiene el PDF y un enlace basado en `APP_URL`.
4. En **Administración**, usa **Editar** o **Eliminar** para gestionar habitaciones.
5. En el historial, usa **Cancelar** para liberar fechas sin borrar el registro o **PDF + QR** para descargar el comprobante.

## Comandos útiles

```bash
# Instalar dependencias y generar el autoload
composer install

# Revisar sintaxis de un archivo PHP
php -l public/index.php

# Iniciar el servidor de desarrollo
php -S localhost:8000 -t public
```

El proyecto no incluye autenticación de administradores; la pestaña administrativa forma parte de la misma página pública y debe protegerse antes de usarlo en producción.

## Arquitectura y responsabilidades

- `public/index.php`: punto de entrada, interfaz HTML, formularios y despacho de acciones.
- `public/confirmation.php`: descarga el comprobante PDF.
- `public/verify.php`: valida la firma del QR y muestra los datos confirmados.
- `public/assets/style.css`: estilos responsive del panel y formularios.
- `src/Conexion/Database.php`: carga `.env`, crea PDO y configura excepciones.
- `src/Contratos/CrudRepository.php`: contrato de operaciones `all`, `find`, `create`, `update` y `delete`.
- `src/Controladores/RoomController.php`: valida y coordina el CRUD de habitaciones.
- `src/Controladores/ReservationController.php`: valida fechas y disponibilidad, calcula totales y coordina correo.
- `src/Modelos/AbstractEntity.php`: clase base con nombre, descripción y comportamiento común.
- `src/Modelos/Room.php`: entidad de habitación con tipo, tarifa y capacidad.
- `src/Repositorios/RoomRepository.php`: consultas SQL de habitaciones.
- `src/Repositorios/ReservationRepository.php`: consultas de reservas, usuarios y transacciones.
- `src/Servicios/Mailer.php`: SMTP, plantilla HTML y PDF adjunto.
- `src/Servicios/ReservationDocument.php`: PDF, QR y firma HMAC.
- `database/create_full_schema.sql`: base de datos, claves foráneas y datos de ejemplo.

## Modelo de datos

- `rooms`: número, tipo, capacidad, tarifa y descripción.
- `users`: datos del huésped; el correo es único.
- `reservations`: habitación, huésped, fechas, estado y total.
- `positions` y `org_units`: tablas base para futuras ampliaciones.

`reservations.room_id` se relaciona con `rooms.id` y `reservations.user_id` con `users.id`. Cancelar cambia el estado a `cancelled`, conservando el historial. El esquema define `ON DELETE CASCADE` para las reservas relacionadas con una habitación eliminada.

## Estructura

- `public/`: punto de entrada HTML/PHP y estilos.
- `src/Conexion/`: conexión PDO con excepciones y consultas nativas.
- `src/Controladores/`: validación y flujo de solicitudes.
- `src/Modelos/`: clases del dominio; `Room` hereda de `AbstractEntity`.
- `src/Repositorios/`: CRUD con consultas preparadas.
- `src/Contratos/`: interfaz `CrudRepository` implementada por el repositorio.
- `database/`: esquema completo y datos iniciales.

## Evidencia técnica para Demo Day

- POO: `Room extends AbstractEntity`.
- Interfaz: `RoomRepository implements CrudRepository`.
- PDO: `RoomRepository` utiliza `prepare()` y parámetros nombrados en crear, editar y eliminar.
- CRUD: el formulario de `public/index.php` crea y actualiza; cada fila permite editar y eliminar.
- Namespaces: Composer carga automáticamente todo lo que está bajo `src/` con PSR-4.
- Validación: el controlador rechaza campos obligatorios vacíos, capacidades menores que uno y precios negativos.
- Correo: `src/Servicios/Mailer.php` envía la confirmación al correo del huésped mediante SMTP de Gmail y PHPMailer después de confirmar la reserva.
- Alerta: la página muestra una alerta JavaScript cuando la reserva se guardó correctamente.
- PDF y QR: el botón `PDF + QR` descarga un comprobante A4 con los datos de la reserva y un código QR de verificación. El enlace del correo usa `APP_URL`.
- Verificación web: el QR abre `verify.php`, valida una firma con `APP_KEY` y muestra los datos reales de la reserva desde MySQL. Para escanearlo desde cualquier lugar, `APP_URL` debe ser un dominio público o un túnel HTTPS, no `localhost`.

### Respuestas para la defensa técnica

- **¿Por qué una interfaz?** `CrudRepository` define las operaciones obligatorias sin imponer una clase padre. Así el controlador depende de un contrato y en el futuro se puede cambiar MySQL por otra implementación.
- **¿Qué cambiarían para agregar otro tipo de entidad?** Se crearía otra clase del dominio que extienda `AbstractEntity`, implementando `category()`. El repositorio nuevo implementaría `CrudRepository`; las clases actuales no tendrían que modificarse.
- **¿Qué método se sobrescribe?** `Room::category()` implementa el método abstracto de `AbstractEntity` y devuelve el tipo de habitación.
- **¿Qué riesgo evita `prepare()`?** Evita que valores enviados por el usuario se interpreten como SQL. Los parámetros nombrados se envían como datos separados de la consulta.
- **¿Cómo se maneja un error de conexión?** `Database` usa `PDO::ERRMODE_EXCEPTION`, captura `PDOException` y lanza un mensaje controlado. `public/index.php` captura el error y muestra una alerta.
- **¿Qué validaciones existen?** El controlador rechaza campos obligatorios vacíos, correos inválidos, fechas incorrectas, habitaciones inexistentes, conflictos de fechas, capacidades menores que uno y precios negativos.
- **¿Qué hace `composer.json`?** Declara dependencias y el mapeo PSR-4 `App\\` hacia `src/`. `composer install` instala librerías y genera `vendor/autoload.php`; sin él habría que cargar clases manualmente.
- **¿Por qué separar carpetas?** Cada namespace tiene una responsabilidad: modelos representan datos, controladores coordinan solicitudes, repositorios ejecutan persistencia, contratos definen interfaces y conexión administra PDO.

### Configuración del correo

Para Gmail, activa la verificación en dos pasos y crea una **contraseña de aplicación** en tu cuenta de Google. Colócala en `MAIL_PASSWORD`, sin espacios; no uses la contraseña normal de Gmail. Cambia `tu-correo@gmail.com` por la cuenta que enviará los mensajes. Después ejecuta `composer install` para instalar PHPMailer. Si Gmail rechaza el envío, la reserva ya guardada se conserva y la aplicación muestra la confirmación visual.

Para probar el envío, inicia la aplicación, registra una reserva con un correo que puedas consultar y revisa también la carpeta de spam. Los errores de autenticación suelen indicar que la contraseña de aplicación es incorrecta; un error de conexión normalmente apunta a `MAIL_HOST`, `MAIL_PORT`, firewall o a las variables no definidas en la sesión actual. `APP_URL` debe apuntar a la dirección pública de la aplicación cuando el enlace de verificación del correo o del QR se vaya a abrir desde otro equipo.

## Uso de IA

Se utilizó GitHub Copilot como apoyo para proponer la estructura inicial, revisar sintaxis y sugerir estilos de interfaz. Algunos ejemplos de solicitudes fueron: diseñar un formulario responsive para habitaciones y reservas, revisar validaciones de fechas y disponibilidad, explicar el uso de `PDO::prepare()` y documentar la configuración SMTP con PHPMailer.

La IA no reemplazó la revisión del equipo: cada sugerencia se contrastó con el esquema SQL y las clases existentes, se corrigieron las respuestas que no coincidían con el dominio y se validó el resultado ejecutando el lint de PHP, revisando las consultas preparadas, probando manualmente el CRUD y verificando el diseño en navegador. Las decisiones finales y la explicación técnica deben ser comprendidas y defendidas por los cuatro integrantes.

## Proceso Kanban

Registrar en el tablero las tareas de base de datos, conexión PDO, modelo, CRUD, interfaz, pruebas y documentación. Mover las tarjetas entre columnas durante el periodo de trabajo y adjuntar el enlace de este repositorio a la tarjeta de Laboratorio I.

> Las preguntas sobre una tarjeta bloqueada y el trabajo individual de los cuatro integrantes deben responderse con el historial real del tablero y los commits del equipo; no deben inventarse dentro del código.
