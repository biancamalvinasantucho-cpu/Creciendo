-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-09-2026 a las 02:19:42
-- Versión del servidor: 10.4.25-MariaDB
-- Versión de PHP: 8.1.10

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
-- Estructura de tabla para la tabla `actividades_diarias`
--

CREATE TABLE `actividades_diarias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(50) COLLATE utf8_spanish_ci NOT NULL CHECK (`tipo` in ('COMIDA','SIESTA','PAÑAL','JUEGO','OTRO')),
  `detalle` text COLLATE utf8_spanish_ci NOT NULL,
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
  `estado` varchar(20) COLLATE utf8_spanish_ci NOT NULL CHECK (`estado` in ('PRESENTE','AUSENTE','JUSTIFICADO')),
  `hora_ingreso` time DEFAULT NULL,
  `registrado_por_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auxiliares`
--

CREATE TABLE `auxiliares` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `rol` varchar(50) COLLATE utf8_spanish_ci DEFAULT 'auxiliar',
  `telefono` varchar(30) COLLATE utf8_spanish_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `auxiliares`
--

INSERT INTO `auxiliares` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `telefono`, `creado_en`) VALUES
(1, 'Guillermo', 'Gutierrez', 'ggg123@gmail.com', '123456', 'auxiliar', NULL, '2026-09-29 00:15:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ninos`
--

CREATE TABLE `ninos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sala` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `alergias_observaciones` text COLLATE utf8_spanish_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observaciones_desarrollo`
--

CREATE TABLE `observaciones_desarrollo` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nino_id` int(11) NOT NULL,
  `fecha` date NOT NULL DEFAULT curdate(),
  `area_desarrollo` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL CHECK (`area_desarrollo` in ('MOTRICIDAD','LENGUAJE','SOCIOAFECTIVO','COGNITIVO')),
  `observacion` text COLLATE utf8_spanish_ci NOT NULL,
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
  `token_qr` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `escaneado_por_id` int(11) DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8_spanish_ci DEFAULT 'EXITOSO' CHECK (`estado` in ('EXITOSO','RECHAZADO'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutores_ninos`
--

CREATE TABLE `tutores_ninos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `nino_id` int(11) NOT NULL,
  `parentesco` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `es_autorizado_retiro` tinyint(1) DEFAULT 1,
  `foto_identificacion_url` varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `rol` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8_spanish_ci DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol`, `telefono`, `creado_en`) VALUES
(2, 'Guillermo', 'Gutierrez', 'ggg1122702187@gmail.com', '123456', 'auxiliar', NULL, '2026-09-28 23:41:01'),
(3, 'Guillermo', 'Gutierrez', 'gustidjparavos@gmail.com', '123456', 'maestro', NULL, '2026-09-29 00:01:43');

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `auxiliares`
--
ALTER TABLE `auxiliares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `ninos`
--
ALTER TABLE `ninos`
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
-- Indices de la tabla `tutores_ninos`
--
ALTER TABLE `tutores_ninos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tutor_id` (`tutor_id`,`nino_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

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
-- AUTO_INCREMENT de la tabla `auxiliares`
--
ALTER TABLE `auxiliares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ninos`
--
ALTER TABLE `ninos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `tutores_ninos`
--
ALTER TABLE `tutores_ninos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
