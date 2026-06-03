-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-06-2026 a las 22:14:14
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
-- Base de datos: `sistema_cocha`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autores`
--

CREATE TABLE `autores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `seudonimo` varchar(100) DEFAULT NULL,
  `tipo_autor` enum('personal','corporativo','institucional') DEFAULT 'personal',
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `nacionalidad` varchar(75) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `grado_academico` varchar(100) DEFAULT NULL,
  `especialidad` varchar(150) DEFAULT NULL,
  `institucion` varchar(150) DEFAULT NULL,
  `orcid` varchar(50) DEFAULT NULL,
  `sitio_web` varchar(200) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `autores`
--

INSERT INTO `autores` (`id`, `nombre`, `apellido_paterno`, `apellido_materno`, `seudonimo`, `tipo_autor`, `correo`, `telefono`, `nacionalidad`, `fecha_nacimiento`, `grado_academico`, `especialidad`, `institucion`, `orcid`, `sitio_web`, `biografia`, `estado`, `creado_el`, `actualizado_el`) VALUES
(1, 'Autor 1', NULL, NULL, NULL, 'personal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(2, 'Autor 2', NULL, NULL, NULL, 'personal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(3, 'Autor 3', NULL, NULL, NULL, 'personal', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `categoria`, `estado`, `creado_el`, `actualizado_el`) VALUES
(1, 'Programación', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(2, 'Base de datos', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(3, 'Inteligencia Artificial', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(4, 'Salud', 'activo', '2026-06-03 20:08:12', '2026-06-03 20:08:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(25) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ruta_logo` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id`, `nombre`, `estado`, `creado_el`, `actualizado_el`, `ruta_logo`) VALUES
(1, 'Editorial 1', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13', NULL),
(2, 'Editorial 2', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13', NULL),
(3, 'Editorial 3', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formatos`
--

CREATE TABLE `formatos` (
  `id` int(11) NOT NULL,
  `formato` varchar(35) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formatos`
--

INSERT INTO `formatos` (`id`, `formato`, `estado`, `creado_el`, `actualizado_el`) VALUES
(1, 'Físico', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(2, 'PDF', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(3, 'Digital', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idiomas`
--

CREATE TABLE `idiomas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(25) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `idiomas`
--

INSERT INTO `idiomas` (`id`, `nombre`, `estado`, `creado_el`, `actualizado_el`) VALUES
(1, 'Español', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(2, 'Inglés', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13'),
(3, 'Portugués', 'activo', '2026-05-27 21:42:13', '2026-05-27 21:42:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `id_categoria` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  `autor_corporativo` varchar(150) DEFAULT NULL,
  `ruta_portada` varchar(250) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `isbn` varchar(25) DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `subtitulo` varchar(50) DEFAULT NULL,
  `id_editorial` int(11) NOT NULL,
  `edicion` int(11) DEFAULT NULL,
  `numero_paginas` int(11) DEFAULT NULL,
  `id_idioma` int(11) NOT NULL,
  `id_formato` int(11) NOT NULL,
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `estado`, `id_categoria`, `id_autor`, `autor_corporativo`, `ruta_portada`, `descripcion`, `isbn`, `fecha_registro`, `subtitulo`, `id_editorial`, `edicion`, `numero_paginas`, `id_idioma`, `id_formato`, `creado_el`, `actualizado_el`) VALUES
(4, 'LIBRO1', 'activo', 2, 1, NULL, 'uploads/portadas/portada_1779977588_7652.jpeg', 'asdadas', '123', '2026-05-09', 'LIBRITO', 2, 1, 100, 1, 3, '2026-05-28 14:13:08', '2026-05-28 14:13:08');

--
-- Disparadores `libros`
--
DELIMITER $$
CREATE TRIGGER `trg_eliminar_relacion_libro_autor` BEFORE DELETE ON `libros` FOR EACH ROW BEGIN
    DELETE FROM libros_autores
    WHERE id_libro = OLD.id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros_autores`
--

CREATE TABLE `libros_autores` (
  `id` int(11) NOT NULL,
  `id_libro` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros_autores`
--

INSERT INTO `libros_autores` (`id`, `id_libro`, `id_autor`) VALUES
(8, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion`
--

CREATE TABLE `recuperacion` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `usado` enum('si','no') DEFAULT 'no'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `permisos` set('crear','mostrar','editar','eliminar','administrar') DEFAULT 'mostrar',
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellido_materno` varchar(35) DEFAULT NULL,
  `apellido_paterno` varchar(35) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(250) NOT NULL,
  `estado` enum('activo','inactivo','bloqueado') DEFAULT 'activo',
  `id_rol` int(11) NOT NULL,
  `verificado` enum('si','no') DEFAULT 'no',
  `correo` varchar(75) NOT NULL,
  `genero` varchar(25) DEFAULT NULL,
  `fecha_nacimiento` timestamp NULL DEFAULT NULL,
  `creado_el` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_el` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `autores`
--
ALTER TABLE `autores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `formatos`
--
ALTER TABLE `formatos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `idiomas`
--
ALTER TABLE `idiomas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_libros_categoria` (`id_categoria`),
  ADD KEY `fk_libros_autor` (`id_autor`),
  ADD KEY `fk_libros_editorial` (`id_editorial`),
  ADD KEY `fk_libros_idioma` (`id_idioma`),
  ADD KEY `fk_libros_formato` (`id_formato`);

--
-- Indices de la tabla `libros_autores`
--
ALTER TABLE `libros_autores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_libros_autores_libro` (`id_libro`),
  ADD KEY `fk_libros_autores_autor` (`id_autor`);

--
-- Indices de la tabla `recuperacion`
--
ALTER TABLE `recuperacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recuperacion_usuarios` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `fk_usuarios_roles` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `autores`
--
ALTER TABLE `autores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `formatos`
--
ALTER TABLE `formatos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `idiomas`
--
ALTER TABLE `idiomas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `libros_autores`
--
ALTER TABLE `libros_autores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `recuperacion`
--
ALTER TABLE `recuperacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `fk_libros_autor` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_editorial` FOREIGN KEY (`id_editorial`) REFERENCES `editoriales` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_formato` FOREIGN KEY (`id_formato`) REFERENCES `formatos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_idioma` FOREIGN KEY (`id_idioma`) REFERENCES `idiomas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `libros_autores`
--
ALTER TABLE `libros_autores`
  ADD CONSTRAINT `fk_libros_autores_autor` FOREIGN KEY (`id_autor`) REFERENCES `autores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_libros_autores_libro` FOREIGN KEY (`id_libro`) REFERENCES `libros` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recuperacion`
--
ALTER TABLE `recuperacion`
  ADD CONSTRAINT `fk_recuperacion_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
