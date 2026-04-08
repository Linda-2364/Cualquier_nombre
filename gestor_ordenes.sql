-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-04-2026 a las 00:58:37
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
-- Base de datos: `gestor_ordenes`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion`
--

CREATE TABLE `asignacion` (
  `idAsignacion` int(11) NOT NULL,
  `idOrden` int(11) NOT NULL,
  `idTecnico` int(11) NOT NULL,
  `rol` varchar(80) DEFAULT 'Principal',
  `fechaAsignada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignacion`
--

INSERT INTO `asignacion` (`idAsignacion`, `idOrden`, `idTecnico`, `rol`, `fechaAsignada`) VALUES
(1, 1, 1, 'Principal', '2026-04-01 09:00:00'),
(2, 3, 2, 'Principal', '2026-04-03 15:00:00'),
(3, 4, 3, 'Principal', '2026-04-03 17:00:00'),
(4, 4, 1, 'Soporte', '2026-04-03 17:00:00'),
(5, 6, 1, 'Principal', '2026-03-20 09:00:00'),
(6, 8, 2, 'Principal', '2026-04-05 10:00:00'),
(7, 9, 3, 'Principal', '2026-03-10 09:00:00'),
(8, 11, 5, 'Principal', '2026-04-06 21:34:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `idAuditoria` int(11) NOT NULL,
  `idUsuario` int(11) DEFAULT NULL,
  `accion` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`idAuditoria`, `idUsuario`, `accion`, `descripcion`, `fechaRegistro`) VALUES
(1, 1, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:23:33'),
(2, 1, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:26:02'),
(3, 7, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:27:43'),
(4, 7, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:29:53'),
(5, 1, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:31:51'),
(6, 1, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:35:07'),
(7, 3, 'LOGIN', 'Inicio de sesion', '2026-04-06 21:36:08'),
(8, 1, 'LOGIN', 'Inicio de sesion', '2026-04-07 08:57:14'),
(9, 1, 'LOGIN', 'Inicio de sesion', '2026-04-07 08:58:07'),
(10, 2, 'LOGIN', 'Inicio de sesion', '2026-04-07 09:07:04'),
(11, 7, 'LOGIN', 'Inicio de sesion', '2026-04-07 09:08:13'),
(12, 1, 'LOGIN', 'Inicio de sesion', '2026-04-07 09:09:36'),
(13, 1, 'LOGIN', 'Inicio de sesion', '2026-04-07 09:10:10'),
(14, 1, 'LOGIN', 'Inicio de sesion', '2026-04-07 18:57:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoriaequipo`
--

CREATE TABLE `categoriaequipo` (
  `idCategoriaEquipo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoriaequipo`
--

INSERT INTO `categoriaequipo` (`idCategoriaEquipo`, `nombre`, `descripcion`) VALUES
(1, 'Maquinaria Pesada', NULL),
(2, 'Sistemas Eléctricos', NULL),
(3, 'HVAC', NULL),
(4, 'Sistemas Hidráulicos', NULL),
(5, 'Equipos de Cómputo', NULL),
(6, 'Vehículos', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoriarepuesto`
--

CREATE TABLE `categoriarepuesto` (
  `idCategoriaRepuesto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoriarepuesto`
--

INSERT INTO `categoriarepuesto` (`idCategoriaRepuesto`, `nombre`, `descripcion`) VALUES
(1, 'Lubricantes y Aceites', NULL),
(2, 'Filtros', NULL),
(3, 'Correas y Bandas', NULL),
(4, 'Rodamientos', NULL),
(5, 'Materiales Eléctricos', NULL),
(6, 'Herramientas', NULL),
(7, 'Repuestos Hidráulicos', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo`
--

CREATE TABLE `equipo` (
  `idEquipo` int(11) NOT NULL,
  `idCategoriaEquipo` int(11) NOT NULL,
  `idUbicacion` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numeroSerie` varchar(100) DEFAULT NULL,
  `fechaAdquisicion` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo`
--

INSERT INTO `equipo` (`idEquipo`, `idCategoriaEquipo`, `idUbicacion`, `nombre`, `marca`, `modelo`, `numeroSerie`, `fechaAdquisicion`, `activo`) VALUES
(1, 1, 1, 'Prensa Hidráulica #1', 'Bosch', 'PH-500', 'SN-001', '2020-03-15', 1),
(2, 1, 1, 'Torno CNC #2', 'Mazak', 'QT-200', 'SN-002', '2019-07-20', 1),
(3, 2, 2, 'Tablero Eléctrico A', 'Siemens', 'S7-1200', 'SN-003', '2021-01-10', 1),
(4, 3, 3, 'Aire Acondicionado Sala', 'Carrier', 'XPower', 'SN-004', '2022-05-05', 1),
(5, 4, 1, 'Bomba Hidráulica Central', 'Parker', 'PGP-350', 'SN-005', '2018-11-30', 1),
(6, 5, 5, 'Servidor Principal', 'Dell', 'PowerEdge', 'SN-006', '2023-02-14', 1),
(7, 6, 6, 'Camión de Carga #1', 'Mercedes', 'Actros', 'SN-007', '2021-09-01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `idEstado` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`idEstado`, `nombre`, `descripcion`) VALUES
(1, 'PENDIENTE', 'Orden creada, esperando asignación'),
(2, 'EN_PROCESO', 'Técnico trabajando en la orden'),
(3, 'EN_ESPERA', 'Esperando repuestos o autorización'),
(4, 'CERRADA', 'Orden completada'),
(5, 'CANCELADA', 'Orden cancelada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenrepuesto`
--

CREATE TABLE `ordenrepuesto` (
  `idOrdenRepuesto` int(11) NOT NULL,
  `idOrden` int(11) NOT NULL,
  `idRepuesto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precioUnitario` decimal(10,2) DEFAULT 0.00,
  `observacion` text DEFAULT NULL,
  `fechaUso` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordenrepuesto`
--

INSERT INTO `ordenrepuesto` (`idOrdenRepuesto`, `idOrden`, `idRepuesto`, `cantidad`, `precioUnitario`, `observacion`, `fechaUso`) VALUES
(1, 6, 7, 2, 18.00, NULL, '2026-03-22 10:00:00'),
(2, 6, 1, 3, 45.50, NULL, '2026-03-22 11:00:00'),
(3, 9, 3, 1, 28.00, NULL, '2026-03-12 10:00:00'),
(4, 9, 4, 2, 15.75, NULL, '2026-03-12 11:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordentrabajo`
--

CREATE TABLE `ordentrabajo` (
  `idOrden` int(11) NOT NULL,
  `idEquipo` int(11) NOT NULL,
  `idEstado` int(11) NOT NULL,
  `idPrioridad` int(11) NOT NULL,
  `idSolicitante` int(11) DEFAULT NULL,
  `tipoMantenimiento` enum('PREVENTIVO','CORRECTIVO','PREDICTIVO') DEFAULT 'CORRECTIVO',
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fechaCreacion` datetime DEFAULT current_timestamp(),
  `fechaProgramada` datetime DEFAULT NULL,
  `fechaCierre` datetime DEFAULT NULL,
  `horasEstimadas` decimal(6,2) DEFAULT NULL,
  `horasReales` decimal(6,2) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ordentrabajo`
--

INSERT INTO `ordentrabajo` (`idOrden`, `idEquipo`, `idEstado`, `idPrioridad`, `idSolicitante`, `tipoMantenimiento`, `titulo`, `descripcion`, `fechaCreacion`, `fechaProgramada`, `fechaCierre`, `horasEstimadas`, `horasReales`, `observaciones`) VALUES
(1, 1, 2, 4, 7, 'CORRECTIVO', 'Falla en cilindro hidráulico prensa #1', 'El cilindro presenta fuga de aceite en el sello superior', '2026-04-01 08:00:00', '2026-04-06 09:00:00', NULL, 4.00, NULL, NULL),
(2, 2, 1, 2, 7, 'PREVENTIVO', 'Mantenimiento preventivo torno CNC', 'Lubricación y ajuste de componentes según plan mensual', '2026-04-02 10:00:00', '2026-04-08 08:00:00', NULL, 3.00, NULL, NULL),
(3, 3, 2, 3, 1, 'CORRECTIVO', 'Cortocircuito en tablero eléctrico A', 'El tablero presenta disparo repetitivo del interruptor', '2026-04-03 14:00:00', '2026-04-05 10:00:00', NULL, 2.00, NULL, NULL),
(4, 5, 3, 4, 7, 'CORRECTIVO', 'Bomba hidráulica pierde presión', 'La bomba no alcanza la presión nominal de 250 bar', '2026-04-03 16:00:00', '2026-04-05 14:00:00', NULL, 6.00, NULL, NULL),
(5, 4, 1, 1, 7, 'PREVENTIVO', 'Limpieza de filtros HVAC', 'Limpieza trimestral de filtros de aire acondicionado', '2026-04-04 09:00:00', '2026-04-10 08:00:00', NULL, 2.00, NULL, NULL),
(6, 1, 4, 5, 1, 'CORRECTIVO', 'Cambio de sello en cilindro auxiliar', 'Sello deteriorado causa fuga de aceite', '2026-03-20 08:00:00', '2026-03-22 09:00:00', '2026-03-22 16:00:00', 3.00, 3.50, 'Sello cambiado, prueba de presion exitosa'),
(7, 7, 1, 2, 7, 'PREVENTIVO', 'Cambio de aceite camión carga #1', 'Mantenimiento 10,000 km - cambio de aceite y filtros', '2026-04-04 11:00:00', '2026-04-09 08:00:00', NULL, 2.00, NULL, NULL),
(8, 6, 2, 3, 1, 'CORRECTIVO', 'Servidor con alta temperatura CPU', 'Alertas de temperatura en CPU del servidor principal', '2026-04-05 09:00:00', '2026-04-05 11:00:00', NULL, 1.00, NULL, NULL),
(9, 2, 4, 2, 7, 'PREVENTIVO', 'Calibración anual torno CNC #2', 'Calibración y ajuste de precisión según plan anual', '2026-03-10 08:00:00', '2026-03-12 09:00:00', '2026-03-12 15:00:00', 5.00, 5.00, 'Calibracion completada, precision dentro de tolerancia'),
(10, 3, 5, 3, 7, 'CORRECTIVO', 'Revisión cableado tablero B', 'Inspección tras corte de energía no programado', '2026-03-25 15:00:00', '2026-03-26 09:00:00', '2026-03-26 12:00:00', 2.00, 2.50, 'Se identificaron cables en mal estado, se reemplazaron'),
(11, 6, 2, 5, 7, 'PREVENTIVO', 'falla en pampeño', 'comer pampeño ya', '2026-04-06 21:28:31', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `persona`
--

CREATE TABLE `persona` (
  `idPersona` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `ci` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `persona`
--

INSERT INTO `persona` (`idPersona`, `nombre`, `apellido`, `ci`, `email`, `telefono`, `direccion`) VALUES
(1, 'Carlos', 'Administrador', '1234567', 'admin@manttech.com', '77700001', NULL),
(2, 'Roberto', 'Supervisor', '2345678', 'supervisor@manttech.com', '77700002', NULL),
(3, 'Carlos', 'Mendoza', '3456789', 'carlos.m@manttech.com', '77700003', NULL),
(4, 'Luis', 'Quispe', '4567890', 'luis.q@manttech.com', '77700004', NULL),
(5, 'Ana', 'Flores', '5678901', 'ana.f@manttech.com', '77700005', NULL),
(6, 'Roberto', 'Gutierrez', '6789012', 'roberto.g@manttech.com', '77700006', NULL),
(7, 'Maria', 'Salazar', '7890123', 'maria.s@manttech.com', '77700007', NULL),
(8, 'sergio', 'mamani', '14992775', 'sergio79@gmail.com', '69240355', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridad`
--

CREATE TABLE `prioridad` (
  `idPrioridad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `nivel` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `prioridad`
--

INSERT INTO `prioridad` (`idPrioridad`, `nombre`, `nivel`) VALUES
(1, 'BAJA', 1),
(2, 'MEDIA', 2),
(3, 'ALTA', 3),
(4, 'CRITICA', 4),
(5, 'EMERGENCIA', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuesto`
--

CREATE TABLE `repuesto` (
  `idRepuesto` int(11) NOT NULL,
  `idCategoriaRepuesto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(80) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `stockActual` int(11) DEFAULT 0,
  `stockMinimo` int(11) DEFAULT 0,
  `precioUnitario` decimal(10,2) DEFAULT 0.00,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `repuesto`
--

INSERT INTO `repuesto` (`idRepuesto`, `idCategoriaRepuesto`, `nombre`, `codigo`, `descripcion`, `stockActual`, `stockMinimo`, `precioUnitario`, `activo`) VALUES
(1, 1, 'Aceite Hidráulico ISO 46', 'ACE-001', 'Aceite para sistemas hidráulicos', 25, 10, 45.50, 1),
(2, 2, 'Filtro de Aceite Motor', 'FIL-001', 'Filtro estándar para motores', 8, 5, 32.00, 1),
(3, 3, 'Correa Trapezoidal B-65', 'COR-001', 'Correa para transmisión', 12, 5, 28.00, 1),
(4, 4, 'Rodamiento 6205-2RS', 'ROD-001', 'Rodamiento estándar', 20, 8, 15.75, 1),
(5, 5, 'Cable Eléctrico 10mm', 'CAB-001', 'Cable para instalaciones', 3, 10, 8.50, 1),
(6, 1, 'Grasa Multipropósito', 'GRA-001', 'Grasa para rodamientos', 15, 5, 22.00, 1),
(7, 7, 'Sello Hidráulico 50mm', 'SEL-001', 'Sello para cilindros', 2, 8, 18.00, 1),
(8, 2, 'Filtro de Aire Compresor', 'FIL-002', 'Filtro para compresor de aire', 6, 5, 41.00, 1),
(9, 4, 'Rodamiento 6308', 'ROD-002', 'Rodamiento de bolas', 14, 6, 24.50, 1),
(10, 5, 'Fusible 32A', 'FUS-001', 'Fusible de protección', 50, 20, 2.50, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idRol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idRol`, `nombre`, `descripcion`) VALUES
(1, 'ADMIN', 'Acceso total al sistema'),
(2, 'SUPERVISOR', 'Gestión de órdenes y técnicos'),
(3, 'TECNICO', 'Ejecución de órdenes asignadas'),
(4, 'SOLICITANTE', 'Creación y seguimiento de solicitudes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tecnico`
--

CREATE TABLE `tecnico` (
  `idTecnico` int(11) NOT NULL,
  `idPersona` int(11) NOT NULL,
  `especialidad` varchar(150) DEFAULT NULL,
  `nivelCertificacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tecnico`
--

INSERT INTO `tecnico` (`idTecnico`, `idPersona`, `especialidad`, `nivelCertificacion`) VALUES
(1, 3, 'Mecánica Industrial', 'Senior'),
(2, 4, 'Electricidad', 'Junior'),
(3, 5, 'Sistemas Hidráulicos', 'Senior'),
(4, 6, 'Mantenimiento General', 'Middle'),
(5, 8, 'sistemas', 'Junior');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicacion`
--

CREATE TABLE `ubicacion` (
  `idUbicacion` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `planta` varchar(100) DEFAULT NULL,
  `zona` varchar(100) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ubicacion`
--

INSERT INTO `ubicacion` (`idUbicacion`, `nombre`, `planta`, `zona`, `area`) VALUES
(1, 'Planta Principal - Zona A', 'Planta 1', 'Zona A', 'Producción'),
(2, 'Planta Principal - Zona B', 'Planta 1', 'Zona B', 'Ensamblaje'),
(3, 'Edificio Administrativo', 'Edificio A', 'Oficinas', 'Administración'),
(4, 'Almacén Central', 'Planta 2', 'Almacén', 'Logística'),
(5, 'Sala de Servidores', 'Edificio B', 'Zona TI', 'Tecnología'),
(6, 'Estacionamiento', 'Exterior', 'Zona E', 'Flota');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `idPersona` int(11) NOT NULL,
  `idRol` int(11) NOT NULL,
  `username` varchar(80) NOT NULL,
  `passwordHash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `intentosFallidos` int(11) DEFAULT 0,
  `ultimoAcceso` datetime DEFAULT NULL,
  `fechaCreacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `idPersona`, `idRol`, `username`, `passwordHash`, `activo`, `intentosFallidos`, `ultimoAcceso`, `fechaCreacion`) VALUES
(1, 1, 1, 'admin', '$2y$10$RDfrEnl1zAWccSSG7rtCeO6TOVDRPsV4YsTYw4tSp28XzS6xSXJ8G', 1, 0, '2026-04-07 18:57:23', '2026-04-06 21:16:46'),
(2, 2, 2, 'supervisor', '$2y$10$xJ9LOS0d0U35FBWcfC0zr.hnQ1iY62unP.iBdURVYX.wl.XE6ThT.', 1, 0, '2026-04-07 09:07:04', '2026-04-06 21:16:46'),
(3, 3, 3, 'carlos.m', '$2y$10$Zs2Sf7XUpQmxNtCQxpfPqOs2y3W1BGWZGUtVzVfT22rw5MvgNEE36', 1, 0, '2026-04-06 21:36:07', '2026-04-06 21:16:46'),
(4, 4, 3, 'luis.q', '$2y$10$t7YSCqstj3cGH87RU5yyJeWCIV5oK2f/YIjb3/8tN3THljZtIe.ui', 1, 0, NULL, '2026-04-06 21:16:46'),
(5, 5, 3, 'ana.f', '$2y$10$Qq.wdRmIOJgclUlFO.uaxOyOmKMLWm7V/vbWoj.ywRy.j8UCEjgd.', 1, 0, NULL, '2026-04-06 21:16:46'),
(6, 6, 2, 'roberto.g', '$2y$10$F4GXrmxEAd6aaHIVdQHfme7Hz3lrHSA.X9KTG42CcjdD/yG79jPLu', 1, 0, NULL, '2026-04-06 21:16:46'),
(7, 7, 4, 'maria.s', '$2y$10$P5hsan/5Y5AO/5CtGGjRdOTQVLovZ2fui1b2OlV.P1lQHcBf/n1fa', 1, 0, '2026-04-07 09:08:13', '2026-04-06 21:16:46');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_consumo_repuestos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_consumo_repuestos` (
`idRepuesto` int(11)
,`nombre` varchar(150)
,`codigo` varchar(80)
,`categoria` varchar(100)
,`stockActual` int(11)
,`stockMinimo` int(11)
,`totalConsumido` decimal(32,0)
,`ultimoUso` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ordenes_pendientes_por_tecnico`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ordenes_pendientes_por_tecnico` (
`idTecnico` int(11)
,`tecnico` varchar(201)
,`especialidad` varchar(150)
,`idOrden` int(11)
,`titulo` varchar(200)
,`nombreEstado` varchar(50)
,`nombrePrioridad` varchar(50)
,`fechaProgramada` datetime
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_consumo_repuestos`
--
DROP TABLE IF EXISTS `v_consumo_repuestos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_consumo_repuestos`  AS SELECT `r`.`idRepuesto` AS `idRepuesto`, `r`.`nombre` AS `nombre`, `r`.`codigo` AS `codigo`, `cr`.`nombre` AS `categoria`, `r`.`stockActual` AS `stockActual`, `r`.`stockMinimo` AS `stockMinimo`, coalesce(sum(`orp`.`cantidad`),0) AS `totalConsumido`, max(`orp`.`fechaUso`) AS `ultimoUso` FROM ((`repuesto` `r` left join `categoriarepuesto` `cr` on(`r`.`idCategoriaRepuesto` = `cr`.`idCategoriaRepuesto`)) left join `ordenrepuesto` `orp` on(`r`.`idRepuesto` = `orp`.`idRepuesto`)) GROUP BY `r`.`idRepuesto`, `r`.`nombre`, `r`.`codigo`, `cr`.`nombre`, `r`.`stockActual`, `r`.`stockMinimo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ordenes_pendientes_por_tecnico`
--
DROP TABLE IF EXISTS `v_ordenes_pendientes_por_tecnico`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ordenes_pendientes_por_tecnico`  AS SELECT `a`.`idTecnico` AS `idTecnico`, concat(`p`.`nombre`,' ',`p`.`apellido`) AS `tecnico`, `t`.`especialidad` AS `especialidad`, `ot`.`idOrden` AS `idOrden`, `ot`.`titulo` AS `titulo`, `e`.`nombre` AS `nombreEstado`, `pr`.`nombre` AS `nombrePrioridad`, `ot`.`fechaProgramada` AS `fechaProgramada` FROM (((((`asignacion` `a` join `tecnico` `t` on(`a`.`idTecnico` = `t`.`idTecnico`)) join `persona` `p` on(`t`.`idPersona` = `p`.`idPersona`)) join `ordentrabajo` `ot` on(`a`.`idOrden` = `ot`.`idOrden`)) join `estado` `e` on(`ot`.`idEstado` = `e`.`idEstado`)) join `prioridad` `pr` on(`ot`.`idPrioridad` = `pr`.`idPrioridad`)) WHERE `e`.`nombre` not in ('CERRADA','CANCELADA') ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD PRIMARY KEY (`idAsignacion`),
  ADD UNIQUE KEY `uq_asig` (`idOrden`,`idTecnico`),
  ADD KEY `idTecnico` (`idTecnico`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`idAuditoria`),
  ADD KEY `idUsuario` (`idUsuario`);

--
-- Indices de la tabla `categoriaequipo`
--
ALTER TABLE `categoriaequipo`
  ADD PRIMARY KEY (`idCategoriaEquipo`);

--
-- Indices de la tabla `categoriarepuesto`
--
ALTER TABLE `categoriarepuesto`
  ADD PRIMARY KEY (`idCategoriaRepuesto`);

--
-- Indices de la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD PRIMARY KEY (`idEquipo`),
  ADD KEY `idCategoriaEquipo` (`idCategoriaEquipo`),
  ADD KEY `idUbicacion` (`idUbicacion`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`idEstado`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `ordenrepuesto`
--
ALTER TABLE `ordenrepuesto`
  ADD PRIMARY KEY (`idOrdenRepuesto`),
  ADD KEY `idOrden` (`idOrden`),
  ADD KEY `idRepuesto` (`idRepuesto`);

--
-- Indices de la tabla `ordentrabajo`
--
ALTER TABLE `ordentrabajo`
  ADD PRIMARY KEY (`idOrden`),
  ADD KEY `idEquipo` (`idEquipo`),
  ADD KEY `idEstado` (`idEstado`),
  ADD KEY `idPrioridad` (`idPrioridad`),
  ADD KEY `idSolicitante` (`idSolicitante`);

--
-- Indices de la tabla `persona`
--
ALTER TABLE `persona`
  ADD PRIMARY KEY (`idPersona`);

--
-- Indices de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  ADD PRIMARY KEY (`idPrioridad`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `repuesto`
--
ALTER TABLE `repuesto`
  ADD PRIMARY KEY (`idRepuesto`),
  ADD KEY `idCategoriaRepuesto` (`idCategoriaRepuesto`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idRol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tecnico`
--
ALTER TABLE `tecnico`
  ADD PRIMARY KEY (`idTecnico`),
  ADD UNIQUE KEY `idPersona` (`idPersona`);

--
-- Indices de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  ADD PRIMARY KEY (`idUbicacion`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idPersona` (`idPersona`),
  ADD KEY `idRol` (`idRol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignacion`
--
ALTER TABLE `asignacion`
  MODIFY `idAsignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `idAuditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `categoriaequipo`
--
ALTER TABLE `categoriaequipo`
  MODIFY `idCategoriaEquipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `categoriarepuesto`
--
ALTER TABLE `categoriarepuesto`
  MODIFY `idCategoriaRepuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `equipo`
--
ALTER TABLE `equipo`
  MODIFY `idEquipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `idEstado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ordenrepuesto`
--
ALTER TABLE `ordenrepuesto`
  MODIFY `idOrdenRepuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ordentrabajo`
--
ALTER TABLE `ordentrabajo`
  MODIFY `idOrden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `persona`
--
ALTER TABLE `persona`
  MODIFY `idPersona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `idPrioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `repuesto`
--
ALTER TABLE `repuesto`
  MODIFY `idRepuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tecnico`
--
ALTER TABLE `tecnico`
  MODIFY `idTecnico` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  MODIFY `idUbicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion`
--
ALTER TABLE `asignacion`
  ADD CONSTRAINT `asignacion_ibfk_1` FOREIGN KEY (`idOrden`) REFERENCES `ordentrabajo` (`idOrden`),
  ADD CONSTRAINT `asignacion_ibfk_2` FOREIGN KEY (`idTecnico`) REFERENCES `tecnico` (`idTecnico`);

--
-- Filtros para la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD CONSTRAINT `auditoria_ibfk_1` FOREIGN KEY (`idUsuario`) REFERENCES `usuario` (`idUsuario`);

--
-- Filtros para la tabla `equipo`
--
ALTER TABLE `equipo`
  ADD CONSTRAINT `equipo_ibfk_1` FOREIGN KEY (`idCategoriaEquipo`) REFERENCES `categoriaequipo` (`idCategoriaEquipo`),
  ADD CONSTRAINT `equipo_ibfk_2` FOREIGN KEY (`idUbicacion`) REFERENCES `ubicacion` (`idUbicacion`);

--
-- Filtros para la tabla `ordenrepuesto`
--
ALTER TABLE `ordenrepuesto`
  ADD CONSTRAINT `ordenrepuesto_ibfk_1` FOREIGN KEY (`idOrden`) REFERENCES `ordentrabajo` (`idOrden`),
  ADD CONSTRAINT `ordenrepuesto_ibfk_2` FOREIGN KEY (`idRepuesto`) REFERENCES `repuesto` (`idRepuesto`);

--
-- Filtros para la tabla `ordentrabajo`
--
ALTER TABLE `ordentrabajo`
  ADD CONSTRAINT `ordentrabajo_ibfk_1` FOREIGN KEY (`idEquipo`) REFERENCES `equipo` (`idEquipo`),
  ADD CONSTRAINT `ordentrabajo_ibfk_2` FOREIGN KEY (`idEstado`) REFERENCES `estado` (`idEstado`),
  ADD CONSTRAINT `ordentrabajo_ibfk_3` FOREIGN KEY (`idPrioridad`) REFERENCES `prioridad` (`idPrioridad`),
  ADD CONSTRAINT `ordentrabajo_ibfk_4` FOREIGN KEY (`idSolicitante`) REFERENCES `usuario` (`idUsuario`);

--
-- Filtros para la tabla `repuesto`
--
ALTER TABLE `repuesto`
  ADD CONSTRAINT `repuesto_ibfk_1` FOREIGN KEY (`idCategoriaRepuesto`) REFERENCES `categoriarepuesto` (`idCategoriaRepuesto`);

--
-- Filtros para la tabla `tecnico`
--
ALTER TABLE `tecnico`
  ADD CONSTRAINT `tecnico_ibfk_1` FOREIGN KEY (`idPersona`) REFERENCES `persona` (`idPersona`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idPersona`) REFERENCES `persona` (`idPersona`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
