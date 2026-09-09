<?php
require_once __DIR__ . '/conexion.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "El correo electrónico y la contraseña son obligatorios.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "No existe una cuenta registrada con este correo.";
        } elseif ($user['password'] !== $password) {
            $error = "La contraseña ingresada es incorrecta.";
        } else {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'telefono' => $user['telefono'],
                'is_admin' => (int)$user['is_admin']
            ];
            header("Location: index.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - María Nail Art & Spa</title>
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
            <h1 class="text-2xl font-serif font-bold text-stone-900">Iniciar Sesión</h1>
            <p class="text-stone-500 text-xs mt-1">María Nail Art & Spa - Proyecto PHP & MySQL</p>
        </div>

        <!-- Quick Access Box -->
        <div class="p-3 bg-amber-50 border border-amber-200/80 rounded-2xl mb-6 text-xs text-amber-900 space-y-1">
            <p class="font-bold">Acceso para pruebas:</p>
            <p>• Admin: <code class="bg-amber-100 px-1 rounded">admin@marianailart.com</code> | Clave: <code class="bg-amber-100 px-1 rounded">admin123</code></p>
            <p>• Clienta: <code class="bg-amber-100 px-1 rounded">sofia@gmail.com</code> | Clave: <code class="bg-amber-100 px-1 rounded">sofia123</code></p>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-semibold">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="tu@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-600 mb-1">Contraseña (Obligatoria)</label>
                <input type="password" name="password" required placeholder="••••••••"
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <button type="submit" class="w-full py-3 bg-[#43AFA0] hover:bg-[#359B8D] text-white font-serif font-bold rounded-xl transition-all shadow-md text-sm cursor-pointer">
                Iniciar Sesión
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-stone-500">
            ¿No tienes cuenta? <a href="register.php" class="text-amber-700 font-bold hover:underline">Regístrate aquí</a>
            <br>
            <a href="index.php" class="inline-block mt-3 text-stone-400 hover:text-stone-600">← Volver al Inicio</a>
        </div>
    </div>

</body>
</html>
