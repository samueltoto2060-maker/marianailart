<?php
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    die("Acceso denegado. Esta sección requiere permisos de Administrador. <a href='login.php'>Iniciar sesión como Admin</a>");
}

$user = $_SESSION['user'];
$message = '';

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
    <title>Panel de Administración - María Nail Art & Spa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body { font-family: sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 min-h-screen">

    <header class="bg-stone-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div>
            <h1 class="font-serif font-bold text-lg text-amber-400">Panel Admin - María Nail Art</h1>
            <p class="text-[10px] text-stone-400">Conectado a MySQL `maria_nail_art`</p>
        </div>
        <div class="flex items-center gap-4 text-xs">
            <span class="text-stone-300">Admin: <strong><?php echo htmlspecialchars($user['nombre']); ?></strong></span>
            <a href="index.php" class="px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg">Ver Sitio Web</a>
            <a href="logout.php" class="text-rose-400 hover:underline">Salir</a>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8 space-y-8">

        <?php if ($message): ?>
            <div class="p-4 bg-emerald-100 border border-emerald-300 text-emerald-900 text-sm font-semibold rounded-2xl shadow-sm">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- SECCIÓN 1: CITAS REGISTRADAS -->
        <section class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200">
            <h2 class="text-xl font-serif font-bold text-stone-900 mb-4 flex items-center justify-between">
                <span>📅 Citas Registradas (<?php echo count($citas); ?>)</span>
            </h2>

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
                                    <span class="text-stone-500 block"><?php echo $c['time_slot']; ?> hs</span>
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
                                            <button type="submit" name="status" value="confirmed" class="px-2 py-1 bg-emerald-600 text-white font-bold rounded hover:bg-emerald-700">Confirmar</button>
                                        <?php endif; ?>
                                        
                                        <?php if ($c['status'] !== 'cancelled'): ?>
                                            <button type="submit" name="status" value="cancelled" class="px-2 py-1 bg-amber-600 text-white font-bold rounded hover:bg-amber-700">Cancelar</button>
                                        <?php endif; ?>

                                        <button type="submit" name="status" value="delete" onclick="return confirm('¿Eliminar esta cita permanentemente?');" class="px-2 py-1 bg-rose-600 text-white font-bold rounded hover:bg-rose-700">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- SECCIÓN 2: GESTIÓN DE SERVICIOS (CUADROS) -->
        <section class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200">
            <h2 class="text-xl font-serif font-bold text-stone-900 mb-4">✨ Servicios en Galería / Cuadros</h2>

            <!-- Formulario para agregar -->
            <form method="POST" action="admin.php" class="bg-stone-50 p-4 rounded-2xl border border-stone-200 mb-6 space-y-3">
                <input type="hidden" name="action" value="add_service">
                <h3 class="text-xs font-bold text-stone-700 uppercase tracking-wider">Añadir Nuevo Cuadro de Servicio</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                    <input type="text" name="nombre" required placeholder="Nombre del Servicio" class="p-2 border rounded-xl bg-white">
                    <input type="number" name="precio" step="any" min="0" required placeholder="Precio libre COP (ej. 35000, 48000)" class="p-2 border rounded-xl bg-white font-bold text-amber-900">
                    <input type="text" name="duracion" required placeholder="Duración (ej. 60 min)" class="p-2 border rounded-xl bg-white">
                    <input type="text" name="descripcion" required placeholder="Descripción corta" class="p-2 border rounded-xl bg-white sm:col-span-2">
                    <select name="categoria" class="p-2 border rounded-xl bg-white">
                        <option value="manicure">Manicure</option>
                        <option value="pedicure">Pedicure</option>
                        <option value="acrylic">Acrílicas</option>
                        <option value="nailart">Nail Art</option>
                    </select>
                    <div class="sm:col-span-2 space-y-1">
                        <input type="file" id="php_file_input" accept="image/*" class="text-xs p-1.5 border rounded-xl bg-white w-full" onchange="const f=this.files[0]; if(f){const r=new FileReader(); r.onload=e=>document.getElementById('php_image_url').value=e.target.result; r.readAsDataURL(f);}">
                        <input type="text" id="php_image_url" name="image_url" required placeholder="O pega enlace/datos de la imagen" class="p-2 border rounded-xl bg-white w-full text-xs">
                    </div>
                    <input type="text" name="detalles" required placeholder="Detalles incluidos..." class="p-2 border rounded-xl bg-white">
                </div>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-sm">
                    + Guardar Nuevo Servicio
                </button>
            </form>

            <!-- Lista de Cuadros -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($servicios as $s): ?>
                    <div class="border border-stone-200 rounded-2xl p-4 bg-stone-50 flex flex-col justify-between">
                        <div>
                            <img src="<?php echo htmlspecialchars($s['image_url']); ?>" class="w-full h-32 object-cover rounded-xl mb-2">
                            <h4 class="font-bold text-stone-900 text-sm"><?php echo htmlspecialchars($s['nombre']); ?></h4>
                            <p class="text-xs text-stone-500 mb-1"><?php echo htmlspecialchars($s['descripcion']); ?></p>
                            <p class="text-xs font-bold text-amber-900">$<?php echo number_format($s['precio'], 0, ',', '.'); ?> COP (<?php echo $s['duracion']; ?>)</p>
                        </div>
                        <form method="POST" action="admin.php" class="mt-3 pt-2 border-t border-stone-200 flex justify-end">
                            <input type="hidden" name="action" value="delete_service">
                            <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                            <button type="submit" onclick="return confirm('¿Eliminar este cuadro de servicio?');" class="text-xs px-3 py-1 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700">
                                🗑️ Eliminar Cuadro
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECCIÓN 3: CLIENTAS -->
        <section class="bg-white rounded-3xl p-6 shadow-sm border border-stone-200">
            <h2 class="text-xl font-serif font-bold text-stone-900 mb-4">👥 Clientas Registradas en MySQL (<?php echo count($clientas); ?>)</h2>
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
        </section>

    </main>

</body>
</html>
