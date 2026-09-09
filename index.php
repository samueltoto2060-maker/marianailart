<?php
require_once __DIR__ . '/conexion.php';

$user = $_SESSION['user'] ?? null;

// Obtener servicios de la base de datos MySQL
$stmtServicios = $pdo->query("SELECT * FROM servicios ORDER BY created_at DESC");
$servicios = $stmtServicios->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>María Nail Art & Spa - Sitio Web PHP & MySQL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
    </style>
</head>
<body class="bg-amber-50/30 text-stone-800 antialiased selection:bg-amber-200">

    <!-- HEADER / BARRA DE NAVEGACIÓN -->
    <header class="bg-white/90 backdrop-blur-md border-b border-amber-200/60 sticky top-0 z-50 px-6 py-4 flex justify-between items-center shadow-xs">
        <a href="index.php" class="flex items-center gap-3 group">
            <img src="logo.jpg" alt="Logo María Nail Art" class="w-10 h-10 rounded-full object-cover ring-2 ring-[#70CEBF]/60 shadow-xs">
            <div>
                <span class="text-xl font-serif font-bold text-stone-900 tracking-tight block">MARÍA</span>
                <span class="text-[10px] font-mono tracking-widest text-[#43AFA0] font-bold block -mt-1">NAIL ART & SPA</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-stone-600">
            <a href="#servicios" class="hover:text-amber-800 transition-colors">Servicios</a>
            <a href="#nosotros" class="hover:text-amber-800 transition-colors">Nosotros</a>
            <a href="#contacto" class="hover:text-amber-800 transition-colors">Contacto</a>
        </nav>

        <div class="flex items-center gap-3 text-xs">
            <?php if ($user): ?>
                <span class="text-stone-700 hidden sm:inline">Hola, <strong class="text-stone-900"><?php echo htmlspecialchars($user['nombre']); ?></strong></span>
                <a href="mis_citas.php" class="px-3.5 py-2 bg-amber-100 text-amber-900 font-bold rounded-xl hover:bg-amber-200 transition-colors">
                    Mis Citas
                </a>
                <?php if (!empty($user['is_admin'])): ?>
                    <a href="admin.php" class="px-3.5 py-2 bg-stone-900 text-amber-400 font-bold rounded-xl hover:bg-stone-800 transition-colors">
                        Panel Admin
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="px-3 py-2 text-rose-600 hover:text-rose-800 font-semibold">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php" class="px-4 py-2 border border-amber-300 text-amber-900 font-bold rounded-xl hover:bg-amber-50 transition-colors">
                    Iniciar Sesión
                </a>
                <a href="register.php" class="px-4 py-2 bg-[#43AFA0] hover:bg-[#359B8D] text-white font-bold rounded-xl shadow-sm transition-colors">
                    Registrarme
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- BANNER HERO -->
    <section class="relative bg-gradient-to-b from-amber-100/50 via-amber-50/20 to-white py-16 px-6 text-center overflow-hidden">
        <div class="max-w-3xl mx-auto">
            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-900 text-[11px] font-bold uppercase tracking-widest rounded-full mb-4 border border-amber-200">
                ⭐ Proyecto PHP Nativo & MySQL XAMPP
            </span>
            <h1 class="text-4xl sm:text-5xl font-serif font-bold text-stone-900 tracking-tight leading-tight mb-4">
                Uñas de Alta Costura & Experiencia Spa Premium
            </h1>
            <p class="text-stone-600 text-sm sm:text-base mb-8 max-w-xl mx-auto">
                En Studio Maria Nail Art creo espacios donde puedes consentirte, cuidar tus uñas y llevar diseños que representen tu estilo. Cada servicio es realizado con dedicación, detalle y buenas prácticas de higiene para que disfrutes una experiencia bonita y personalizada.
            </p>
            <div class="flex justify-center gap-4 flex-wrap">
                <a href="<?php echo $user ? 'agendar.php' : 'login.php'; ?>" class="px-6 py-3.5 bg-[#43AFA0] hover:bg-[#359B8D] text-white font-serif font-bold rounded-2xl shadow-lg transition-transform hover:scale-105">
                    ✨ Agendar Cita en Línea
                </a>
                <a href="#servicios" class="px-6 py-3.5 bg-white border border-stone-200 text-stone-700 font-semibold rounded-2xl hover:bg-stone-50 transition-colors">
                    Ver Catálogo
                </a>
            </div>
        </div>
    </section>

    <!-- CATÁLOGO DE SERVICIOS (CUADROS) DESDE MYSQL -->
    <section id="servicios" class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-serif font-bold text-stone-900">Nuestros Servicios Exclusivos</h2>
            <p class="text-stone-500 text-xs mt-2">Cargados dinámicamente desde la base de datos MySQL <code>maria_nail_art.servicios</code></p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($servicios as $serv): ?>
                <div class="bg-white border border-amber-200/80 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="relative h-52 overflow-hidden">
                            <img src="<?php echo htmlspecialchars($serv['image_url']); ?>" alt="<?php echo htmlspecialchars($serv['nombre']); ?>" class="w-full h-full object-cover">
                            <span class="absolute top-3 right-3 px-3 py-1 bg-black/60 backdrop-blur-md text-amber-300 text-xs font-bold rounded-full">
                                <?php echo htmlspecialchars($serv['duracion']); ?>
                            </span>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-serif font-bold text-stone-900 mb-2"><?php echo htmlspecialchars($serv['nombre']); ?></h3>
                            <p class="text-stone-600 text-xs mb-4 leading-relaxed"><?php echo htmlspecialchars($serv['descripcion']); ?></p>
                            <p class="text-stone-500 text-[11px] bg-amber-50/80 p-3 rounded-2xl border border-amber-100 mb-4">
                                💡 <?php echo htmlspecialchars($serv['detalles']); ?>
                            </p>
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-2 border-t border-stone-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-stone-400 block">Precio</span>
                            <span class="text-lg font-serif font-bold text-stone-900">$<?php echo number_format($serv['precio'], 0, ',', '.'); ?> COP</span>
                        </div>
                        <a href="<?php echo $user ? 'agendar.php?service_id='.$serv['id'] : 'login.php'; ?>" class="px-4 py-2 bg-stone-900 hover:bg-[#43AFA0] text-white text-xs font-bold rounded-xl transition-colors">
                            Reservar Turno
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-stone-900 text-stone-400 text-xs py-12 px-6 border-t border-stone-800">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-6 text-center sm:text-left">
            <div>
                <h4 class="text-lg font-serif font-bold text-amber-400 mb-1">María Nail Art & Spa</h4>
                <p class="text-stone-500">Desarrollado en PHP + MySQL para XAMPP / Servidores Locales.</p>
            </div>
            <div class="space-y-1">
                <p>📍 Carrera 57 #38-290, Urb. Puerto Nuevo - Bello, Antioquia</p>
                <p>📱 WhatsApp: +57 3234893612</p>
                <p>✉️ Correo: s.gxmez05@gmail.com</p>
            </div>
        </div>
        <div class="mt-8 text-center text-stone-600 text-[11px] pt-6 border-t border-stone-800">
            © 2026 María Nail Art. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>
