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

// Cancelar cita si se solicitó
if (isset($_GET['cancel_id'])) {
    $cancelId = $_GET['cancel_id'];
    $stmtCancel = $pdo->prepare("UPDATE citas SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmtCancel->execute([$cancelId, $user['id']]);
    header("Location: mis_citas.php");
    exit();
}

// Obtener citas de la clienta
<<<<<<< HEAD
$stmtCitas = $pdo->prepare("SELECT * FROM citas WHERE user_id = ? ORDER BY date DESC, time_slot DESC");
=======
$stmtCitas = $pdo->prepare("SELECT citas.*, servicios.image_url FROM citas LEFT JOIN servicios ON servicios.id = citas.service_id WHERE citas.user_id = ? ORDER BY citas.date DESC, citas.time_slot DESC");
>>>>>>> e7ba4e9 (primer commit colegio)
$stmtCitas->execute([$user['id']]);
$citas = $stmtCitas->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Citas - María Nail Art & Spa</title>
<<<<<<< HEAD
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
=======
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #37414b; --muted: #718182; --mint: #43b5a6; --line: #d5e9e5; }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: linear-gradient(rgba(247, 251, 250, .88), rgba(247, 251, 250, .88)), url('hero-nails.jpg') center/cover fixed; font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .history-shell { width: min(100% - 48px, 920px); height: min(100vh - 72px, 620px); margin: 36px auto; display: flex; flex-direction: column; overflow: hidden; background: #f7fbfa; border: 1px solid var(--mint); border-radius: 24px; box-shadow: 0 15px 45px rgba(55, 65, 75, .18); }
        .history-header { display: flex; justify-content: space-between; align-items: center; padding: 24px 26px; background: rgba(255,255,255,.88); border-bottom: 1px solid var(--line); }
        .history-heading { display: flex; align-items: center; gap: 12px; }
        .history-icon { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; color: var(--mint); border: 1px solid #b7e3dc; border-radius: 50%; }
        .history-close { color: #8ca09f; font-size: 28px; line-height: 1; }
        .history-content { flex: 1; overflow-y: auto; padding: 24px; }
        .appointment-card { display: flex; gap: 16px; align-items: center; padding: 16px; margin-bottom: 12px; background: #fff; border: 1px solid var(--line); border-radius: 17px; box-shadow: 0 4px 12px rgba(55,65,75,.05); }
        .appointment-image { width: 64px; height: 64px; object-fit: cover; border-radius: 11px; }
        .appointment-info { flex: 1; min-width: 0; }
        .appointment-line { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .appointment-meta { margin-top: 7px; color: var(--muted); font-size: 11px; }
        .appointment-note { margin-top: 9px; padding: 8px 10px; color: var(--muted); background: #f5faf9; border: 1px solid #e5efed; border-radius: 9px; font-size: 11px; font-style: italic; }
        .status { padding: 5px 10px; border: 1px solid; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; white-space: nowrap; }
        .status.confirmed { color: #07984a; border-color: #b8edcf; background: #f2fff7; }
        .status.cancelled { color: #d65770; border-color: #f2b7c4; background: #fff7f8; }
        .status.pending { color: #b27d20; border-color: #f0d49c; background: #fffaf0; }
        .cancel-button { padding: 8px 11px; color: #d65770; background: #fff7f8; border: 1px solid #f2b7c4; border-radius: 9px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .cancel-button:hover { background: #ffecef; }
        .history-footer { display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; background: #fff; border-top: 1px solid var(--line); color: var(--muted); font-size: 11px; }
        .history-link { color: var(--mint); font-weight: 700; }
        @media (max-width: 640px) { .history-shell { width: 100%; height: 100vh; margin: 0; border-radius: 0; } .history-header { padding: 18px 16px; } .history-content { padding: 16px; } .appointment-card { align-items: flex-start; } .history-footer { padding: 12px 16px; } }
    </style>
</head>
<body>

    <div class="history-shell">
    <header class="history-header"><div class="history-heading"><span class="history-icon">&#128197;</span><div><h1 class="text-2xl font-serif font-bold text-gray-800">Mi Historial de Servicios</h1><p class="text-[10px] text-gray-500">Consulta tus citas de belleza y estados de reserva.</p></div></div><a href="index.php" class="history-close" aria-label="Cerrar">&times;</a></header>

    <main class="history-content">
        <div class="max-w-3xl mx-auto">
        <p class="text-xs text-gray-500 mb-5">Tienes <?php echo count($citas); ?> registro(s) en tu cuenta:</p>

        <?php if (empty($citas)): ?>
            <div class="bg-white p-8 rounded-2xl border border-gray-200 text-center">
                <p class="text-gray-500 text-sm">Aún no tienes citas agendadas.</p>
                <a href="agendar.php" class="inline-block mt-4 px-4 py-2 bg-teal-500 text-white text-xs font-bold rounded-xl">Agendar mi primera cita</a>
>>>>>>> e7ba4e9 (primer commit colegio)
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($citas as $cita): ?>
<<<<<<< HEAD
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
=======
                    <div class="appointment-card">
                        <img src="<?php echo htmlspecialchars($cita['image_url'] ?? 'hero-nails.jpg'); ?>" class="appointment-image" alt="<?php echo htmlspecialchars($cita['service_name']); ?>" onerror="this.src='hero-nails.jpg'">
                        <div class="appointment-info">
                            <div class="appointment-line">
                                <span class="font-serif font-bold text-gray-800"><?php echo htmlspecialchars($cita['service_name']); ?></span>
                                <?php if ($cita['status'] === 'confirmed'): ?>
                                    <span class="status confirmed">Confirmada</span>
                                <?php elseif ($cita['status'] === 'cancelled'): ?>
                                    <span class="status cancelled">Cancelada</span>
                                <?php else: ?>
                                    <span class="status pending">Pendiente</span>
                                <?php endif; ?>
                            </div>
                            <p class="appointment-meta">&#128197; <?php echo date('d M Y', strtotime($cita['date'])); ?> &nbsp;&nbsp; &#9719; <?php echo date('g:i A', strtotime($cita['time_slot'])); ?> &nbsp;&nbsp; <strong class="text-teal-700">$<?php echo number_format($cita['price'], 0, ',', '.'); ?></strong></p>
                            <?php if (!empty($cita['notes'])): ?>
                                <p class="appointment-note">“<?php echo htmlspecialchars($cita['notes']); ?>”</p>
>>>>>>> e7ba4e9 (primer commit colegio)
                            <?php endif; ?>
                        </div>

                        <?php if ($cita['status'] !== 'cancelled'): ?>
                            <a href="mis_citas.php?cancel_id=<?php echo $cita['id']; ?>" onclick="return confirm('¿Estás segura de cancelar esta cita?');"
<<<<<<< HEAD
                                class="text-xs px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold rounded-xl">
                                Cancelar Cita
=======
                                class="cancel-button">
                                Cancelar
>>>>>>> e7ba4e9 (primer commit colegio)
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

<<<<<<< HEAD
        <div class="mt-8 text-center text-xs text-stone-400">
            <a href="index.php" class="hover:underline">← Volver al Inicio</a>
        </div>
    </main>
=======
        </div>
    </main>
    <footer class="history-footer"><span>Cliente: <strong><?php echo htmlspecialchars($user['nombre']); ?></strong></span><a href="agendar.php" class="history-link">+ Reservar nueva</a></footer>
    </div>
>>>>>>> e7ba4e9 (primer commit colegio)

</body>
</html>
