<?php
require_once __DIR__ . '/conexion.php';

$user = $_SESSION['user'] ?? null;
$authError = '';
$authPanel = ($_GET['auth'] ?? '') === 'register' ? 'register' : 'login';
$authRequested = isset($_GET['auth']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_action'])) {
    $authPanel = $_POST['auth_action'] === 'register' ? 'register' : 'login';

    if ($authPanel === 'login') {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $authError = 'El correo electrónico y la contraseña son obligatorios.';
        } else {
            $stmtLogin = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
            $stmtLogin->execute([$email]);
            $loginUser = $stmtLogin->fetch();

            if (!$loginUser) {
                $authError = 'No existe una cuenta registrada con este correo.';
            } elseif ($loginUser['password'] !== $password) {
                $authError = 'La contraseña ingresada es incorrecta.';
            } else {
                $_SESSION['user'] = [
                    'id' => $loginUser['id'],
                    'nombre' => $loginUser['nombre'],
                    'email' => $loginUser['email'],
                    'telefono' => $loginUser['telefono'],
                    'is_admin' => (int)$loginUser['is_admin']
                ];
                header('Location: index.php');
                exit();
            }
        }
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = preg_replace('/\s+/', '', trim($_POST['telefono'] ?? ''));
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        if (empty($nombre) || strlen($nombre) < 3) {
            $authError = 'Por favor ingresa un nombre completo de al menos 3 caracteres.';
        } elseif (!preg_match('/^\d{10}$/', $telefono)) {
            $authError = 'El número de celular debe tener exactamente 10 dígitos.';
        } elseif (!str_ends_with($email, '@gmail.com') && !str_ends_with($email, '@hotmail.com')) {
            $authError = 'Solo se permite el registro con correos @gmail.com o @hotmail.com.';
        } elseif (empty($password) || strlen($password) < 6) {
            $authError = 'La contraseña debe tener al menos 6 caracteres.';
        } else {
            $stmtExisting = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
            $stmtExisting->execute([$email]);

            if ($stmtExisting->fetch()) {
                $authError = 'Este correo electrónico ya está registrado.';
            } else {
                $userId = 'user-' . time();
                $stmtRegister = $pdo->prepare('INSERT INTO usuarios (id, nombre, telefono, email, password, is_admin) VALUES (?, ?, ?, ?, ?, 0)');

                if ($stmtRegister->execute([$userId, $nombre, $telefono, $email, $password])) {
                    $_SESSION['user'] = [
                        'id' => $userId,
                        'nombre' => $nombre,
                        'email' => $email,
                        'telefono' => $telefono,
                        'is_admin' => 0
                    ];
                    header('Location: index.php');
                    exit();
                }

                $authError = 'Ocurrió un error al guardar el registro.';
            }
        }
    }
}

// Obtener servicios de la base de datos MySQL
$stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY created_at DESC");
$servicios = $stmtServicios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>María Nail Art | Manicure, diseño y spa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #37414b;
            --muted: #727780;
            --cream: #fbf8f4;
            --paper: #fffdfb;
            --blush: #e9a9b8;
            --blush-deep: #c97991;
            --mint: #a8ddd5;
            --mint-deep: #4eaaa1;
            --line: #eadfdc;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            background: var(--cream);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-serif { font-family: 'Playfair Display', serif; }
        .site-header { background: rgba(255, 253, 251, .9); border-color: var(--line); }
        .brand-mark { border: 1px solid rgba(201, 121, 145, .45); box-shadow: 0 5px 16px rgba(55, 65, 75, .1); }
        .hero { background: radial-gradient(circle at 15% 20%, rgba(168, 221, 213, .42), transparent 30%), radial-gradient(circle at 85% 15%, rgba(233, 169, 184, .28), transparent 28%), var(--paper); }
        .hero-photo { aspect-ratio: 4 / 5; box-shadow: 18px 20px 0 rgba(168, 221, 213, .52), 0 22px 50px rgba(55, 65, 75, .18); }
        .eyebrow { color: var(--mint-deep); letter-spacing: .22em; }
        .primary-button { background: var(--ink); box-shadow: 0 9px 18px rgba(55, 65, 75, .18); }
        .primary-button:hover { background: var(--mint-deep); }
        .secondary-button { border-color: var(--blush); color: var(--ink); }
        .secondary-button:hover { background: #fff2f4; }
        .section-kicker { color: var(--blush-deep); letter-spacing: .18em; }
        .service-card { background: var(--paper); border-color: var(--line); box-shadow: 0 10px 26px rgba(55, 65, 75, .06); }
        .service-card:hover { border-color: var(--mint); box-shadow: 0 18px 34px rgba(55, 65, 75, .12); transform: translateY(-4px); }
        .service-note { background: #f1fbf9; border-color: #d7eee9; }
        .price { color: var(--blush-deep); }
        .footer { background: var(--ink); }
        .footer-accent { color: var(--mint); }
        .nav-link { position: relative; color: var(--ink); transition: color .2s ease; }
        .nav-link::after { content: ''; position: absolute; left: 0; right: 0; bottom: -9px; height: 2px; border-radius: 999px; background: var(--blush); transform: scaleX(0); transform-origin: center; transition: transform .2s ease; }
        .nav-link:hover { color: var(--blush-deep); }
        .nav-link:hover::after { transform: scaleX(1); }
        .auth-actions { display: flex; align-items: center; gap: 5px; padding: 5px; background: #fff; border: 1px solid var(--line); border-radius: 999px; box-shadow: 0 5px 16px rgba(55, 65, 75, .07); }
        .auth-login, .auth-register { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 0 16px; border-radius: 999px; font-weight: 700; transition: all .2s ease; }
        .auth-login { color: var(--blush-deep); border: 1px solid var(--blush); background: #fff; }
        .auth-login:hover { background: #fff1f4; color: #a5526e; }
        .auth-register { color: #fff; background: var(--mint-deep); box-shadow: 0 4px 10px rgba(78, 170, 161, .25); }
        .auth-register:hover { background: var(--ink); transform: translateY(-1px); }
        .user-actions { display: flex; align-items: center; gap: 8px; }
        .location-section { background: #effaf8; border-top: 1px solid #d8efeb; border-bottom: 1px solid #d8efeb; }
        .location-card { background: rgba(255, 255, 255, .78); border: 1px solid #d7eee9; border-radius: 16px; }
        .location-card:hover { border-color: var(--mint); background: #fff; }
        .location-icon { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; flex-shrink: 0; color: var(--mint-deep); background: #e8f8f5; border: 1px solid #ccebe5; border-radius: 50%; }
        .private-note { color: #fff; background: var(--ink); border-radius: 16px; }
        .map-frame { min-height: 360px; border: 4px solid #fff; border-radius: 22px; box-shadow: 0 12px 28px rgba(55, 65, 75, .16); overflow: hidden; }
        .map-frame iframe { width: 100%; height: 100%; min-height: 360px; border: 0; filter: saturate(.8); }
        .maps-button { display: inline-flex; align-items: center; gap: 8px; color: #fff; background: #43b5a6; box-shadow: 0 8px 16px rgba(67, 181, 166, .2); }
        .maps-button:hover { background: var(--ink); }
        .contact-section { background: var(--paper); border-top: 1px solid var(--line); }
        .contact-profile { background: #f1fbf9; border: 1px solid #d7eee9; }
        .contact-form { background: #fff; border: 1px solid #d7eee9; box-shadow: 0 12px 28px rgba(55, 65, 75, .1); }
        .contact-input { width: 100%; background: #f8fbfa; border: 1px solid #d8e7e4; border-radius: 10px; color: var(--ink); font-size: 12px; padding: 11px 12px; outline: none; }
        .contact-input:focus { border-color: var(--mint-deep); box-shadow: 0 0 0 3px rgba(168, 221, 213, .25); }
        .contact-submit { display: inline-flex; align-items: center; justify-content: center; width: 100%; min-height: 42px; color: #fff; background: var(--ink); border-radius: 10px; font-size: 12px; font-weight: 700; transition: background .2s ease; }
        .contact-submit:hover { background: var(--mint-deep); }
        .contact-login-note { background: #f1fbf9; border: 1px solid #bde5de; border-radius: 11px; }
        .contact-login-button { color: #fff; background: #43b5a6; border-radius: 999px; padding: 7px 13px; font-weight: 700; white-space: nowrap; }
        .contact-login-button:hover { background: var(--ink); }
        .auth-overlay { position: fixed; inset: 0; z-index: 100; background: rgba(31, 39, 45, .58); opacity: 0; pointer-events: none; transition: opacity .25s ease; }
        .auth-overlay.is-open { opacity: 1; pointer-events: auto; }
        .auth-drawer { position: absolute; top: 0; right: 0; display: flex; flex-direction: column; width: min(100%, 440px); height: 100%; overflow-y: auto; background: #f7fbfa; box-shadow: -12px 0 34px rgba(31, 39, 45, .18); transform: translateX(100%); transition: transform .3s ease; }
        .auth-overlay.is-open .auth-drawer { transform: translateX(0); }
        .auth-drawer-header { display: flex; justify-content: space-between; align-items: flex-start; padding: 30px 26px 18px; }
        .auth-close { color: #91a3a4; font-size: 26px; line-height: 1; transition: color .2s ease; }
        .auth-close:hover { color: var(--blush-deep); }
        .auth-drawer-body { padding: 0 26px 26px; }
        .auth-quick-access { color: #4c6265; background: #effaf8; border: 1px solid #bde5de; border-radius: 8px; font-size: 10px; line-height: 1.45; }
        .auth-quick-access code { color: #397e79; background: #dff3ef; border-radius: 4px; padding: 1px 4px; }
        .auth-field { width: 100%; margin-top: 5px; padding: 10px 12px; color: var(--ink); background: #fff; border: 1px solid #d5e2e0; border-radius: 10px; font-size: 12px; outline: none; }
        .auth-field:focus { border-color: var(--mint-deep); box-shadow: 0 0 0 3px rgba(168, 221, 213, .24); }
        .auth-submit { width: 100%; min-height: 42px; color: #fff; background: #43b5a6; border-radius: 10px; font-family: 'Playfair Display', serif; font-size: 14px; font-weight: 700; transition: background .2s ease; }
        .auth-submit:hover { background: var(--ink); }
        .auth-error { color: #a34862; background: #fff1f4; border: 1px solid #f0c5d0; border-radius: 9px; font-size: 11px; }
        .auth-drawer-footer { margin-top: auto; padding: 20px 26px 24px; border-top: 1px solid #dce9e6; text-align: center; color: #829294; font-size: 11px; }
        .auth-switch { color: var(--mint-deep); font-weight: 700; }
        .auth-switch:hover { color: var(--blush-deep); }
        .auth-footer-note { color: #9aa9a9; font-size: 10px; }
        @media (max-width: 640px) {
            .auth-actions { gap: 2px; padding: 3px; }
            .auth-login, .auth-register { min-height: 34px; padding: 0 10px; font-size: 11px; }
            .map-frame, .map-frame iframe { min-height: 300px; }
            .hero-visual { display: none; }
        }
    </style>
</head>
<body class="antialiased selection:bg-pink-200">

    <header class="site-header backdrop-blur-md border-b sticky top-0 z-50 px-5 sm:px-8 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center gap-6">
        <a href="index.php" class="flex items-center gap-3 group flex-shrink-0">
            <img src="logo.jpg" alt="Logo María Nail Art" class="brand-mark w-12 h-12 rounded-full object-cover">
            <div>
                <span class="text-xl font-serif font-bold text-gray-800 tracking-tight block">MARÍA</span>
                <span class="text-[10px] tracking-widest text-teal-600 font-bold block -mt-1">NAIL ART & SPA</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-8 text-xs font-semibold text-gray-500">
            <a href="#servicios" class="nav-link">Servicios</a>
            <a href="#como-llegar" class="nav-link">Cómo llegar</a>
            <a href="#contacto" class="nav-link">Contacto</a>
        </nav>

        <div class="text-xs flex-shrink-0">
            <?php if ($user): ?>
                <div class="user-actions">
                    <span class="text-gray-600 hidden lg:inline">Hola, <strong class="text-gray-800"><?php echo htmlspecialchars($user['nombre']); ?></strong></span>
                    <a href="mis_citas.php" class="auth-login">Mis citas</a>
                    <?php if (!empty($user['is_admin'])): ?>
                        <a href="admin.php" class="auth-register">Panel Admin</a>
                    <?php endif; ?>
                    <a href="logout.php" class="px-2 py-2 text-pink-700 hover:text-pink-900 font-semibold">Salir</a>
                </div>
            <?php else: ?>
                <div class="auth-actions">
                    <a href="#auth-drawer" data-auth-open="login" class="auth-login">Iniciar sesión</a>
                    <a href="#auth-drawer" data-auth-open="register" class="auth-register">Registrarme <span class="ml-1">&#8599;</span></a>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </header>

    <main>
    <section class="hero px-6 py-14 sm:py-20 overflow-hidden">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-14 items-center">
            <div class="max-w-xl">
                <p class="eyebrow text-[11px] font-bold uppercase mb-5">Belleza que se nota en los detalles</p>
                <h1 class="text-5xl sm:text-6xl font-serif font-bold text-gray-800 leading-none mb-6">Tu estilo,<br><span class="text-pink-500 italic">en cada uña.</span></h1>
                <p class="text-gray-500 text-sm sm:text-base leading-relaxed mb-8 max-w-lg">Un espacio para consentirte, descubrir tu próximo diseño favorito y salir sintiéndote tan especial como te ves.</p>
                <div class="flex gap-3 flex-wrap">
                    <?php if ($user): ?>
                        <a href="agendar.php" class="primary-button px-6 py-3.5 text-white font-bold rounded-full transition-colors">Agendar mi cita <span class="ml-2">&#8599;</span></a>
                    <?php else: ?>
                        <a href="#auth-drawer" data-auth-open="login" class="primary-button px-6 py-3.5 text-white font-bold rounded-full transition-colors">Agendar mi cita <span class="ml-2">&#8599;</span></a>
                    <?php endif; ?>
                    <a href="#servicios" class="secondary-button px-6 py-3.5 bg-white border font-semibold rounded-full transition-colors">Explorar servicios</a>
                    <a href="#como-llegar" class="secondary-button px-6 py-3.5 bg-white border font-semibold rounded-full transition-colors">Cómo llegar <span class="ml-1">&#8595;</span></a>
                </div>
            </div>
            <div class="hero-visual relative max-w-md w-full mx-auto lg:ml-auto">
                <div class="absolute -top-5 -left-5 w-20 h-20 rounded-full border-2 border-pink-300 bg-white shadow-md overflow-hidden">
                    <img src="logo.jpg" alt="Logo María Nail Art" class="w-full h-full object-cover">
                </div>
                <img src="hero-nails.jpg" alt="Diseño de uñas realizado en María Nail Art" class="hero-photo relative w-full aspect-[4/5] object-cover rounded-t-full rounded-b-3xl">
                <div class="absolute -bottom-5 -right-4 sm:right-4 bg-white px-5 py-4 rounded-2xl shadow-xl border border-pink-100">
                    <span class="block text-[10px] uppercase tracking-widest text-teal-600 font-bold">Tu momento</span>
                    <span class="font-serif text-lg text-gray-800">empieza aquí</span>
                </div>
            </div>
        </div>
    </section>

    <section id="servicios" class="max-w-7xl mx-auto px-6 py-20">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-10">
            <div>
                <p class="section-kicker text-[11px] font-bold uppercase mb-3">Elige tu ritual</p>
                <h2 class="text-3xl sm:text-4xl font-serif font-bold text-gray-800">Servicios para sentirte increíble</h2>
            </div>
            <p class="text-gray-500 text-xs max-w-xs leading-relaxed">Diseños hechos con calma, productos de calidad y una atención pensada para ti.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($servicios as $serv): ?>
                <div class="service-card border rounded-3xl overflow-hidden transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="relative h-56 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($serv['image_url']); ?>" alt="<?php echo htmlspecialchars($serv['nombre']); ?>" class="w-full h-full object-cover">
                            <span class="absolute top-3 right-3 px-3 py-1 bg-gray-800/80 backdrop-blur-md text-white text-xs font-bold rounded-full">
                                <?php echo htmlspecialchars($serv['duracion']); ?>
                            </span>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-serif font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($serv['nombre']); ?></h3>
                            <p class="text-gray-500 text-xs mb-4 leading-relaxed"><?php echo htmlspecialchars($serv['descripcion']); ?></p>
                            <p class="service-note text-gray-500 text-[11px] p-3 rounded-2xl border mb-4">
                                <span class="text-teal-600 font-bold">Incluye:</span> <?php echo htmlspecialchars($serv['detalles']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-2 border-t border-pink-50 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Desde</span>
                            <span class="price text-lg font-serif font-bold">$<?php echo number_format($serv['precio'], 0, ',', '.'); ?> COP</span>
                        </div>
                        <a href="<?php echo $user ? 'agendar.php?service_id='.$serv['id'] : '#auth-drawer'; ?>" <?php echo !$user ? 'data-auth-open="login"' : ''; ?> class="px-4 py-2 bg-gray-800 hover:bg-teal-600 text-white text-xs font-bold rounded-full transition-colors">
                            Reservar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="contacto" class="contact-section px-6 py-16 sm:py-20">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="max-w-xl">
                <p class="section-kicker text-[10px] font-bold uppercase mb-3">Contacto directo</p>
                <h2 class="text-3xl sm:text-4xl font-serif font-bold text-gray-800 mb-5">Hablemos sobre tus uñas</h2>
                <p class="text-gray-500 text-xs sm:text-sm leading-relaxed mb-7">¿Tienes una idea específica para tus uñas o un diseño especial? Escríbenos y nuestra asesora principal, Sandra, te contestará para ayudarte a resolver tus dudas y cotizar diseños complejos de nail art.</p>
                <div class="space-y-2 text-xs text-gray-600">
                    <p><span class="text-teal-600 mr-2">&#128172;</span> WhatsApp: <a href="https://wa.me/573234893612" target="_blank" rel="noopener noreferrer" class="font-bold text-teal-700 hover:text-gray-800">+57 3234893612</a></p>
                    <p><span class="text-teal-600 mr-2">&#9993;</span> Correo: <a href="mailto:s.gxmez05@gmail.com" class="font-bold text-teal-700 hover:text-gray-800">s.gxmez05@gmail.com</a></p>
                </div>
            </div>

            <div class="contact-form rounded-3xl p-6 sm:p-7">
                <h3 class="text-xl font-serif font-bold text-gray-800">Escríbenos un mensaje</h3>
                <p class="text-[10px] text-gray-500 mt-1 mb-5">Sandra Gómez te responderá en minutos.</p>
                <?php if (!$user): ?>
                    <div class="contact-login-note flex items-center justify-between gap-3 p-3 mb-5 text-[10px] text-gray-600">
                        <span><span class="text-teal-600 mr-1">&#128274;</span> Para enviar un mensaje debes iniciar sesión con tu cuenta.</span>
                        <a href="#auth-drawer" data-auth-open="login" class="contact-login-button">Iniciar sesión</a>
                    </div>
                <?php endif; ?>
                <form action="<?php echo $user ? 'https://wa.me/573234893612' : '#auth-drawer'; ?>" method="get">
                    <div class="grid sm:grid-cols-2 gap-3 mb-4">
                        <label class="text-[10px] font-bold text-gray-600">Tu nombre<input class="contact-input mt-1" type="text" name="nombre" placeholder="Ej. Sofía Gómez" <?php echo $user ? '' : 'disabled'; ?>></label>
                        <label class="text-[10px] font-bold text-gray-600">Tu celular<input class="contact-input mt-1" type="tel" name="telefono" placeholder="Ej. 3147890123" <?php echo $user ? '' : 'disabled'; ?>></label>
                    </div>
                    <label class="block text-[10px] font-bold text-gray-600 mb-4">Servicio de interés (opcional)<select class="contact-input mt-1" name="servicio" <?php echo $user ? '' : 'disabled'; ?>><option>Selecciona una opción...</option><?php foreach ($servicios as $serv): ?><option><?php echo htmlspecialchars($serv['nombre']); ?></option><?php endforeach; ?></select></label>
                    <label class="block text-[10px] font-bold text-gray-600 mb-4">Tu mensaje<textarea class="contact-input mt-1" name="mensaje" rows="3" placeholder="Escribe tus dudas, colores que te gustan o ideas de decoración..." <?php echo $user ? '' : 'disabled'; ?>></textarea></label>
                    <?php if ($user): ?>
                        <a href="https://wa.me/573234893612" target="_blank" rel="noopener noreferrer" class="contact-submit">&#128172;&nbsp; Continuar por WhatsApp</a>
                    <?php else: ?>
                        <a href="#auth-drawer" data-auth-open="login" class="contact-submit">&#128274;&nbsp; Iniciar sesión para enviar mensaje</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <section id="como-llegar" class="location-section px-6 py-16 sm:py-20">
        <div class="max-w-6xl mx-auto grid lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div>
                <p class="eyebrow text-[10px] font-bold uppercase mb-3">Ubicación y llegada</p>
                <h2 class="text-3xl sm:text-4xl font-serif font-bold text-gray-800 mb-4">¿Cómo llegar a tu cita? <span class="text-pink-500">&#128205;</span></h2>
                <p class="text-gray-500 text-xs sm:text-sm leading-relaxed mb-6 max-w-xl">Estamos en un sector tranquilo y de fácil acceso en Bello, Antioquia, cerca al Hospital Mental.</p>

                <div class="space-y-3">
                    <div class="location-card flex gap-3 items-start p-3">
                        <span class="location-icon">&#8982;</span>
                        <div><p class="text-[10px] uppercase tracking-wide font-bold text-teal-700">Dirección</p><p class="text-xs font-bold text-gray-800">Carrera 57 #38-290, Urbanización Puerto Nuevo</p><p class="text-[10px] text-gray-500">Bello, Antioquia (cerca al Hospital Mental)</p></div>
                    </div>
                    <div class="location-card flex gap-3 items-start p-3">
                        <span class="location-icon">&#8962;</span>
                        <div><p class="text-[10px] uppercase tracking-wide font-bold text-teal-700">Atención en estudio privado</p><p class="text-[10px] text-gray-500">Atención desde mi hogar, exclusivamente con cita previa.</p></div>
                    </div>
                    <div class="location-card flex gap-3 items-start p-3">
                        <span class="location-icon">&#10024;</span>
                        <div><p class="text-[10px] uppercase tracking-wide font-bold text-teal-700">¿Prefieres que vaya hasta ti?</p><p class="text-[10px] text-gray-500">También contamos con servicio a domicilio dentro de la urbanización y sectores cercanos.</p></div>
                    </div>
                    <div class="location-card flex gap-3 items-start p-3">
                        <span class="location-icon">&#9719;</span>
                        <div><p class="text-[10px] uppercase tracking-wide font-bold text-teal-700">Horarios de atención</p><p class="text-[10px] text-gray-500"><strong class="text-gray-700">Lunes a sábado:</strong> 8:00 AM - 7:00 PM<br>Domingos y festivos: Cerrado por descanso</p></div>
                    </div>
                </div>

                <div class="private-note mt-3 p-4 text-[10px] leading-relaxed"><strong class="block text-teal-200 uppercase tracking-wide mb-1">&#128274; Indicaciones privadas de acceso</strong>Una vez confirmes tu cita, te compartiré las indicaciones necesarias para llegar al estudio o coordinaremos contigo el servicio a domicilio.</div>
                <a href="https://www.google.com/maps/search/?api=1&query=Carrera+57+%2338-290%2C+Bello%2C+Antioquia" target="_blank" rel="noopener noreferrer" class="maps-button mt-6 px-5 py-3 text-xs font-bold rounded-full transition-colors">&#9678; Abrir dirección en Google Maps <span>&#8594;</span></a>
            </div>
            <div class="map-frame">
                <iframe src="https://www.google.com/maps?q=Carrera+57+%2338-290%2C+Bello%2C+Antioquia&output=embed" title="Mapa de ubicación de María Nail Art" loading="lazy" allowfullscreen></iframe>
            </div>
        </div>
    </section>

    <div id="auth-drawer" class="auth-overlay<?php echo ($authError || $authRequested) ? ' is-open' : ''; ?>" aria-hidden="<?php echo ($authError || $authRequested) ? 'false' : 'true'; ?>">
        <aside class="auth-drawer" role="dialog" aria-modal="true" aria-labelledby="auth-title">
            <div class="auth-drawer-header">
                <div>
                    <h2 id="auth-title" class="text-2xl font-serif font-bold text-gray-800" data-auth-title>Bienvenida a tu Club</h2>
                    <p class="text-[10px] text-gray-500 mt-1" data-auth-subtitle>Inicia sesión para ver tu historial y agendar más rápido.</p>
                </div>
                <button type="button" class="auth-close" data-auth-close aria-label="Cerrar panel">&times;</button>
            </div>

            <div class="auth-drawer-body">
                <?php if ($authError): ?>
                    <div class="auth-error p-3 mb-4">&#9888;&nbsp; <?php echo htmlspecialchars($authError); ?></div>
                <?php endif; ?>

                <div data-auth-view="login" class="<?php echo $authPanel === 'login' ? '' : 'hidden'; ?>">
                    <form method="POST" action="index.php" class="space-y-4">
                        <input type="hidden" name="auth_action" value="login">
                        <label class="block text-[10px] font-bold text-gray-600">Correo electrónico<input class="auth-field" type="email" name="email" required placeholder="Ej. sofia@gmail.com" value="<?php echo $authPanel === 'login' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>"></label>
                        <label class="block text-[10px] font-bold text-gray-600">Contraseña<input class="auth-field" type="password" name="password" required placeholder="Ingresa tu contraseña"></label>
                        <button type="submit" class="auth-submit">Iniciar Sesión</button>
                    </form>
                    <p class="text-center text-xs text-gray-500 mt-6">¿Aún no tienes cuenta? <a href="#auth-drawer" data-auth-switch="register" class="auth-switch">Regístrate aquí</a></p>
                </div>

                <div data-auth-view="register" class="<?php echo $authPanel === 'register' ? '' : 'hidden'; ?>">
                    <div class="auth-quick-access p-3 mb-5">Crea tu cuenta para reservar tus servicios y consultar tus citas desde cualquier dispositivo.</div>
                    <form method="POST" action="index.php" class="space-y-4">
                        <input type="hidden" name="auth_action" value="register">
                        <label class="block text-[10px] font-bold text-gray-600">Nombre completo<input class="auth-field" type="text" name="nombre" required minlength="3" placeholder="Ej. Sofía Restrepo" value="<?php echo $authPanel === 'register' ? htmlspecialchars($_POST['nombre'] ?? '') : ''; ?>"></label>
                        <label class="block text-[10px] font-bold text-gray-600">Celular / WhatsApp<input class="auth-field" type="tel" name="telefono" required pattern="\d{10}" maxlength="10" placeholder="Ej. 3147890123" value="<?php echo $authPanel === 'register' ? htmlspecialchars($_POST['telefono'] ?? '') : ''; ?>"></label>
                        <label class="block text-[10px] font-bold text-gray-600">Correo electrónico<input class="auth-field" type="email" name="email" required placeholder="tu_correo@gmail.com" value="<?php echo $authPanel === 'register' ? htmlspecialchars($_POST['email'] ?? '') : ''; ?>"></label>
                        <label class="block text-[10px] font-bold text-gray-600">Contraseña<input class="auth-field" type="password" name="password" required minlength="6" placeholder="Mínimo 6 caracteres"></label>
                        <button type="submit" class="auth-submit">Crear mi cuenta</button>
                    </form>
                    <p class="text-center text-xs text-gray-500 mt-6">¿Ya tienes cuenta? <a href="#auth-drawer" data-auth-switch="login" class="auth-switch">Inicia sesión</a></p>
                </div>
            </div>
            <div class="auth-drawer-footer"><p class="auth-footer-note">&#128274; Acceso seguro protegido mediante contraseña en la base de datos MySQL / XAMPP.</p><p class="mt-4">&#169; 2026 María Nail Art</p></div>
        </aside>
    </div>

    <footer class="footer text-gray-300 text-xs py-12 px-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-6 text-center sm:text-left">
            <div>
                <h4 class="text-lg font-serif font-bold footer-accent mb-1">María Nail Art & Spa</h4>
                <p class="text-gray-400">Diseños que cuentan tu historia.</p>
            </div>
            <div class="space-y-1">
                <p>&#8226; Carrera 57 #38-290, Urb. Puerto Nuevo - Bello, Antioquia</p>
                <p>&#8226; WhatsApp: +57 3234893612</p>
                <p>&#8226; Correo: s.gxmez05@gmail.com</p>
            </div>
        </div>
        <div class="mt-8 text-center text-gray-500 text-[11px] pt-6 border-t border-gray-700">
            © 2026 María Nail Art. Todos los derechos reservados.
        </div>
    </footer>
    </main>

    <script>
        (() => {
            const overlay = document.getElementById('auth-drawer');
            const views = overlay.querySelectorAll('[data-auth-view]');
            const title = overlay.querySelector('[data-auth-title]');
            const subtitle = overlay.querySelector('[data-auth-subtitle]');

            const setPanel = (panel) => {
                views.forEach((view) => view.classList.toggle('hidden', view.dataset.authView !== panel));
                title.textContent = panel === 'register' ? 'Crea tu cuenta' : 'Bienvenida a tu Club';
                subtitle.textContent = panel === 'register' ? 'Regístrate para reservar tu próximo momento.' : 'Inicia sesión para ver tu historial y agendar más rápido.';
            };

            const openPanel = (panel) => {
                setPanel(panel || 'login');
                overlay.classList.add('is-open');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            };

            const closePanel = () => {
                overlay.classList.remove('is-open');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            };

            document.querySelectorAll('[data-auth-open]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    openPanel(trigger.dataset.authOpen);
                });
            });
            document.querySelectorAll('[data-auth-switch]').forEach((trigger) => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    openPanel(trigger.dataset.authSwitch);
                });
            });
            overlay.querySelector('[data-auth-close]').addEventListener('click', closePanel);
            overlay.addEventListener('click', (event) => {
                if (event.target === overlay) closePanel();
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && overlay.classList.contains('is-open')) closePanel();
            });
            if (overlay.classList.contains('is-open')) document.body.style.overflow = 'hidden';
        })();
    </script>

</body>
</html>
