<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$error = '';
$success = '';

// Obtener lista de servicios desde MySQL
$stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY nombre ASC");
$servicios = $stmtServicios->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? '';
    $date = $_POST['date'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($service_id) || empty($date) || empty($time_slot)) {
        $error = "Todos los campos del formulario son obligatorios.";
    } else {
        // Obtener datos del servicio seleccionado
        $stmtServ = $pdo->prepare("SELECT nombre, precio FROM servicios WHERE id = ?");
        $stmtServ->execute([$service_id]);
        $serviceInfo = $stmtServ->fetch();

        if (!$serviceInfo) {
            $error = "El servicio seleccionado no es válido.";
        } else {
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
                    </div>
                </div>

                <div>
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

</body>
</html>
