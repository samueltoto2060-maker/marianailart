<?php
require_once __DIR__ . '/conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $telefono = preg_replace('/\s+/', '', trim($_POST['telefono'] ?? ''));
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($nombre) || strlen($nombre) < 3) {
        $error = "Por favor ingresa un nombre completo de al menos 3 caracteres.";
    } elseif (!preg_match('/^\d{10}$/', $telefono)) {
        $error = "El número de celular debe tener exactamente 10 dígitos (ej. 3101234567).";
    } elseif (!str_ends_with($email, '@gmail.com') && !str_ends_with($email, '@hotmail.com')) {
        $error = "Solo se permite el registro con correos @gmail.com o @hotmail.com";
    } elseif (empty($password) || strlen($password) < 6) {
        $error = "La contraseña es obligatoria y debe tener al menos 6 caracteres.";
    } else {
        // Verificar si el correo ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Este correo electrónico ya está registrado.";
        } else {
            $userId = 'user-' . time();
            $stmtInsert = $pdo->prepare("INSERT INTO usuarios (id, nombre, telefono, email, password, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
            
            if ($stmtInsert->execute([$userId, $nombre, $telefono, $email, $password])) {
                $_SESSION['user'] = [
                    'id' => $userId,
                    'nombre' => $nombre,
                    'email' => $email,
                    'telefono' => $telefono,
                    'is_admin' => 0
                ];
                header("Location: index.php");
                exit();
            } else {
                $error = "Ocurrió un error al guardar el registro en MySQL.";
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
    <title>Crear Cuenta - María Nail Art & Spa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-amber-50/40 text-stone-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white border border-amber-200/80 rounded-3xl p-8 shadow-xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-serif font-bold text-stone-900">Crear Cuenta</h1>
            <p class="text-stone-500 text-xs mt-1">María Nail Art & Spa - Registro PHP MySQL</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-semibold">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Nombre Completo</label>
                <input type="text" name="nombre" required minlength="3" placeholder="Ej. Sofía Restrepo" value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Celular / WhatsApp (Exactamente 10 dígitos)</label>
                <input type="tel" name="telefono" required pattern="\d{10}" maxlength="10" placeholder="Ej. 3147890123" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Correo Electrónico (@gmail.com o @hotmail.com)</label>
                <input type="email" name="email" required placeholder="tu_correo@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Contraseña (Mínimo 6 caracteres)</label>
                <input type="password" name="password" required minlength="6" placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <button type="submit" class="w-full py-3 bg-[#43AFA0] hover:bg-[#359B8D] text-white font-serif font-bold rounded-xl transition-all shadow-md text-sm cursor-pointer">
                Registrarme
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-stone-500">
            ¿Ya tienes cuenta? <a href="login.php" class="text-amber-700 font-bold hover:underline">Inicia sesión</a>
            <br>
            <a href="index.php" class="inline-block mt-3 text-stone-400 hover:text-stone-600">← Volver al Inicio</a>
        </div>
    </div>

</body>
</html>
