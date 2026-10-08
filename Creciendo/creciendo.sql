-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-10-2026 a las 17:57:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `creciendo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` date NOT NULL,
  `creado_por` varchar(50) DEFAULT 'Dirección',
  `sala_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_diarias`
--

CREATE TABLE `actividades_diarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(50) NOT NULL CHECK (`tipo` in ('COMIDA','SIESTA','PAÑAL','JUEGO','OTRO')),
  `detalle` text NOT NULL,
  `docente_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `estado` varchar(20) NOT NULL CHECK (`estado` in ('PRESENTE','AUSENTE','JUSTIFICADO')),
  `hora_ingreso` time DEFAULT NULL,
  `registrado_por_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias`
--

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `estado` varchar(50) NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `asistencias`
--

INSERT INTO `asistencias` (`id`, `alumno_id`, `fecha`, `estado`, `hora`) VALUES
(1, 1, '2026-10-08', 'Ausente', '07:55:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `avisos_privados`
--

CREATE TABLE `avisos_privados` (
  `id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `sala_id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directores`
--

CREATE TABLE `directores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'director',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `directores`
--

INSERT INTO `directores` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `creado_en`) VALUES
(1, 'Guillermo', 'Gutierrez', 'ggg1122702187@gmail.com', '123456', 'directores', '2026-10-06 22:00:45'),
(2, 'bianca', 'santucho', 'biancamalvinasantucho@gmail.com', '123456', 'directores', '2026-10-08 03:52:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_ninos`
--

CREATE TABLE `fotos_ninos` (
  `id` int(11) NOT NULL,
  `nino_id` int(11) NOT NULL,
  `maestro_id` int(11) NOT NULL,
  `ruta_imagen` varchar(255) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hitos_desarrollo`
--

CREATE TABLE `hitos_desarrollo` (
  `id` int(11) NOT NULL,
  `sala_id` int(11) NOT NULL,
  `eje_tematico` varchar(100) NOT NULL,
  `tema` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `hitos_desarrollo`
--

INSERT INTO `hitos_desarrollo` (`id`, `sala_id`, `eje_tematico`, `tema`, `descripcion`) VALUES
(1, 1, 'Exploración sensorial', 'Texturas y tacto', 'Texturas y tacto'),
(2, 1, 'Exploración sensorial', 'Colores y estímulos visuales', 'Colores y estímulos visuales'),
(3, 1, 'Exploración sensorial', 'Sonidos y estímulos auditivos', 'Sonidos y estímulos auditivos'),
(4, 1, 'Exploración sensorial', 'Olores seguros', 'Olores seguros'),
(5, 1, 'Movimiento y equilibrio', 'Caminar y explorar', 'Caminar y explorar'),
(6, 1, 'Movimiento y equilibrio', 'Gatear y desplazarse', 'Gatear y desplazarse'),
(7, 1, 'Movimiento y equilibrio', 'Balanceo y estabilidad', 'Balanceo y estabilidad'),
(8, 1, 'Movimiento y equilibrio', 'Empujar y tirar', 'Empujar y tirar'),
(9, 1, 'Interacción afectiva', 'Vínculo con cuidadores', 'Vínculo con cuidadores'),
(10, 1, 'Interacción afectiva', 'Imitación y reciprocidad', 'Imitación y reciprocidad'),
(11, 1, 'Interacción afectiva', 'Juego compartido', 'Juego compartido'),
(12, 1, 'Interacción afectiva', 'Expresión emocional básica', 'Expresión emocional básica'),
(13, 1, 'Manipulación de objetos', 'Agarre y suelta', 'Agarre y suelta'),
(14, 1, 'Manipulación de objetos', 'Traslado de objetos', 'Traslado de objetos'),
(15, 1, 'Manipulación de objetos', 'Apilado y construcción', 'Apilado y construcción'),
(16, 1, 'Manipulación de objetos', 'Inserción de objetos', 'Inserción de objetos'),
(17, 1, 'Sonidos y ritmos', 'Exploración de sonidos simples', 'Exploración de sonidos simples'),
(18, 1, 'Sonidos y ritmos', 'Producción de sonidos con objetos', 'Producción de sonidos con objetos'),
(19, 1, 'Sonidos y ritmos', 'Movimiento al ritmo', 'Movimiento al ritmo'),
(20, 1, 'Sonidos y ritmos', 'Imitación de sonidos', 'Imitación de sonidos'),
(21, 2, 'Motricidad y coordinación', 'Movimientos corporales amplios', 'Movimientos corporales amplios'),
(22, 2, 'Motricidad y coordinación', 'Coordinación mano-ojo', 'Coordinación mano-ojo'),
(23, 2, 'Motricidad y coordinación', 'Equilibrio y estabilidad', 'Equilibrio y estabilidad'),
(24, 2, 'Motricidad y coordinación', 'Manipulación fina de objetos', 'Manipulación fina de objetos'),
(25, 2, 'Motricidad y coordinación', 'Movimientos rítmicos coordinados', 'Movimientos rítmicos coordinados'),
(26, 2, 'Lenguaje y comunicación', 'Expresión vocal básica', 'Expresión vocal básica'),
(27, 2, 'Lenguaje y comunicación', 'Comprensión de instrucciones simples', 'Comprensión de instrucciones simples'),
(28, 2, 'Lenguaje y comunicación', 'Imitación de gestos y sonidos', 'Imitación de gestos y sonidos'),
(29, 2, 'Lenguaje y comunicación', 'Interacción comunicativa con pares', 'Interacción comunicativa con pares'),
(30, 2, 'Lenguaje y comunicación', 'Exploración de objetos comunicativos', 'Exploración de objetos comunicativos'),
(31, 2, 'Exploración del entorno', 'Observación de objetos cotidianos', 'Observación de objetos cotidianos'),
(32, 2, 'Exploración del entorno', 'Exploración táctil de texturas', 'Exploración táctil de texturas'),
(33, 2, 'Exploración del entorno', 'Interacción con elementos naturales', 'Interacción con elementos naturales'),
(34, 2, 'Imitación y juego simbólico', 'Imitación de acciones cotidianas', 'Imitación de acciones cotidianas'),
(35, 2, 'Imitación y juego simbólico', 'Juego con objetos representativos', 'Juego con objetos representativos'),
(36, 2, 'Imitación y juego simbólico', 'Imitación de roles sociales', 'Imitación de roles sociales'),
(37, 2, 'Imitación y juego simbólico', 'Juego con sonidos simbólicos', 'Juego con sonidos simbólicos'),
(38, 2, 'Imitación y juego simbólico', 'Interacción simbólica en grupo', 'Interacción simbólica en grupo'),
(39, 2, 'Conceptos básicos', 'Colores primarios', 'Colores primarios'),
(40, 2, 'Conceptos básicos', 'Formas básicas', 'Formas básicas'),
(41, 2, 'Conceptos básicos', 'Tamaños (grande/pequeño)', 'Tamaños (grande/pequeño)'),
(42, 2, 'Conceptos básicos', 'Posiciones espaciales (arriba/abajo, dentro/fuera)', 'Posiciones espaciales (arriba/abajo, dentro/fuera)'),
(43, 2, 'Conceptos básicos', 'Cantidades básicas (uno/mucho)', 'Cantidades básicas (uno/mucho)'),
(44, 3, 'Creatividad y expresión', 'Exploración con materiales artísticos', 'Exploración con materiales artísticos'),
(45, 3, 'Creatividad y expresión', 'Movimiento expresivo', 'Movimiento expresivo'),
(46, 3, 'Creatividad y expresión', 'Creación con objetos cotidianos', 'Creación con objetos cotidianos'),
(47, 3, 'Creatividad y expresión', 'Expresión sonora libre', 'Expresión sonora libre'),
(48, 3, 'Creatividad y expresión', 'Juego simbólico creativo', 'Juego simbólico creativo'),
(49, 3, 'Resolución de problemas', 'Manipulación para alcanzar objetivos', 'Manipulación para alcanzar objetivos'),
(50, 3, 'Resolución de problemas', 'Exploración de causa y efecto', 'Exploración de causa y efecto'),
(51, 3, 'Resolución de problemas', 'Superación de obstáculos físicos', 'Superación de obstáculos físicos'),
(52, 3, 'Resolución de problemas', 'Clasificación y organización simples', 'Clasificación y organización simples'),
(53, 3, 'Resolución de problemas', 'Resolución de problemas en juegos simbólicos', 'Resolución de problemas en juegos simbólicos'),
(54, 3, 'Interacción social y valores', 'Cooperación en grupo', 'Cooperación en grupo'),
(55, 3, 'Interacción social y valores', 'Respeto por los turnos', 'Respeto por los turnos'),
(56, 3, 'Interacción social y valores', 'Empatía y cuidado mutuo', 'Empatía y cuidado mutuo'),
(57, 3, 'Interacción social y valores', 'Reconocimiento de normas básicas', 'Reconocimiento de normas básicas'),
(58, 3, 'Narración y cuentos', 'Escucha activa de cuentos', 'Escucha activa de cuentos'),
(59, 3, 'Narración y cuentos', 'Participación en narraciones', 'Participación en narraciones'),
(60, 3, 'Narración y cuentos', 'Creación de historias simples', 'Creación de historias simples'),
(61, 3, 'Narración y cuentos', 'Exploración de imágenes narrativas', 'Exploración de imágenes narrativas'),
(62, 3, 'Secuencias y rutinas', 'Secuencias de acciones simples', 'Secuencias de acciones simples'),
(63, 3, 'Secuencias y rutinas', 'Rutinas diarias lúdicas', 'Rutinas diarias lúdicas'),
(64, 3, 'Secuencias y rutinas', 'Secuencias temporales básicas', 'Secuencias temporales básicas'),
(65, 3, 'Secuencias y rutinas', 'Patrones rítmicos y repetitivos', 'Patrones rítmicos y repetitivos'),
(66, 3, 'Secuencias y rutinas', 'Secuencias en juegos grupales', 'Secuencias en juegos grupales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logros_ninos`
--

CREATE TABLE `logros_ninos` (
  `id` int(11) NOT NULL,
  `nino_id` int(11) NOT NULL,
  `hito_id` int(11) NOT NULL,
  `fecha_logrado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maestros`
--

CREATE TABLE `maestros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'maestro',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `sala_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `maestros`
--

INSERT INTO `maestros` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `creado_en`, `sala_id`) VALUES
(1, 'Guillermo', 'Gutierrez', 'ggg1122702187@gmail.com', '123456', 'maestro', '2026-10-06 22:04:20', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ninos`
--

CREATE TABLE `ninos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sala` varchar(50) NOT NULL,
  `alergias_observaciones` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `sala_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nino_tutor`
--

CREATE TABLE `nino_tutor` (
  `id` int(11) NOT NULL,
  `nino_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observaciones_desarrollo`
--

CREATE TABLE `observaciones_desarrollo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `area_desarrollo` varchar(50) DEFAULT NULL CHECK (`area_desarrollo` in ('MOTRICIDAD','LENGUAJE','SOCIOAFECTIVO','COGNITIVO')),
  `observacion` text NOT NULL,
  `docente_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retiros_qr`
--

CREATE TABLE `retiros_qr` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_qr` varchar(255) NOT NULL,
  `escaneado_por_id` int(11) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'EXITOSO' CHECK (`estado` in ('EXITOSO','RECHAZADO'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salas`
--

CREATE TABLE `salas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `salas`
--

INSERT INTO `salas` (`id`, `nombre`) VALUES
(1, 'Sala 1'),
(2, 'Sala 2'),
(3, 'Sala 3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutores`
--

CREATE TABLE `tutores` (
  `id` int(11) NOT NULL,
  `nino_id` int(11) DEFAULT NULL,
  `parentesco` varchar(50) DEFAULT NULL,
  `es_autorizado_retiro` tinyint(1) DEFAULT 0,
  `foto_identificacion_url` varchar(255) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'tutor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `tutores`
--

INSERT INTO `tutores` (`id`, `nino_id`, `parentesco`, `es_autorizado_retiro`, `foto_identificacion_url`, `nombre`, `apellido`, `email`, `password`, `rol`) VALUES
(1, NULL, NULL, 0, NULL, 'bianca', 'santucho', 'biancamalvinasantucho@gmail.com', '123456', 'tutor');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `actividades_diarias`
--
ALTER TABLE `actividades_diarias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nino_id` (`nino_id`,`fecha`);

--
-- Indices de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `avisos_privados`
--
ALTER TABLE `avisos_privados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tutor_id` (`tutor_id`),
  ADD KEY `sala_id` (`sala_id`);

--
-- Indices de la tabla `directores`
--
ALTER TABLE `directores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `fotos_ninos`
--
ALTER TABLE `fotos_ninos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `hitos_desarrollo`
--
ALTER TABLE `hitos_desarrollo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `logros_ninos`
--
ALTER TABLE `logros_ninos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `logro_unico` (`nino_id`,`hito_id`);

--
-- Indices de la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `sala_id` (`sala_id`);

--
-- Indices de la tabla `ninos`
--
ALTER TABLE `ninos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sala_id` (`sala_id`);

--
-- Indices de la tabla `nino_tutor`
--
ALTER TABLE `nino_tutor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `observaciones_desarrollo`
--
ALTER TABLE `observaciones_desarrollo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `retiros_qr`
--
ALTER TABLE `retiros_qr`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `salas`
--
ALTER TABLE `salas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tutores`
--
ALTER TABLE `tutores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `actividades_diarias`
--
ALTER TABLE `actividades_diarias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias`
--
ALTER TABLE `asistencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `avisos_privados`
--
ALTER TABLE `avisos_privados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `directores`
--
ALTER TABLE `directores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `fotos_ninos`
--
ALTER TABLE `fotos_ninos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `hitos_desarrollo`
--
ALTER TABLE `hitos_desarrollo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `logros_ninos`
--
ALTER TABLE `logros_ninos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `maestros`
--
ALTER TABLE `maestros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ninos`
--
ALTER TABLE `ninos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nino_tutor`
--
ALTER TABLE `nino_tutor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `observaciones_desarrollo`
--
ALTER TABLE `observaciones_desarrollo`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `retiros_qr`
--
ALTER TABLE `retiros_qr`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `salas`
--
ALTER TABLE `salas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tutores`
--
ALTER TABLE `tutores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `avisos_privados`
--
ALTER TABLE `avisos_privados`
  ADD CONSTRAINT `avisos_privados_ibfk_1` FOREIGN KEY (`tutor_id`) REFERENCES `tutores` (`id`),
  ADD CONSTRAINT `avisos_privados_ibfk_2` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`);

--
-- Filtros para la tabla `maestros`
--
ALTER TABLE `maestros`
  ADD CONSTRAINT `maestros_ibfk_1` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`);

--
-- Filtros para la tabla `ninos`
--
ALTER TABLE `ninos`
  ADD CONSTRAINT `ninos_ibfk_1` FOREIGN KEY (`sala_id`) REFERENCES `salas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
