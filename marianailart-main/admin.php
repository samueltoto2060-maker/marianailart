<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    die("Acceso denegado. Esta sección requiere permisos de Administrador. <a href='index.php?auth=login'>Iniciar sesión como Admin</a>");
}

$user = $_SESSION['user'];
$message = '';
function formatAdminTime($time) {
    $timestamp = strtotime($time);
    return $timestamp ? date('g:i A', $timestamp) : $time;
}
$scheduleSlots = ['08:00', '09:30', '11:00', '12:30', '14:00', '15:30', '17:00', '18:30'];
$selectedDate = $_POST['schedule_date'] ?? $_GET['schedule_date'] ?? date('Y-m-d');
$postAction = $_POST['action'] ?? '';
$activeTab = $_GET['view'] === 'schedule' || $postAction === 'toggle_schedule' ? 'schedule' : ($postAction === 'update_service' || $postAction === 'add_service' || $postAction === 'delete_service' ? 'services' : 'appointments');

// Acciones de administración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Crear nuevo servicio (Cuadro)
    if (isset($_POST['action']) && $_POST['action'] === 'add_service') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $detalles = trim($_POST['detalles'] ?? '');
        $duracion = trim($_POST['duracion'] ?? '60 min');
        $precio = floatval($_POST['precio'] ?? 0);
        $categoria = $_POST['categoria'] ?? 'manicure';
        $image_url = trim($_POST['image_url'] ?? 'https://images.unsplash.com/photo-1604654894610-df63bc536371?q=80&w=600&auto=format&fit=crop');

        $servId = strval(time());
        $stmtAdd = $pdo->prepare("INSERT INTO servicios (id, nombre, descripcion, detalles, duracion, precio, categoria, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmtAdd->execute([$servId, $nombre, $descripcion, $detalles, $duracion, $precio, $categoria, $image_url])) {
            $message = "¡Servicio '$nombre' agregado exitosamente a la base de datos!";
        }
    }

    // 2. Eliminar servicio
    if (isset($_POST['action']) && $_POST['action'] === 'delete_service') {
        $servId = $_POST['service_id'] ?? '';
        $stmtDelServ = $pdo->prepare("DELETE FROM servicios WHERE id = ?");
        if ($stmtDelServ->execute([$servId])) {
            $message = "Cuadro de servicio eliminado permanentemente de MySQL.";
        }
    }

    // 3. Modificar servicio existente
    if (isset($_POST['action']) && $_POST['action'] === 'update_service') {
        $servId = $_POST['service_id'] ?? '';
        $stmtUpdateServ = $pdo->prepare("UPDATE servicios SET nombre = ?, descripcion = ?, detalles = ?, duracion = ?, precio = ?, image_url = ? WHERE id = ?");
        if ($stmtUpdateServ->execute([
            trim($_POST['nombre'] ?? ''),
            trim($_POST['descripcion'] ?? ''),
            trim($_POST['detalles'] ?? ''),
            trim($_POST['duracion'] ?? '60 min'),
            floatval($_POST['precio'] ?? 0),
            trim($_POST['image_url'] ?? ''),
            $servId
        ])) {
            $message = 'Cuadro de servicio actualizado correctamente.';
        }
    }

    // 4. Bloquear o habilitar un día completo o una hora
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_schedule') {
        $scheduleDate = $_POST['schedule_date'] ?? date('Y-m-d');
        $mode = $_POST['schedule_mode'] ?? '';
        $slot = $_POST['schedule_slot'] ?? '';
        $stmtSchedule = $pdo->prepare("SELECT * FROM fechas_bloqueadas WHERE fecha = ?");
        $stmtSchedule->execute([$scheduleDate]);
        $schedule = $stmtSchedule->fetch();
        $blockedSlots = $schedule && $schedule['turnos_bloqueados'] ? array_filter(explode(',', $schedule['turnos_bloqueados'])) : [];
        $isFullyBlocked = $schedule ? (int)$schedule['is_fully_blocked'] : 0;

        if ($mode === 'day') {
            $isFullyBlocked = $isFullyBlocked ? 0 : 1;
        } elseif ($mode === 'slot' && in_array($slot, $scheduleSlots, true)) {
            if ($isFullyBlocked) {
                $blockedSlots = array_values(array_diff($scheduleSlots, [$slot]));
                $isFullyBlocked = 0;
            } elseif (in_array($slot, $blockedSlots, true)) {
                $blockedSlots = array_values(array_diff($blockedSlots, [$slot]));
            } else {
                $blockedSlots[] = $slot;
            }
        }

        $blockedSlots = implode(',', array_unique($blockedSlots));
        $stmtSaveSchedule = $pdo->prepare("INSERT INTO fechas_bloqueadas (fecha, turnos_bloqueados, is_fully_blocked) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE turnos_bloqueados = VALUES(turnos_bloqueados), is_fully_blocked = VALUES(is_fully_blocked)");
        $stmtSaveSchedule->execute([$scheduleDate, $blockedSlots, $isFullyBlocked]);
        $selectedDate = $scheduleDate;
        $message = $isFullyBlocked ? "Día $scheduleDate bloqueado." : "Disponibilidad de $scheduleDate actualizada.";
    }

    // 3. Confirmar / Cancelar / Eliminar cita
    if (isset($_POST['action']) && $_POST['action'] === 'update_cita') {
        $citaId = $_POST['cita_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($status === 'delete') {
            $stmtDelCita = $pdo->prepare("DELETE FROM citas WHERE id = ?");
            $stmtDelCita->execute([$citaId]);
            $message = "Cita eliminada de la base de datos.";
        } else {
            $stmtUpdCita = $pdo->prepare("UPDATE citas SET status = ? WHERE id = ?");
            $stmtUpdCita->execute([$status, $citaId]);
            $message = "Estado de cita actualizado a: $status.";
        }
    }
}

$stmtSelectedSchedule = $pdo->prepare("SELECT * FROM fechas_bloqueadas WHERE fecha = ?");
$stmtSelectedSchedule->execute([$selectedDate]);
$selectedSchedule = $stmtSelectedSchedule->fetch();
$blockedSlots = $selectedSchedule && $selectedSchedule['turnos_bloqueados'] ? array_filter(explode(',', $selectedSchedule['turnos_bloqueados'])) : [];
$isDayBlocked = $selectedSchedule && (int)$selectedSchedule['is_fully_blocked'] === 1;

// Cargar listas
$citas = $pdo->query("SELECT * FROM citas ORDER BY date DESC, time_slot DESC")->fetchAll();
$servicios = $pdo->query("SELECT * FROM servicios ORDER BY created_at DESC")->fetchAll();
$clientas = $pdo->query("SELECT * FROM usuarios WHERE is_admin = 0 ORDER BY registered_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | María Nail Art</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #37414b; --muted: #6f8081; --paper: #f7fbfa; --white: #fff; --mint: #43b5a6; --mint-soft: #e7f7f4; --line: #d6e8e5; --pink: #e9a9b8; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--ink); background: #edf5f3; font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .admin-shell { width: min(100% - 24px, 1180px); height: min(100vh - 24px, 900px); margin: 12px auto; display: flex; flex-direction: column; overflow: hidden; background: var(--paper); border: 1px solid var(--mint); border-radius: 24px; box-shadow: 0 15px 45px rgba(55, 65, 75, .16); }
        .admin-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 26px; background: #f2faf8; border-bottom: 1px solid var(--line); }
        .admin-title { display: flex; align-items: center; gap: 14px; }
        .admin-mark { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; color: var(--mint); border: 1px solid #b5e4dd; border-radius: 50%; font-size: 19px; }
        .admin-kicker { color: var(--muted); font-size: 11px; }
        .admin-badge { display: inline-block; margin-left: 7px; padding: 3px 8px; color: #fff; background: var(--mint); border-radius: 5px; font: 700 10px 'Plus Jakarta Sans', sans-serif; vertical-align: middle; }
        .admin-close { color: #8aa09f; font-size: 28px; line-height: 1; }
        .admin-close:hover { color: var(--pink); }
        .admin-tabs { display: grid; grid-template-columns: repeat(4, 1fr); background: #fff; border-bottom: 1px solid var(--line); }
        .admin-tab { min-height: 48px; padding: 0 12px; color: var(--muted); background: #fff; border-bottom: 2px solid transparent; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
        .admin-tab:hover, .admin-tab.is-active { color: var(--mint); border-bottom-color: var(--mint); background: #fbfefd; }
        .admin-content { flex: 1; overflow-y: auto; padding: 24px; }
        .admin-panel { display: none; }
        .admin-panel.is-active { display: block; }
        .admin-intro { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 13px 14px; margin-bottom: 16px; background: #eff8f6; border: 1px solid var(--line); border-radius: 14px; color: #526566; font-size: 12px; }
        .admin-count { padding: 5px 10px; background: #dff3ef; border-radius: 999px; color: var(--ink); font-weight: 700; white-space: nowrap; }
        .admin-card, .service-admin-card, .client-card { background: var(--white); border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 4px 12px rgba(55, 65, 75, .04); }
        .admin-card { padding: 16px; margin-bottom: 12px; }
        .appointment-head { display: flex; justify-content: space-between; gap: 12px; }
        .appointment-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; padding: 11px; background: #f5faf9; border: 1px solid #e5efed; border-radius: 12px; color: var(--muted); font-size: 12px; }
        .admin-comment { margin-top: 9px; padding: 9px 11px; background: #f8fbfa; border: 1px solid #e5efed; border-radius: 10px; color: var(--muted); font-size: 11px; font-style: italic; }
        .admin-actions { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #e7efed; }
        .admin-action { padding: 7px 10px; border: 1px solid var(--line); border-radius: 8px; color: var(--muted); background: #fff; font-size: 11px; font-weight: 700; }
        .admin-action:hover { border-color: var(--mint); color: var(--mint); }
        .admin-action.confirm { color: #fff; background: #08a64d; border-color: #08a64d; }
        .admin-action.cancel, .admin-action.delete { color: #d65770; border-color: #f2b7c4; background: #fff7f8; }
        .status { padding: 4px 9px; border: 1px solid; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .status.pending { color: #c28b28; border-color: #f0d49c; background: #fffaf0; }
        .status.confirmed { color: #08a64d; border-color: #b8edcf; background: #f2fff7; }
        .status.cancelled { color: #d65770; border-color: #f2b7c4; background: #fff7f8; }
        .service-admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; }
        .service-admin-card { display: flex; flex-direction: column; padding: 12px; }
        .service-admin-card img { width: 86px; height: 80px; flex-shrink: 0; object-fit: cover; border-radius: 11px; }
        .service-row { display: flex; gap: 12px; }
        .service-description { color: var(--muted); font-size: 11px; line-height: 1.5; }
        .price-tag { padding: 4px 8px; color: var(--mint); border: 1px solid #bce8e1; border-radius: 5px; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .service-details { margin-top: 12px; padding: 10px; background: #f3f9f8; border: 1px solid #e2efec; border-radius: 11px; color: var(--muted); font-size: 11px; font-style: italic; }
        .admin-form { padding: 16px; margin-bottom: 18px; background: #eff8f6; border: 1px solid var(--line); border-radius: 16px; }
        .admin-input { width: 100%; padding: 10px 12px; background: #fff; border: 1px solid var(--line); border-radius: 10px; color: var(--ink); font-size: 12px; outline: none; }
        .admin-input:focus { border-color: var(--mint); box-shadow: 0 0 0 3px rgba(67, 181, 166, .14); }
        .admin-primary { padding: 10px 14px; color: #fff; background: var(--mint); border-radius: 10px; font-size: 12px; font-weight: 700; }
        .admin-primary:hover { background: var(--ink); }
        .schedule-box { padding: 18px; background: #eff8f6; border: 1px solid var(--line); border-radius: 16px; }
        .schedule-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 16px; }
        .schedule-slot { padding: 15px 8px; text-align: center; background: #fff; border: 1px solid var(--line); border-radius: 12px; color: var(--ink); font-size: 12px; font-weight: 700; }
        .schedule-slot span { display: block; margin-top: 4px; color: #08a64d; font-size: 10px; text-transform: uppercase; }
        .client-list { display: grid; gap: 10px; }
        .client-card { display: flex; justify-content: space-between; gap: 12px; padding: 16px; }
        .admin-footer { display: flex; justify-content: space-between; gap: 12px; padding: 14px 20px; background: #fff; border-top: 1px solid var(--line); color: var(--muted); font-size: 11px; }
        .admin-footer strong { color: var(--ink); }
        .message { padding: 12px 14px; margin-bottom: 16px; color: #087f45; background: #eafaf1; border: 1px solid #bfe9ce; border-radius: 12px; font-size: 12px; font-weight: 700; }
        @media (max-width: 700px) { .admin-shell { width: 100%; height: 100vh; margin: 0; border-radius: 0; } .admin-header { padding: 16px; } .admin-content { padding: 14px; } .admin-tabs { grid-template-columns: repeat(2, 1fr); } .admin-tab { min-height: 42px; font-size: 10px; } .schedule-grid { grid-template-columns: repeat(2, 1fr); } .appointment-meta { grid-template-columns: 1fr; } .admin-footer { padding: 12px 14px; } }
    </style>
</head>
<body>

    <div class="admin-shell">
    <header class="admin-header">
        <div class="admin-title"><span class="admin-mark">&#9881;</span><div><h1 class="font-serif text-2xl font-bold text-gray-800">Panel de Administración <span class="admin-badge">ADMIN</span></h1><p class="admin-kicker">Bienvenida, <?php echo htmlspecialchars($user['nombre']); ?>. Controla citas, bloquea horarios y gestiona recuadros de servicios.</p></div></div>
        <a href="index.php" class="admin-close" aria-label="Cerrar panel">&times;</a>
    </header>
    <nav class="admin-tabs" aria-label="Secciones del panel">
        <button type="button" class="admin-tab <?php echo $activeTab === 'appointments' ? 'is-active' : ''; ?>" data-tab="appointments">&#128197; &nbsp;Citas (<?php echo count($citas); ?>)</button>
        <button type="button" class="admin-tab <?php echo $activeTab === 'schedule' ? 'is-active' : ''; ?>" data-tab="schedule">&#9881; &nbsp;Bloquear horarios</button>
        <button type="button" class="admin-tab <?php echo $activeTab === 'services' ? 'is-active' : ''; ?>" data-tab="services">&#10024; &nbsp;Servicios (cuadros)</button>
        <button type="button" class="admin-tab <?php echo $activeTab === 'clients' ? 'is-active' : ''; ?>" data-tab="clients">&#128101; &nbsp;Clientas</button>
    </nav>

    <main class="admin-content">

        <?php if ($message): ?>
            <div class="message">
                &#10003; <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN 1: CITAS REGISTRADAS -->
        <section class="admin-panel <?php echo $activeTab === 'appointments' ? 'is-active' : ''; ?>" data-panel="appointments">
            <div class="admin-intro"><span>Gestión de reservas, aprobación y liberación de fechas:</span><span class="admin-count"><?php echo count($citas); ?> Citas Registradas</span></div>
            <div class="admin-card">
            <h2 class="text-lg font-serif font-bold text-gray-800 mb-4 flex items-center justify-between"><span>Citas Registradas</span></h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-600 font-bold">
                            <th class="p-3">Clienta</th>
                            <th class="p-3">Servicio</th>
                            <th class="p-3">Fecha y Hora</th>
                            <th class="p-3">Estado</th>
                            <th class="p-3">Precio</th>
                            <th class="p-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($citas as $c): ?>
                            <tr class="hover:bg-stone-50/80">
                                <td class="p-3">
                                    <strong class="text-stone-900 block"><?php echo htmlspecialchars($c['user_name']); ?></strong>
                                    <span class="text-stone-400"><?php echo htmlspecialchars($c['user_phone']); ?></span>
                                </td>
                                <td class="p-3 text-stone-800 font-medium"><?php echo htmlspecialchars($c['service_name']); ?></td>
                                <td class="p-3">
                                    <span class="font-bold text-stone-900"><?php echo $c['date']; ?></span>
                                    <span class="text-stone-500 block"><?php echo formatAdminTime($c['time_slot']); ?></span>
                                </td>
                                <td class="p-3">
                                    <?php if ($c['status'] === 'confirmed'): ?>
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px]">Confirmada</span>
                                    <?php elseif ($c['status'] === 'cancelled'): ?>
                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded-full text-[10px]">Cancelada</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded-full text-[10px]">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 font-bold">$<?php echo number_format($c['price'], 0, ',', '.'); ?></td>
                                <td class="p-3 text-right">
                                    <form method="POST" action="admin.php" class="inline-flex gap-1">
                                        <input type="hidden" name="action" value="update_cita">
                                        <input type="hidden" name="cita_id" value="<?php echo $c['id']; ?>">
                                        
                                        <?php if ($c['status'] === 'pending'): ?>
                                            <button type="submit" name="status" value="confirmed" class="admin-action confirm">&#10003; Confirmar cita</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($c['status'] !== 'cancelled'): ?>
                                            <button type="submit" name="status" value="cancelled" class="admin-action cancel">&#8856; Cancelar cita</button>
                                        <?php endif; ?>

                                        <button type="submit" name="status" value="delete" onclick="return confirm('¿Eliminar esta cita permanentemente?');" class="admin-action delete">&#128465; Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <section class="admin-panel <?php echo $activeTab === 'schedule' ? 'is-active' : ''; ?>" data-panel="schedule">
            <div class="schedule-box">
                <h2 class="text-sm font-bold uppercase tracking-wide text-gray-700">1. Selecciona la fecha para quitar o habilitar</h2>
                <form method="GET" action="admin.php" class="mt-3"><input type="hidden" name="view" value="schedule"><input type="date" name="schedule_date" class="admin-input" value="<?php echo htmlspecialchars($selectedDate); ?>" onchange="this.form.submit()"></form>
            </div>
            <div class="mt-8 flex items-center justify-between gap-3"><h2 class="text-sm font-bold uppercase tracking-wide text-gray-700">2. Control de turnos horarios</h2><form method="POST" action="admin.php"><input type="hidden" name="action" value="toggle_schedule"><input type="hidden" name="schedule_mode" value="day"><input type="hidden" name="schedule_date" value="<?php echo htmlspecialchars($selectedDate); ?>"><button type="submit" class="status <?php echo $isDayBlocked ? 'confirmed' : 'cancelled'; ?>"><?php echo $isDayBlocked ? '&#128275; Habilitar todo el día' : '&#128274; Bloquear todo el día'; ?></button></form></div>
            <p class="text-xs text-gray-500 mt-3">Presiona cualquier hora para quitar su disponibilidad o volver a habilitarla si una cita previa fue cancelada.</p>
            <div class="schedule-grid">
                <?php foreach ($scheduleSlots as $slot): $slotBlocked = $isDayBlocked || in_array($slot, $blockedSlots, true); ?>
                    <form method="POST" action="admin.php"><input type="hidden" name="action" value="toggle_schedule"><input type="hidden" name="schedule_mode" value="slot"><input type="hidden" name="schedule_date" value="<?php echo htmlspecialchars($selectedDate); ?>"><input type="hidden" name="schedule_slot" value="<?php echo $slot; ?>"><button type="submit" class="schedule-slot w-full"><span class="block text-gray-800"><?php echo formatAdminTime($slot); ?></span><span class="<?php echo $slotBlocked ? 'text-pink-600' : ''; ?>"><?php echo $slotBlocked ? 'Bloqueado' : 'Habilitado'; ?></span></button></form>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 pt-5 border-t border-gray-200"><h3 class="text-xs font-bold uppercase tracking-wide text-gray-600">Historial de fechas con modificaciones:</h3><p class="text-xs text-gray-400 italic mt-2">Todas las fechas están completamente habilitadas por defecto.</p></div>
        </section>

        <!-- SECCIÓN 2: GESTIÓN DE SERVICIOS (CUADROS) -->
        <section class="admin-panel <?php echo $activeTab === 'services' ? 'is-active' : ''; ?>" data-panel="services">
            <div class="admin-intro"><span>Modifica o agrega recuadros de servicios para la galería principal.</span><button type="button" class="admin-primary" data-add-service>+ Agregar cuadro adicional</button></div>
            <div class="admin-card">
            <h2 class="text-lg font-serif font-bold text-gray-800 mb-4">Servicios en Galería / Cuadros</h2>

            <!-- Formulario para agregar -->
            <form method="POST" action="admin.php" class="admin-form space-y-3" data-service-form>
                <input type="hidden" name="action" value="add_service">
                <h3 class="text-xs font-bold text-stone-700 uppercase tracking-wider">Añadir Nuevo Cuadro de Servicio</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                    <input type="text" name="nombre" required placeholder="Nombre del Servicio" class="admin-input">
                    <input type="number" name="precio" step="any" min="0" required placeholder="Precio libre COP" class="admin-input">
                    <input type="text" name="duracion" required placeholder="Duración (ej. 60 min)" class="admin-input">
                    <input type="text" name="descripcion" required placeholder="Descripción corta" class="admin-input sm:col-span-2">
                    <div class="sm:col-span-2 space-y-1">
                        <input type="file" id="php_file_input" accept="image/*" class="admin-input" onchange="const f=this.files[0]; if(f){const r=new FileReader(); r.onload=e=>document.getElementById('php_image_url').value=e.target.result; r.readAsDataURL(f);}">
                        <input type="text" id="php_image_url" name="image_url" required placeholder="O pega enlace/datos de la imagen" class="admin-input">
                    </div>
                    <input type="text" name="detalles" required placeholder="Detalles incluidos..." class="admin-input">
                </div>
                <button type="submit" class="admin-primary">
                    + Guardar Nuevo Servicio
                </button>
            </form>

            <!-- Lista de Cuadros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($servicios as $s): ?>
                    <div class="service-admin-card">
                        <div>
                            <div class="service-row"><img src="<?php echo htmlspecialchars($s['image_url']); ?>" alt="<?php echo htmlspecialchars($s['nombre']); ?>"><div class="flex-1"><div class="flex justify-between gap-2"><h4 class="font-serif font-bold text-gray-800 text-sm"><?php echo htmlspecialchars($s['nombre']); ?></h4><span class="price-tag">$<?php echo number_format($s['precio'], 0, ',', '.'); ?></span></div><p class="text-[10px] text-gray-500 mt-2"><?php echo htmlspecialchars($s['duracion']); ?></p><p class="service-description mt-2"><?php echo htmlspecialchars($s['descripcion']); ?></p></div></div>
                            <p class="service-details"><strong>Definición/Detalles completos:</strong><br><?php echo htmlspecialchars($s['detalles']); ?></p>
                        </div>
                        <details class="mt-3">
                            <summary class="admin-action inline-block cursor-pointer">&#9998; Modificar cuadro</summary>
                            <form method="POST" action="admin.php" class="admin-form mt-3 space-y-3">
                                <input type="hidden" name="action" value="update_service"><input type="hidden" name="service_id" value="<?php echo htmlspecialchars($s['id']); ?>">
                                <input type="text" name="nombre" required value="<?php echo htmlspecialchars($s['nombre']); ?>" class="admin-input" placeholder="Nombre del servicio">
                                <div class="grid grid-cols-2 gap-2"><input type="number" name="precio" step="any" min="0" required value="<?php echo htmlspecialchars($s['precio']); ?>" class="admin-input" placeholder="Precio"><input type="text" name="duracion" required value="<?php echo htmlspecialchars($s['duracion']); ?>" class="admin-input" placeholder="Duración"></div>
                                <textarea name="descripcion" required class="admin-input" rows="2" placeholder="Descripción corta"><?php echo htmlspecialchars($s['descripcion']); ?></textarea>
                                <textarea name="detalles" required class="admin-input" rows="3" placeholder="Detalles completos"><?php echo htmlspecialchars($s['detalles']); ?></textarea>
                                <label class="block text-[10px] font-bold text-gray-600">Elegir imagen desde tus archivos<input type="file" id="edit_image_file_<?php echo htmlspecialchars($s['id']); ?>" accept="image/*" class="admin-input mt-1" onchange="const file=this.files[0]; if(file){const reader=new FileReader(); reader.onload=event=>document.getElementById('edit_image_url_<?php echo htmlspecialchars($s['id']); ?>').value=event.target.result; reader.readAsDataURL(file);}"></label>
                                <input type="text" id="edit_image_url_<?php echo htmlspecialchars($s['id']); ?>" name="image_url" required value="<?php echo htmlspecialchars($s['image_url']); ?>" class="admin-input" placeholder="O pega el enlace de la imagen">
                                <button type="submit" class="admin-primary">Guardar cambios</button>
                            </form>
                        </details>
                        <form method="POST" action="admin.php" class="mt-3 pt-2 border-t border-stone-200 flex justify-end">
                            <input type="hidden" name="action" value="delete_service">
                            <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                            <button type="submit" onclick="return confirm('¿Eliminar este cuadro de servicio?');" class="admin-action delete">
                                &#128465; Eliminar cuadro
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
            </div>
        </section>

        <!-- SECCIÓN 3: CLIENTAS -->
        <section class="admin-panel <?php echo $activeTab === 'clients' ? 'is-active' : ''; ?>" data-panel="clients">
            <div class="admin-intro"><span>Clientas registradas y datos de contacto.</span><span class="admin-count"><?php echo count($clientas); ?> Clientas</span></div>
            <div class="admin-card">
            <h2 class="text-lg font-serif font-bold text-gray-800 mb-4">Clientas Registradas</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-stone-50 text-stone-600 font-bold border-b">
                            <th class="p-3">Nombre</th>
                            <th class="p-3">Teléfono / WhatsApp</th>
                            <th class="p-3">Correo Electrónico</th>
                            <th class="p-3">Fecha Registro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($clientas as $cli): ?>
                            <tr>
                                <td class="p-3 font-bold text-stone-900"><?php echo htmlspecialchars($cli['nombre']); ?></td>
                                <td class="p-3 text-stone-700"><?php echo htmlspecialchars($cli['telefono']); ?></td>
                                <td class="p-3 text-stone-700"><?php echo htmlspecialchars($cli['email']); ?></td>
                                <td class="p-3 text-stone-400"><?php echo $cli['registered_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

    </main>
    <footer class="admin-footer"><span>Usuario conectado: <strong><?php echo htmlspecialchars($user['nombre']); ?> (Admin)</strong></span><a href="index.php" class="font-bold text-teal-600">Cerrar panel</a></footer>
    </div>

    <script>
        const tabs = document.querySelectorAll('[data-tab]');
        const panels = document.querySelectorAll('[data-panel]');
        tabs.forEach((tab) => tab.addEventListener('click', () => {
            tabs.forEach((item) => item.classList.toggle('is-active', item === tab));
            panels.forEach((panel) => panel.classList.toggle('is-active', panel.dataset.panel === tab.dataset.tab));
        }));
        const serviceForm = document.querySelector('[data-service-form]');
        document.querySelector('[data-add-service]').addEventListener('click', () => {
            serviceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            serviceForm.querySelector('input[name="nombre"]').focus();
        });
    </script>

</body>
</html>
