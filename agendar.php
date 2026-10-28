<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user'])) {
<<<<<<< HEAD
    header("Location: login.php");
=======
    header("Location: index.php?auth=login");
>>>>>>> e7ba4e9 (primer commit colegio)
    exit();
}

$user = $_SESSION['user'];
$error = '';
$success = '';
<<<<<<< HEAD
=======
$scheduleSlots = ['08:00', '09:30', '11:00', '12:30', '14:00', '15:30', '17:00', '18:30'];
$selectedDate = $_POST['date'] ?? $_GET['date'] ?? date('Y-m-d');
$selectedServiceId = $_POST['service_id'] ?? $_GET['service_id'] ?? '';

function formatBookingTime($time) {
    $timestamp = strtotime($time);
    return $timestamp ? date('g:i A', $timestamp) : $time;
}
>>>>>>> e7ba4e9 (primer commit colegio)

// Obtener lista de servicios desde MySQL
$stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY nombre ASC");
$servicios = $stmtServicios->fetchAll();

<<<<<<< HEAD
=======
$stmtBlockedForView = $pdo->prepare("SELECT is_fully_blocked, turnos_bloqueados FROM fechas_bloqueadas WHERE fecha = ?");
$stmtBlockedForView->execute([$selectedDate]);
$blockedDateForView = $stmtBlockedForView->fetch();
$blockedSlotsForView = $blockedDateForView && $blockedDateForView['turnos_bloqueados'] ? array_filter(explode(',', $blockedDateForView['turnos_bloqueados'])) : [];
$fullyBlockedForView = $blockedDateForView && (int)$blockedDateForView['is_fully_blocked'] === 1;
$stmtOccupiedForView = $pdo->prepare("SELECT time_slot FROM citas WHERE date = ? AND status != 'cancelled'");
$stmtOccupiedForView->execute([$selectedDate]);
$occupiedSlotsForView = array_column($stmtOccupiedForView->fetchAll(), 'time_slot');

>>>>>>> e7ba4e9 (primer commit colegio)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($service_id) || empty($date) || empty($time_slot)) {
        $error = "Todos los campos del formulario son obligatorios.";
    } else {
<<<<<<< HEAD
        // Obtener datos del servicio seleccionado
        $stmtServ = $pdo->prepare("SELECT nombre, precio FROM servicios WHERE id = ?");
        $stmtServ->execute([$service_id]);
        $serviceInfo = $stmtServ->fetch();

        if (!$serviceInfo) {
            $error = "El servicio seleccionado no es válido.";
        } else {
=======
        // Respetar los bloqueos definidos por administración para el día y la hora.
        $stmtBlocked = $pdo->prepare("SELECT is_fully_blocked, turnos_bloqueados FROM fechas_bloqueadas WHERE fecha = ?");
        $stmtBlocked->execute([$date]);
        $blockedDate = $stmtBlocked->fetch();
        $blockedSlots = $blockedDate && $blockedDate['turnos_bloqueados'] ? array_filter(explode(',', $blockedDate['turnos_bloqueados'])) : [];

        if ($blockedDate && ((int)$blockedDate['is_fully_blocked'] === 1 || in_array($time_slot, $blockedSlots, true))) {
            $error = "Este día u horario no está disponible. Por favor elige otra fecha u hora.";
        } else {
            // Obtener datos del servicio seleccionado
            $stmtServ = $pdo->prepare("SELECT nombre, precio FROM servicios WHERE id = ?");
            $stmtServ->execute([$service_id]);
            $serviceInfo = $stmtServ->fetch();

            if (!$serviceInfo) {
                $error = "El servicio seleccionado no es válido.";
            } else {
>>>>>>> e7ba4e9 (primer commit colegio)
            // Verificar si el horario ya está ocupado en la misma fecha
            $stmtCheck = $pdo->prepare("SELECT id FROM citas WHERE date = ? AND time_slot = ? AND status != 'cancelled'");
            $stmtCheck->execute([$date, $time_slot]);
            
            if ($stmtCheck->fetch()) {
                $error = "Lo sentimos, el horario $time_slot para el día $date ya se encuentra ocupado. Por favor elige otro horario.";
            } else {
                $appId = 'app-' . time();
                $stmtInsert = $pdo->prepare("INSERT INTO citas (id, user_id, user_name, user_phone, service_id, service_name, date, time_slot, status, price, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
                
                if ($stmtInsert->execute([$appId, $user['id'], $user['nombre'], $user['telefono'], $service_id, $serviceInfo['nombre'], $date, $time_slot, $serviceInfo['precio'], $notes])) {
                    $success = "¡Cita reservada con éxito para el $date a las $time_slot hs!";
                } else {
                    $error = "Ocurrió un error al guardar la cita en MySQL.";
                }
            }
<<<<<<< HEAD
=======
            }
>>>>>>> e7ba4e9 (primer commit colegio)
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - María Nail Art & Spa</title>
<<<<<<< HEAD
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-amber-50/40 text-stone-800 min-h-screen">

    <header class="bg-white/90 backdrop-blur-md border-b border-amber-200/60 sticky top-0 z-50 px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-xl font-serif font-bold text-stone-900 tracking-tight">María <span class="text-[#43AFA0]">Nail Art & Spa</span></a>
        <div class="flex items-center gap-4 text-xs">
            <span class="font-semibold text-stone-700">Hola, <?php echo htmlspecialchars($user['nombre']); ?></span>
            <a href="mis_citas.php" class="text-amber-800 font-bold hover:underline">Mis Citas</a>
            <a href="logout.php" class="text-rose-600 hover:underline">Cerrar Sesión</a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-10">
        <div class="bg-white border border-amber-200/80 rounded-3xl p-8 shadow-xl">
            <h1 class="text-2xl font-serif font-bold text-stone-900 mb-2">Agendar Nueva Cita</h1>
            <p class="text-stone-500 text-xs mb-6">Selecciona el servicio, la fecha y la hora deseada. Los datos se guardarán directamente en la base de datos MySQL.</p>
=======
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #37414b; --muted: #718182; --mint: #43b5a6; --mint-soft: #eff9f7; --line: #d5e9e5; }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: linear-gradient(rgba(247, 251, 250, .9), rgba(247, 251, 250, .9)), url('hero-nails.jpg') center/cover fixed; font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .booking-shell { width: min(100% - 24px, 1040px); height: min(100vh - 24px, 860px); margin: 12px auto; display: flex; flex-direction: column; overflow: hidden; background: #f7fbfa; border: 1px solid var(--mint); border-radius: 24px; box-shadow: 0 15px 45px rgba(55, 65, 75, .18); }
        .booking-header { display: flex; justify-content: space-between; align-items: center; padding: 18px 26px; background: rgba(255, 255, 255, .86); border-bottom: 1px solid var(--line); }
        .booking-heading { display: flex; align-items: center; gap: 12px; }
        .booking-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; color: var(--mint); border: 1px solid #b7e3dc; border-radius: 50%; }
        .booking-close { color: #8ca09f; font-size: 28px; line-height: 1; }
        .booking-content { flex: 1; overflow-y: auto; padding: 24px; }
        .service-choice { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; }
        .service-option { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 13px; color: var(--ink); background: #fff; border: 1px solid var(--line); border-radius: 14px; cursor: pointer; }
        .service-option:has(input:checked) { border-color: var(--mint); box-shadow: 0 0 0 2px rgba(67, 181, 166, .16); background: var(--mint-soft); }
        .service-option input { accent-color: var(--mint); }
        .field-label { display: block; color: #536869; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .booking-input { width: 100%; margin-top: 6px; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid var(--line); border-radius: 11px; font-size: 12px; outline: none; }
        .booking-input:focus { border-color: var(--mint); box-shadow: 0 0 0 3px rgba(67, 181, 166, .14); }
        .booking-note { color: var(--muted); background: var(--mint-soft); border: 1px solid var(--line); border-radius: 14px; }
        .time-choice { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 8px; }
        .time-option { display: block; padding: 13px 8px; text-align: center; color: var(--ink); background: #fff; border: 1px solid var(--line); border-radius: 12px; cursor: pointer; font-size: 12px; font-weight: 700; }
        .time-option:has(input:checked) { color: #087f72; border-color: var(--mint); background: var(--mint-soft); box-shadow: 0 0 0 2px rgba(67,181,166,.14); }
        .time-option input { position: absolute; opacity: 0; pointer-events: none; }
        .time-option small { display: block; margin-top: 4px; color: #08a64d; font-size: 10px; text-transform: uppercase; }
        .time-option.is-disabled { color: #9a747d; background: #fff7f8; border-color: #f2c6cf; cursor: not-allowed; }
        .time-option.is-disabled small { color: #d65770; }
        .booking-footer { padding: 14px 24px; background: #fff; border-top: 1px solid var(--line); }
        .booking-submit { width: 100%; min-height: 46px; color: #fff; background: var(--mint); border-radius: 11px; font-family: 'Playfair Display', serif; font-size: 15px; font-weight: 700; }
        .booking-submit:hover { background: var(--ink); }
        @media (max-width: 640px) { .booking-shell { width: 100%; height: 100vh; margin: 0; border-radius: 0; } .booking-header { padding: 16px; } .booking-content { padding: 16px; } .booking-footer { padding: 12px 16px; } }
    </style>
</head>
<body>

    <div class="booking-shell">
    <header class="booking-header">
        <div class="booking-heading"><span class="booking-icon">&#128197;</span><div><h1 class="text-2xl font-serif font-bold text-gray-800">Reserva tu Cita en Línea</h1><p class="text-[10px] text-gray-500">Completa tu reserva en menos de un minuto.</p></div></div>
        <a href="index.php" class="booking-close" aria-label="Cerrar">&times;</a>
    </header>

    <main class="booking-content">
        <div class="max-w-4xl mx-auto">
            <p class="field-label mb-3">1. Elige tu servicio</p>
            <div class="service-choice mb-6">
                <?php foreach ($servicios as $serv): ?>
                    <label class="service-option"><span><input type="radio" name="service_choice" value="<?php echo $serv['id']; ?>" form="booking-form" required <?php echo $selectedServiceId == $serv['id'] ? 'checked' : ''; ?>><strong class="ml-2 text-xs"><?php echo htmlspecialchars($serv['nombre']); ?></strong><small class="block ml-6 mt-1 text-[10px] text-gray-500"><?php echo $serv['duracion']; ?></small></span><strong class="text-sm text-teal-700">$<?php echo number_format($serv['precio'], 0, ',', '.'); ?></strong></label>
                <?php endforeach; ?>
            </div>
>>>>>>> e7ba4e9 (primer commit colegio)

            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-semibold">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl font-semibold">
                    ✨ <?php echo htmlspecialchars($success); ?>
                    <div class="mt-2 text-xs">
                        <a href="mis_citas.php" class="text-emerald-900 underline font-bold">Ver todas mis citas</a>
                    </div>
                </div>
            <?php endif; ?>

<<<<<<< HEAD
            <form method="POST" action="agendar.php" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Servicio Deseado</label>
                    <select name="service_id" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">-- Selecciona un servicio --</option>
                        <?php foreach ($servicios as $serv): ?>
                            <option value="<?php echo $serv['id']; ?>" <?php echo (isset($_GET['service_id']) && $_GET['service_id'] == $serv['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($serv['nombre']); ?> - $<?php echo number_format($serv['precio'], 0, ',', '.'); ?> COP (<?php echo $serv['duracion']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-stone-600 mb-1">Fecha de la Cita</label>
                        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-stone-600 mb-1">Horario Disponible</label>
                        <select name="time_slot" required class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="08:00">08:00 AM</option>
                            <option value="09:30">09:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="14:00">02:00 PM</option>
                            <option value="15:30">03:30 PM</option>
                            <option value="17:00">05:00 PM</option>
                        </select>
=======
            <form id="booking-form" method="POST" action="agendar.php" class="space-y-5">
                <input type="hidden" name="service_id" id="selected-service">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">2. Elige la fecha</label>
                        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selectedDate); ?>" onchange="const service=document.querySelector('input[name=service_choice]:checked'); window.location='agendar.php?date='+encodeURIComponent(this.value)+(service ? '&service_id='+encodeURIComponent(service.value) : '')"
                            class="booking-input">
                    </div>

                    <div>
                        <label class="field-label">3. Datos del cliente</label><div class="booking-input text-gray-600">&#128100; <?php echo htmlspecialchars($user['nombre']); ?> &nbsp; &#128222; <?php echo htmlspecialchars($user['telefono']); ?></div>
>>>>>>> e7ba4e9 (primer commit colegio)
                    </div>
                </div>

                <div>
<<<<<<< HEAD
                    <label class="block text-xs font-semibold text-stone-600 mb-1">Notas o Solicitudes Especiales (Opcional)</label>
                    <textarea name="notes" rows="3" placeholder="Ej. Diseño personalizado con tonos rosa pastel y flores..."
                        class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <button type="submit" class="w-full py-3.5 bg-[#43AFA0] hover:bg-[#359B8D] text-white font-serif font-bold rounded-xl transition-all shadow-md text-sm cursor-pointer">
                    Confirmar Reserva
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-stone-400">
                <a href="index.php" class="hover:underline">← Volver al Menú Principal</a>
            </div>
        </div>
    </main>
=======
                    <label class="field-label">4. Selecciona el horario disponible</label>
                    <p class="text-[11px] text-gray-500 mt-2">Horarios para el <?php echo date('d/m/Y', strtotime($selectedDate)); ?>. Los horarios bloqueados u ocupados no se pueden seleccionar.</p>
                    <div class="time-choice">
                        <?php foreach ($scheduleSlots as $slot): $isBlocked = $fullyBlockedForView || in_array($slot, $blockedSlotsForView, true); $isOccupied = in_array($slot, $occupiedSlotsForView, true); $isUnavailable = $isBlocked || $isOccupied; $statusLabel = $isBlocked ? 'Bloqueado' : ($isOccupied ? 'Ocupado' : 'Disponible'); ?>
                            <label class="time-option <?php echo $isUnavailable ? 'is-disabled' : ''; ?>"><input type="radio" name="time_slot" value="<?php echo $slot; ?>" <?php echo $isUnavailable ? 'disabled' : ''; ?> required><span><?php echo formatBookingTime($slot); ?></span><small><?php echo $statusLabel; ?></small></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label class="field-label">5. Notas adicionales / ideas de diseño (opcional)</label>
                    <textarea name="notes" rows="3" placeholder="Ej. Diseño personalizado con tonos rosa pastel y flores..."
                        class="booking-input"></textarea>
                </div>
            </form>
        </div>
    </main>
    <footer class="booking-footer"><button type="submit" form="booking-form" class="booking-submit">&#10003;&nbsp; Confirmar Cita y Enviar Cita</button></footer>
    </div>

    <script>document.querySelectorAll('input[name="service_choice"]').forEach((input) => input.addEventListener('change', () => { document.getElementById('selected-service').value = input.value; })); const selected = document.querySelector('input[name="service_choice"]:checked'); if (selected) document.getElementById('selected-service').value = selected.value;</script>
>>>>>>> e7ba4e9 (primer commit colegio)

</body>
</html>
