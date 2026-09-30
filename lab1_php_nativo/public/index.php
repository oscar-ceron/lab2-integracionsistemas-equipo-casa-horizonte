<?php

declare(strict_types=1);

use App\Conexion\Database;
use App\Controladores\RoomController;
use App\Controladores\ReservationController;
use App\Repositorios\RoomRepository;
use App\Repositorios\ReservationRepository;
use App\Servicios\Mailer;

require dirname(__DIR__) . '/vendor/autoload.php';

function escape(string|int|float|null $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$controller = null;
$rooms = [];
$reservationRooms = [];
$reservations = [];
$editingRoom = null;
$reservationError = null;
$message = null;
$error = null;

try {
    $controller = new RoomController(new RoomRepository(Database::connection()));
    $reservationController = new ReservationController(new ReservationRepository(Database::connection()), new Mailer());
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    $rooms = $controller->index();
    $reservationRooms = $reservationController->rooms();
    $reservations = $reservationController->index();

    if ($action === 'create-reservation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $reservationController->save($_POST);
        header('Location: index.php?status=reservation-created#reservas');
        exit;
    }

    if ($action === 'cancel-reservation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $reservationController->cancel((int) ($_POST['id'] ?? 0));
        header('Location: index.php?status=reservation-cancelled#reservas');
        exit;
    }

    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $controller->save($_POST, $id);
        header('Location: index.php?status=' . ($id === null ? 'created' : 'updated'));
        exit;
    }

    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->remove((int) ($_POST['id'] ?? 0));
        header('Location: index.php?status=deleted');
        exit;
    }

    if ($action === 'edit') {
        $editingRoom = $controller->edit((int) ($_GET['id'] ?? 0));
    }

    $message = match ($_GET['status'] ?? '') {
        'created' => 'Habitacion creada correctamente.',
        'updated' => 'Habitacion actualizada correctamente.',
        'deleted' => 'Habitacion eliminada correctamente.',
        'reservation-created' => 'Reserva creada correctamente. Revisa tu correo para ver la confirmación.',
        'reservation-cancelled' => 'Reserva cancelada correctamente.',
        default => null,
    };
} catch (\InvalidArgumentException $exception) {
    if (($action ?? '') === 'create-reservation') {
        $reservationError = $exception->getMessage();
    } else {
        $error = $exception->getMessage();
    }
} catch (\Throwable $exception) {
    $error = $exception->getMessage();
}

if ($message !== null) {
    $sweetAlert = [
        'title' => 'Éxito',
        'text' => $message,
        'icon' => 'success',
        'confirmButtonText' => 'Aceptar',
    ];
} elseif ($error !== null) {
    $sweetAlert = [
        'title' => 'Error',
        'text' => $error,
        'icon' => 'error',
        'confirmButtonText' => 'Aceptar',
    ];
} elseif ($reservationError !== null) {
    $sweetAlert = [
        'title' => 'Error en la reserva',
        'text' => $reservationError,
        'icon' => 'error',
        'confirmButtonText' => 'Aceptar',
    ];
} else {
    $sweetAlert = null;
}

$totalRooms = count($rooms);
$totalCapacity = array_sum(array_map(static fn (array $room): int => (int) $room['capacity'], $rooms));
$avgPrice = $totalRooms > 0 ? array_sum(array_map(static fn (array $room): float => (float) $room['price'], $rooms)) / $totalRooms : 0;
$isEditing = is_array($editingRoom);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospedaje | Panel de habitaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="shell">
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <div class="brand-mark">H</div>
                <div>
                    <h1>Casa Horizonte</h1>
                    <p>Administracion de hospedaje</p>
                </div>
            </div>
        </div>
    </header>

    <main class="content">
        <section class="hero">
            <div>
                <div class="eyebrow">Casa Horizonte / Gestión de hospedaje</div>
                <h2>Panel de hospedaje</h2>
                <p class="hero-copy">Consulta habitaciones, crea reservas y administra el inventario desde una sola página.</p>
            </div>
        </section>

        <ul class="nav nav-tabs dashboard-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas-panel" type="button" role="tab" aria-controls="reservas-panel" aria-selected="true">Reservas / Vista de usuario</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-panel" type="button" role="tab" aria-controls="admin-panel" aria-selected="false">Administracion</button></li>
        </ul>

        <div class="tab-content" id="dashboardTabsContent">
        <section class="tab-pane fade show active" id="reservas-panel" role="tabpanel" aria-labelledby="reservas-tab" tabindex="0">
        <section class="reservation-area user-area" id="reservas">
            <div class="section-heading">
                <div>
                    <div class="eyebrow">Atencion al huesped</div>
                    <h3>Crear una reserva</h3>
                </div>
                <span class="section-note">Confirmacion inmediata</span>
            </div>
            <div class="reservation-grid">
                <form class="panel form-panel" method="post">
                    <input type="hidden" name="action" value="create-reservation">
                    <div class="field"><label for="guest_name">Nombre del huesped *</label><input class="form-control" id="guest_name" name="guest_name" required maxlength="150" value="<?= escape($_POST['guest_name'] ?? '') ?>" placeholder="Nombre completo"></div>
                    <div class="field"><label for="guest_email">Correo electronico *</label><input class="form-control" id="guest_email" name="guest_email" type="email" required maxlength="200" value="<?= escape($_POST['guest_email'] ?? '') ?>" placeholder="huesped@correo.com"></div>
                    <div class="field"><label for="room_id">Habitacion *</label><select class="form-select" id="room_id" name="room_id" required>
                        <option value="">Selecciona una habitacion</option>
                        <?php foreach ($reservationRooms as $room): ?><option value="<?= escape($room['id']) ?>" <?= ((string) ($_POST['room_id'] ?? '') === (string) $room['id']) ? 'selected' : '' ?>>Habitacion <?= escape($room['number']) ?> · <?= escape($room['type']) ?> · $<?= number_format((float) $room['price'], 2) ?>/noche</option><?php endforeach; ?>
                    </select></div>
                    <div class="date-grid"><div class="field"><label for="start_date">Entrada *</label><input class="form-control" id="start_date" name="start_date" type="date" min="<?= date('Y-m-d') ?>" required value="<?= escape($_POST['start_date'] ?? '') ?>"></div><div class="field"><label for="end_date">Salida *</label><input class="form-control" id="end_date" name="end_date" type="date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required value="<?= escape($_POST['end_date'] ?? '') ?>"></div></div>
                    <button class="button" type="submit">Confirmar reserva</button>
                </form>
                <div class="user-info"><span class="eyebrow">Disponibilidad</span><h3>Elige tu estancia</h3><p>Selecciona una habitación, indica tus fechas y recibirás una confirmación inmediata. El sistema evita automáticamente las reservas duplicadas.</p><div class="availability-count"><strong><?= count($reservationRooms) ?></strong><span>habitaciones disponibles para reservar</span></div></div>
            </div>
        </section>
        </section>

        <section class="tab-pane fade" id="admin-panel" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
        <section class="stats" aria-label="Resumen administrativo">
            <div class="stat"><strong><?= $totalRooms ?></strong><span>Habitaciones registradas</span></div>
            <div class="stat"><strong><?= $totalCapacity ?></strong><span>Capacidad total</span></div>
            <div class="stat"><strong>$<?= number_format($avgPrice, 2) ?></strong><span>Tarifa promedio / noche</span></div>
        </section>

        <section class="workspace">
            <div class="panel">
                <div class="panel-header">
                    <h3>Habitaciones</h3>
                    <span class="eyebrow"><?= $totalRooms ?> registros</span>
                </div>
                <?php if ($rooms === []): ?>
                    <div class="empty">No hay habitaciones disponibles. Usa el formulario para registrar la primera.</div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Numero</th><th>Tipo</th><th>Capacidad</th><th>Tarifa</th><th>Descripcion</th><th>Acciones</th></tr></thead>
                            <tbody>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><strong><?= escape($room['number']) ?></strong></td>
                                    <td><?= escape($room['type']) ?></td>
                                    <td><?= escape($room['capacity']) ?> personas</td>
                                    <td><strong>$<?= number_format((float) $room['price'], 2) ?></strong></td>
                                    <td class="description"><?= escape($room['description']) ?: 'Sin descripcion' ?></td>
                                    <td class="actions">
                                        <a class="button secondary" href="index.php?action=edit&id=<?= escape($room['id']) ?>#formulario">Editar</a>
                                        <form method="post" style="display:inline" data-confirm data-confirm-title="Eliminar habitacion" data-confirm-text="¿Deseas eliminar esta habitacion? Esta acción no se puede deshacer." data-confirm-button-text="Sí, eliminar" data-confirm-cancel-button-text="Cancelar">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= escape($room['id']) ?>">
                                            <button class="button danger" type="submit" title="Eliminar">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="panel form-panel" id="formulario">
                <h3><?= $isEditing ? 'Editar habitacion' : 'Registrar habitacion' ?></h3>
                <form method="post">
                    <input type="hidden" name="action" value="save">
                    <?php if ($isEditing): ?><input type="hidden" name="id" value="<?= escape($editingRoom['id']) ?>"><?php endif; ?>
                    <div class="field"><label for="number">Numero *</label><input id="number" name="number" required maxlength="50" value="<?= escape($editingRoom['number'] ?? '') ?>" placeholder="Ej. 301"></div>
                    <div class="field"><label for="type">Tipo *</label><select class="form-select" id="type" name="type" required>
                        <?php foreach (['Single', 'Double', 'Suite', 'Family'] as $type): ?><option value="<?= $type ?>" <?= (($editingRoom['type'] ?? '') === $type) ? 'selected' : '' ?>><?= $type ?></option><?php endforeach; ?>
                    </select></div>
                    <div class="field"><label for="capacity">Capacidad *</label><input class="form-control" id="capacity" name="capacity" type="number" min="1" max="30" required value="<?= escape($editingRoom['capacity'] ?? 1) ?>"></div>
                    <div class="field"><label for="price">Precio por noche *</label><input class="form-control" id="price" name="price" type="number" min="0" step="0.01" required value="<?= escape($editingRoom['price'] ?? '') ?>" placeholder="0.00"></div>
                    <div class="field"><label for="description">Descripcion</label><textarea id="description" name="description" maxlength="1000" placeholder="Detalles de la habitacion..."><?= escape($editingRoom['description'] ?? '') ?></textarea></div>
                    <div class="form-actions"><button class="button" type="submit"><?= $isEditing ? 'Guardar cambios' : 'Crear habitacion' ?></button><?php if ($isEditing): ?><a class="button secondary" href="index.php#formulario">Cancelar</a><?php endif; ?></div>
                </form>
            </aside>
        </section>

        <section class="reservation-area" id="historial-reservas">
            <div class="section-heading">
                <div>
                    <div class="eyebrow">Control administrativo</div>
                    <h3>Historial de reservas</h3>
                </div>
                <span class="section-note">Gestion y comprobantes</span>
            </div>
            <div class="panel reservation-list">
                    <div class="panel-header"><h3>Reservas recientes</h3><span class="eyebrow"><?= count($reservations) ?> reservas</span></div>
                    <?php if ($reservations === []): ?><div class="empty">Todavia no hay reservas registradas.</div><?php else: ?>
                        <?php foreach ($reservations as $reservation): ?>
                            <article class="reservation-item">
                                <div><strong><?= escape($reservation['guest_name'] ?? 'Huesped') ?></strong><span><?= escape($reservation['guest_email'] ?? '') ?></span><span>Habitacion <?= escape($reservation['room_number']) ?> · <?= escape($reservation['start_date']) ?> al <?= escape($reservation['end_date']) ?></span></div>
                                <div class="reservation-meta"><strong>$<?= number_format((float) $reservation['total'], 2) ?></strong><span class="status <?= escape($reservation['status']) ?>"><?= escape($reservation['status']) ?></span><a class="button secondary" href="confirmation.php?id=<?= escape($reservation['id']) ?>">PDF + QR</a><?php if ($reservation['status'] !== 'cancelled'): ?><form method="post" data-confirm data-confirm-title="Cancelar reserva" data-confirm-text="¿Deseas cancelar esta reserva?" data-confirm-button-text="Sí, cancelar" data-confirm-cancel-button-text="Volver"><input type="hidden" name="action" value="cancel-reservation"><input type="hidden" name="id" value="<?= escape($reservation['id']) ?>"><button class="button danger" type="submit">Cancelar</button></form><?php endif; ?></div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
            </div>
        </section>
        </section>
        </div>
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($sweetAlert !== null): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire(<?php echo json_encode($sweetAlert, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>);
});
</script>
<?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const title = form.dataset.confirmTitle || 'Confirmación';
            const text = form.dataset.confirmText || '¿Estás seguro?';
            const confirmText = form.dataset.confirmButtonText || 'Sí, continuar';
            const cancelText = form.dataset.confirmCancelButtonText || 'Cancelar';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
</body>
</html>
