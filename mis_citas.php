<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Cancelar cita si se solicitó
if (isset($_GET['cancel_id'])) {
    $cancelId = $_GET['cancel_id'];
    $stmtCancel = $pdo->prepare("UPDATE citas SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmtCancel->execute([$cancelId, $user['id']]);
    header("Location: mis_citas.php");
    exit();
}

// Obtener citas de la clienta
$stmtCitas = $pdo->prepare("SELECT * FROM citas WHERE user_id = ? ORDER BY date DESC, time_slot DESC");
$stmtCitas->execute([$user['id']]);
$citas = $stmtCitas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - María Nail Art & Spa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-amber-50/40 text-stone-800 min-h-screen">

    <header class="bg-white/90 border-b border-amber-200/60 px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="text-xl font-serif font-bold text-stone-900">María <span class="text-[#43AFA0]">Nail Art</span></a>
        <div class="flex items-center gap-4 text-xs">
            <a href="agendar.php" class="bg-[#43AFA0] text-white px-3 py-1.5 rounded-lg font-bold hover:bg-[#359B8D]">Agendar Cita</a>
            <a href="logout.php" class="text-rose-600 hover:underline">Cerrar Sesión</a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-serif font-bold text-stone-900 mb-1">Mis Citas Reservadas</h1>
        <p class="text-stone-500 text-xs mb-6">Consulta el historial y estado de tus citas guardadas en MySQL.</p>

        <?php if (empty($citas)): ?>
            <div class="bg-white p-8 rounded-2xl border border-stone-200 text-center">
                <p class="text-stone-500 text-sm">Aún no tienes citas agendadas.</p>
                <a href="agendar.php" class="inline-block mt-4 px-4 py-2 bg-[#43AFA0] text-white text-xs font-bold rounded-xl">Agendar mi primera cita</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($citas as $cita): ?>
                    <div class="bg-white border border-amber-200/80 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-stone-900"><?php echo htmlspecialchars($cita['service_name']); ?></span>
                                <?php if ($cita['status'] === 'confirmed'): ?>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full">Confirmada</span>
                                <?php elseif ($cita['status'] === 'cancelled'): ?>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 text-[10px] font-bold rounded-full">Cancelada</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-full">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-stone-600">📅 Fecha: <strong><?php echo $cita['date']; ?></strong> | ⏰ Hora: <strong><?php echo $cita['time_slot']; ?> hs</strong></p>
                            <p class="text-xs text-stone-500 mt-1">Precio: $<?php echo number_format($cita['price'], 0, ',', '.'); ?> COP</p>
                            <?php if (!empty($cita['notes'])): ?>
                                <p class="text-xs text-stone-400 italic mt-1">Nota: <?php echo htmlspecialchars($cita['notes']); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($cita['status'] !== 'cancelled'): ?>
                            <a href="mis_citas.php?cancel_id=<?php echo $cita['id']; ?>" onclick="return confirm('¿Estás segura de cancelar esta cita?');"
                                class="text-xs px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold rounded-xl">
                                Cancelar Cita
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="mt-8 text-center text-xs text-stone-400">
            <a href="index.php" class="hover:underline">← Volver al Inicio</a>
        </div>
    </main>

</body>
</html>
