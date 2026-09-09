-- ============================================================
-- BASE DE DATOS MYSQL PARA MARÍA NAIL ART & SPA (PROYECTO PHP)
-- ============================================================


-- 1. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL DEFAULT '123456',
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `registered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` (`id`, `nombre`, `telefono`, `email`, `password`, `is_admin`, `registered_at`) VALUES
('admin-1', 'Sandra Gómez (Admin)', '3234893612', 'admin@marianailart.com', 'admin123', 1, '2026-01-01 08:00:00'),
('user-1', 'Sofía Restrepo', '3147890123', 'sofia@gmail.com', 'sofia123', 0, '2026-07-10 14:30:00'),
('user-2', 'Valentina Gómez', '3205554321', 'valentina@gmail.com', 'valentina123', 0, '2026-07-12 09:15:00')
ON DUPLICATE KEY UPDATE `nombre`=`nombre`;

-- 2. Tabla de Servicios
CREATE TABLE IF NOT EXISTS `servicios` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `nombre` VARCHAR(120) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `detalles` TEXT NOT NULL,
  `duracion` VARCHAR(30) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `categoria` ENUM('manicure', 'pedicure', 'acrylic', 'nailart', 'spa') NOT NULL DEFAULT 'manicure',
  `image_url` VARCHAR(500) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `detalles`, `duracion`, `precio`, `categoria`, `image_url`) VALUES
('1', 'Manicura Semi-permanente', 'Esmaltado de larga duración con curado en lámpara LED, ideal para mantener tus uñas perfectas por más de 15 días.', 'Incluye limado de uñas, cuidado detallado de cutículas, exfoliación suave, hidratación y esmaltado semi-permanente.', '60 min', 45000.00, 'manicure', 'https://images.unsplash.com/photo-1604654894610-df63bc536371?q=80&w=600&auto=format&fit=crop'),
('2', 'Uñas Acrílicas Esculpidas', 'Extensiones personalizadas y esculpidas a mano para lograr el largo y la forma perfecta de tus sueños.', 'Creadas desde cero utilizando polvo acrílico premium. Incluye preparación completa y esmaltado a elección.', '120 min', 85000.00, 'acrylic', 'https://images.unsplash.com/photo-1632345031435-8797b2d58045?q=80&w=600&auto=format&fit=crop'),
('3', 'Nail Art Especializado', 'Diseños artísticos únicos hechos a mano alzada para expresar tu estilo personal y creativo.', 'Efectos dorados, encapsulados, flores, líneas minimalistas o efectos franceses modernos.', '90 min', 60000.00, 'nailart', 'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?q=80&w=600&auto=format&fit=crop'),
('4', 'Pedicura Jelly Spa Relax', 'Una experiencia sensorial de spa que transforma el agua en una gelatina tibia y nutritiva.', 'Inmersión de pies en gelatina de spa aromática, limado de uñas, exfoliación profunda y masaje.', '75 min', 55000.00, 'pedicure', 'https://images.unsplash.com/photo-1519415387722-a1c3bbffdef8?q=80&w=600&auto=format&fit=crop'),
('5', 'Baño de Acrílico (Kapping)', 'Una fina capa de acrílico protectora sobre tu uña natural para evitar quiebres y fomentar el crecimiento sano.', 'Ideal para uñas débiles o escamadas. No extiende el largo, sino que da resistencia de acero.', '75 min', 50000.00, 'acrylic', 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?q=80&w=600&auto=format&fit=crop')
ON DUPLICATE KEY UPDATE `nombre`=`nombre`;

-- 3. Tabla de Citas
CREATE TABLE IF NOT EXISTS `citas` (
  `id` VARCHAR(50) NOT NULL PRIMARY KEY,
  `user_id` VARCHAR(50) DEFAULT NULL,
  `user_name` VARCHAR(100) NOT NULL,
  `user_phone` VARCHAR(20) NOT NULL,
  `service_id` VARCHAR(50) NOT NULL,
  `service_name` VARCHAR(120) NOT NULL,
  `date` DATE NOT NULL,
  `time_slot` VARCHAR(10) NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
  `price` DECIMAL(10,2) NOT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`service_id`) REFERENCES `servicios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `citas` (`id`, `user_id`, `user_name`, `user_phone`, `service_id`, `service_name`, `date`, `time_slot`, `status`, `price`, `notes`, `created_at`) VALUES
('app-1', 'user-1', 'Sofía Restrepo', '3147890123', '1', 'Manicura Semi-permanente', '2026-08-15', '09:30', 'confirmed', 45000.00, 'Tonos pasteles con decoración minimalista.', '2026-08-10 10:00:00'),
('app-2', 'user-2', 'Valentina Gómez', '3205554321', '2', 'Uñas Acrílicas Esculpidas', '2026-08-16', '14:00', 'pending', 85000.00, 'Efecto dorado cromado para grado.', '2026-08-11 11:20:00')
ON DUPLICATE KEY UPDATE `status`=`status`;

-- 4. Tabla de Bloqueo de Horarios
CREATE TABLE IF NOT EXISTS `fechas_bloqueadas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha` DATE NOT NULL UNIQUE,
  `turnos_bloqueados` TEXT DEFAULT NULL,
  `is_fully_blocked` TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
