-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-02-2026 a las 02:36:20
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `spdss`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora_accesos`
--

CREATE TABLE `bitacora_accesos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attempted_email` varchar(150) DEFAULT NULL,
  `tipo_evento_id` int(10) UNSIGNED NOT NULL,
  `fail_reason` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(512) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre oficial de la carrera',
  `codigo` varchar(20) DEFAULT NULL COMMENT 'Código interno (ej: 05, SIS)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id`, `nombre`, `codigo`, `created_at`, `updated_at`) VALUES
(1, 'Derecho', 'DER', '2026-02-01 18:05:58', '2026-02-01 18:05:58'),
(2, 'Contaduría Pública', 'CP', '2026-02-01 18:05:58', '2026-02-01 18:05:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `carrera_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `persona_id`, `carrera_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-02-11 04:50:54', '2026-02-11 04:50:54'),
(2, 3, 1, '2026-02-11 05:13:38', '2026-02-11 05:13:38'),
(3, 4, 1, '2026-02-12 18:08:20', '2026-02-12 18:08:20'),
(4, 5, 1, '2026-02-12 18:10:33', '2026-02-12 18:10:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `estudiante_id` bigint(20) UNSIGNED NOT NULL,
  `seccion_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id`, `estudiante_id`, `seccion_id`, `created_at`) VALUES
(1, 1, 1, '2026-02-11 04:50:54'),
(2, 2, 1, '2026-02-11 05:13:38'),
(3, 3, 1, '2026-02-12 18:08:20'),
(4, 4, 1, '2026-02-12 18:10:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Derecho Sucesoral',
  `codigo` varchar(20) DEFAULT NULL COMMENT 'Código interno Unimar',
  `carrera_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id`, `nombre`, `codigo`, `carrera_id`, `created_at`) VALUES
(1, 'DERECHO DE SUCESIONES', 'DES0904321', 1, '2026-02-01 23:29:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos`
--

CREATE TABLE `periodos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL COMMENT 'Ej: 2026-I, 2026-II, Intensivo 2026',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Solo un periodo puede estar activo a la vez',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `periodos`
--

INSERT INTO `periodos` (`id`, `nombre`, `fecha_inicio`, `fecha_fin`, `activo`, `created_at`, `updated_at`) VALUES
(1, '2026-I', '2026-01-19', '2026-04-17', 1, '2026-02-01 18:40:19', '2026-02-01 18:41:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nacionalidad` enum('V','E') NOT NULL DEFAULT 'V' COMMENT 'V=Venezolano, E=Extranjero',
  `cedula` varchar(20) NOT NULL COMMENT 'Número de cédula sin puntos',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F','Otro','Prefiero no decir') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nacionalidad`, `cedula`, `nombres`, `apellidos`, `fecha_nacimiento`, `genero`, `created_at`, `updated_at`) VALUES
(1, 'V', '31120479', 'Cesar', 'Requena', '1991-02-27', 'M', '2026-02-11 04:30:29', '2026-02-11 04:30:29'),
(2, 'V', '27836650', 'Francisco', 'Diaz', '2026-02-11', 'M', '2026-02-11 04:50:54', '2026-02-11 04:51:09'),
(3, 'V', '4224014', 'Valeria', 'Cardier', '2026-02-28', '', '2026-02-11 05:13:38', '2026-02-11 05:13:38'),
(4, 'V', '31120478', 'prueba', 'Cardier', '2001-05-29', '', '2026-02-12 18:08:20', '2026-02-12 18:08:20'),
(5, 'V', '31860250', 'Valeria', 'Cardier', '2001-05-29', '', '2026-02-12 18:10:33', '2026-02-12 18:10:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores`
--

CREATE TABLE `profesores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(20) NOT NULL,
  `firma_digital` varchar(255) DEFAULT NULL COMMENT 'Ruta de la imagen de la firma',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `profesores`
--

INSERT INTO `profesores` (`id`, `persona_id`, `titulo`, `firma_digital`, `created_at`, `updated_at`) VALUES
(1, 1, 'ingeniero', NULL, '2026-02-11 04:34:10', '2026-02-11 04:34:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesores_autorizados`
--

CREATE TABLE `profesores_autorizados` (
  `id` bigint(20) NOT NULL,
  `email` varchar(150) NOT NULL COMMENT 'Debe coincidir con users.email',
  `estatus_registro` enum('pendiente','completado') NOT NULL DEFAULT 'pendiente',
  `condicion` enum('activo','retirado') NOT NULL DEFAULT 'activo',
  `observaciones` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrador del Sistema y Configuración Fiscal', '2026-02-01 18:18:24', '2026-02-01 18:18:24'),
(2, 'profesor', 'Docente evaluador y supervisor de secciones', '2026-02-01 18:18:24', '2026-02-11 04:35:30'),
(3, 'estudiante', 'Estudiante cursante de la materia', '2026-02-01 18:18:24', '2026-02-11 04:35:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `materia_id` int(10) UNSIGNED NOT NULL,
  `profesor_id` bigint(20) UNSIGNED NOT NULL,
  `periodo_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(20) NOT NULL COMMENT 'Ej: 01, Noche-A, SL01',
  `cupo_maximo` int(11) DEFAULT 40,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `materia_id`, `profesor_id`, `periodo_id`, `nombre`, `cupo_maximo`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'sucesiones onl 1', 20, '2026-02-11 04:46:12', '2026-02-11 04:46:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_eventos`
--

CREATE TABLE `tipos_eventos` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(50) NOT NULL COMMENT 'Ej: login_failed, password_reset',
  `descripcion` varchar(255) NOT NULL COMMENT 'Texto legible para reportes',
  `nivel_riesgo` enum('info','warning','critical') NOT NULL DEFAULT 'info'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_eventos`
--

INSERT INTO `tipos_eventos` (`id`, `codigo`, `descripcion`, `nivel_riesgo`) VALUES
(1, 'login_success', 'Inicio de sesión exitoso', 'info'),
(2, 'logout', 'Cierre de sesión', 'info'),
(3, 'login_failed', 'Fallo de autenticación (Credenciales inválidas)', 'warning'),
(4, 'user_blocked', 'Intento de acceso de usuario bloqueado', 'critical'),
(5, 'password_reset_req', 'Solicitud de recuperación de contraseña', 'warning'),
(6, 'password_reset_ok', 'Cambio de contraseña exitoso', 'info'),
(7, 'suspicious_ip', 'Acceso denegado por IP sospechosa', 'critical');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `persona_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `persona_id`, `role_id`, `email`, `password`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'fadr2001@gmail.com', '$2b$12$I9S9nebQUxtwhXxv5U6GJeZP7CZUbHUuxsxp9SP/KPnjb0P4qOxwm', 'active', '2026-02-11 04:44:49', '2026-02-11 04:44:49'),
(2, 2, 3, 'fdiaz.6650@unimar.edu.ve', '$2y$10$ctwj3nxTnx.83Ax9hNT3Ye5SHMEi6ATT5LzUh.G3oIVhsWEiMqobm', 'active', '2026-02-11 04:50:54', '2026-02-11 04:50:54'),
(3, 3, 3, 'cardierv@gmail.com', '$2y$10$ZbEymi1ldw5.s46K0t4cdOOCS5EAVPntryBTZCx2ozNskNLTjpMtq', 'active', '2026-02-11 05:13:38', '2026-02-11 05:13:38'),
(4, 4, 3, 'vcardier.0479@unimar.edu.ve', '$2y$10$KRc/FlxyrUVWliVPC13RK.Vuak8bRsta4f16HeHlXBhovaT5b93r6', 'active', '2026-02-12 18:08:20', '2026-02-12 18:08:20'),
(5, 5, 3, 'valefrancardiaz@gmail.com', '$2y$10$JCFra4N/FFLg3zA66srZeOWbVXPdaz9m8A8ctl4c0aVosYhzS1SYm', 'active', '2026-02-12 18:10:33', '2026-02-12 18:10:33');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`,`created_at`),
  ADD KEY `idx_audit_ip` (`ip_address`,`created_at`),
  ADD KEY `idx_audit_evento` (`tipo_evento_id`,`created_at`);

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nombre` (`nombre`),
  ADD UNIQUE KEY `unique_codigo_carrera` (`codigo`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_persona` (`persona_id`),
  ADD KEY `fk_estudiantes_carreras` (`carrera_id`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inscripcion` (`estudiante_id`,`seccion_id`),
  ADD KEY `fk_inscripcion_seccion` (`seccion_id`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nombre` (`nombre`),
  ADD UNIQUE KEY `unique_codigo_materia` (`codigo`),
  ADD KEY `fk_materias_carreras` (`carrera_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token_hash` (`token_hash`),
  ADD KEY `idx_user_resets` (`user_id`);

--
-- Indices de la tabla `periodos`
--
ALTER TABLE `periodos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nombre` (`nombre`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_documento` (`nacionalidad`,`cedula`);

--
-- Indices de la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_persona_profesor` (`persona_id`);

--
-- Indices de la tabla `profesores_autorizados`
--
ALTER TABLE `profesores_autorizados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `profesores_autorizados_email_unique` (`email`),
  ADD KEY `fk_profesores_autorizados_admin` (`created_by`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nombre` (`nombre`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seccion_periodo` (`materia_id`,`periodo_id`,`nombre`),
  ADD UNIQUE KEY `unique_seccion_periodo_materia` (`periodo_id`,`materia_id`,`nombre`),
  ADD KEY `fk_secciones_profesores` (`profesor_id`);

--
-- Indices de la tabla `tipos_eventos`
--
ALTER TABLE `tipos_eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_codigo` (`codigo`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_persona_user` (`persona_id`),
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `periodos`
--
ALTER TABLE `periodos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `profesores`
--
ALTER TABLE `profesores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `profesores_autorizados`
--
ALTER TABLE `profesores_autorizados`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipos_eventos`
--
ALTER TABLE `tipos_eventos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `bitacora_accesos`
--
ALTER TABLE `bitacora_accesos`
  ADD CONSTRAINT `fk_bitacora_tipos` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipos_eventos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bitacora_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `fk_estudiantes_carreras` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estudiantes_personas` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `fk_inscripcion_estudiante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `fk_materias_carreras` FOREIGN KEY (`carrera_id`) REFERENCES `carreras` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesores`
--
ALTER TABLE `profesores`
  ADD CONSTRAINT `fk_profesores_personas` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `profesores_autorizados`
--
ALTER TABLE `profesores_autorizados`
  ADD CONSTRAINT `fk_profesores_autorizados_admin` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD CONSTRAINT `fk_secciones_materias` FOREIGN KEY (`materia_id`) REFERENCES `materias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_secciones_periodos` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_secciones_profesores` FOREIGN KEY (`profesor_id`) REFERENCES `profesores` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_personas` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
