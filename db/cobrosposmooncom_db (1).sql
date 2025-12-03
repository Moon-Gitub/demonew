-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 01-12-2025 a las 22:54:55
-- Versión del servidor: 10.6.24-MariaDB
-- Versión de PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cobrosposmooncom_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `punto_venta` int(11) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT 0.00,
  `medio_pago` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `codigo_venta` varchar(255) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `id_cliente_proveedor` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caja_cierres`
--

CREATE TABLE `caja_cierres` (
  `id` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `punto_venta_cobro` int(11) DEFAULT NULL,
  `ultimo_id_caja` int(11) DEFAULT NULL,
  `total_ingresos` decimal(11,2) DEFAULT NULL,
  `total_egresos` decimal(11,2) DEFAULT NULL,
  `detalle_ingresos` text DEFAULT NULL,
  `detalle_egresos` text DEFAULT NULL,
  `apertura_siguiente_monto` decimal(11,2) DEFAULT NULL,
  `id_usuario_cierre` int(11) DEFAULT NULL,
  `detalle` varchar(255) DEFAULT NULL,
  `detalle_ingresos_manual` text DEFAULT NULL,
  `detalle_egresos_manual` text DEFAULT NULL,
  `diferencias` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `categoria` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `dominio` varchar(100) DEFAULT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `condicion_iva` int(11) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `telefono` text DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `compras` int(11) DEFAULT NULL,
  `ultima_compra` datetime DEFAULT NULL,
  `mensual` int(12) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `estado_cuenta` int(10) NOT NULL,
  `estado_bloqueo` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes_cuenta_corriente`
--

CREATE TABLE `clientes_cuenta_corriente` (
  `id` bigint(20) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `id_cliente` bigint(20) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `id_venta` bigint(20) DEFAULT NULL,
  `importe` decimal(11,2) DEFAULT 0.00,
  `metodo_pago` text DEFAULT NULL,
  `numero_recibo` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `codigo` int(11) DEFAULT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `usuarioPedido` varchar(250) DEFAULT NULL,
  `sucursalDestino` varchar(255) DEFAULT NULL,
  `usuarioConfirma` varchar(250) DEFAULT NULL,
  `productos` text NOT NULL,
  `totalNeto` float DEFAULT NULL,
  `iva` float DEFAULT NULL,
  `precepcionesIngresosBrutos` float DEFAULT NULL,
  `precepcionesIva` float DEFAULT NULL,
  `precepcionesGanancias` float DEFAULT NULL,
  `impuestoInterno` float DEFAULT NULL,
  `total` varchar(255) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `fechaEntrega` varchar(255) DEFAULT NULL,
  `fechaPago` varchar(255) DEFAULT NULL,
  `medioPago` int(11) DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `remitoNumero` varchar(255) DEFAULT NULL,
  `numeroFactura` varchar(255) DEFAULT NULL,
  `fechaEmision` varchar(255) DEFAULT NULL,
  `observacionFactura` varchar(255) DEFAULT NULL,
  `fechaIngreso` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `titular` varchar(255) DEFAULT NULL,
  `cuit` varchar(20) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `codigo_postal` varchar(255) DEFAULT NULL,
  `mail` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `ptos_venta` text DEFAULT NULL,
  `pto_venta_defecto` char(100) DEFAULT NULL,
  `almacenes` text DEFAULT NULL,
  `listas_precio` text DEFAULT NULL,
  `condicion_iva` char(1) DEFAULT NULL,
  `condicion_iibb` char(1) DEFAULT NULL,
  `numero_iibb` varchar(50) DEFAULT NULL,
  `numero_establecimiento` varchar(100) DEFAULT NULL,
  `cbu` varchar(255) DEFAULT NULL,
  `cbu_alias` varchar(255) DEFAULT NULL,
  `inicio_actividades` varchar(50) NOT NULL,
  `concepto_defecto` char(1) DEFAULT '0',
  `tipos_cbtes` text DEFAULT NULL,
  `entorno_facturacion` varchar(50) DEFAULT NULL,
  `ws_padron` varchar(50) DEFAULT NULL,
  `csr` varchar(255) DEFAULT NULL,
  `passphrase` varchar(255) DEFAULT NULL,
  `pem` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `id_vendedor` varchar(255) NOT NULL,
  `productos` mediumtext NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `estado` int(11) NOT NULL,
  `usuarioConfirma` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestos`
--

CREATE TABLE `presupuestos` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `productos` text NOT NULL,
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` decimal(11,2) DEFAULT NULL COMMENT 'Es el neto mas los intereses de tarjeta y menos los descuentos',
  `base_imponible_0` decimal(10,2) DEFAULT NULL,
  `base_imponible_2` decimal(10,2) DEFAULT NULL,
  `base_imponible_5` decimal(10,2) DEFAULT NULL,
  `base_imponible_10` decimal(10,2) DEFAULT NULL,
  `base_imponible_21` decimal(10,2) DEFAULT NULL,
  `base_imponible_27` decimal(10,2) DEFAULT NULL,
  `iva_2` decimal(10,2) DEFAULT NULL,
  `iva_5` decimal(10,2) DEFAULT NULL,
  `iva_10` decimal(10,2) DEFAULT NULL,
  `iva_21` decimal(10,2) DEFAULT NULL,
  `iva_27` decimal(10,2) DEFAULT NULL,
  `impuesto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_detalle` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` text NOT NULL,
  `estado` int(11) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `codigo` varchar(255) NOT NULL DEFAULT '',
  `id_proveedor` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `imagen` text DEFAULT NULL,
  `stock` decimal(11,2) DEFAULT 0.00,
  `deposito` decimal(10,2) NOT NULL,
  `stock_medio` decimal(11,2) DEFAULT 0.00,
  `stock_bajo` decimal(11,2) DEFAULT 0.00,
  `precio_compra` decimal(11,2) DEFAULT 0.00,
  `precio_compra_dolar` decimal(11,2) DEFAULT 0.00,
  `margen_ganancia` decimal(11,2) DEFAULT 0.00,
  `precio_venta_neto` decimal(11,2) DEFAULT 0.00,
  `tipo_iva` decimal(11,2) DEFAULT 0.00,
  `precio_venta` decimal(11,2) DEFAULT 0.00,
  `precio_venta_mayorista` decimal(11,2) DEFAULT NULL,
  `ventas` int(11) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nombre_usuario` varchar(50) DEFAULT NULL,
  `cambio_desde` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW IF NEW.stock <> OLD.stock || 
NEW.precio_compra <> OLD.precio_compra || 
NEW.precio_venta <> OLD.precio_venta ||
NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
END IF
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `productos_cambios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `productos_cambios` (
`fecha_hora` datetime
,`accion` varchar(9)
,`id_prod` int(11)
,`descripcion` varchar(255)
,`stock` varchar(27)
,`precio_compra` varchar(27)
,`precio_venta` varchar(27)
,`precio_venta_mayorista` varchar(27)
,`nombre_usuario` varchar(255)
,`cambio_desde` varchar(255)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_historial`
--

CREATE TABLE `productos_historial` (
  `accion` varchar(9) DEFAULT 'insertar',
  `revision` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `id` int(11) NOT NULL,
  `stock` float DEFAULT NULL,
  `precio_compra` float DEFAULT NULL,
  `precio_venta` float DEFAULT NULL,
  `precio_venta_mayorista` float DEFAULT NULL,
  `nombre_usuario` varchar(255) DEFAULT NULL,
  `cambio_desde` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `cuit` varchar(255) DEFAULT NULL,
  `localidad` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `inicio_actividades` varchar(255) DEFAULT NULL,
  `ingresos_brutos` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores_cuenta_corriente`
--

CREATE TABLE `proveedores_cuenta_corriente` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) DEFAULT NULL,
  `id_proveedor` int(11) NOT NULL,
  `total_compra` double NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `importe` double NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `metodo_pago` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_usuario` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL DEFAULT '',
  `usuario` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `perfil` varchar(255) NOT NULL DEFAULT '',
  `sucursal` varchar(200) DEFAULT NULL,
  `puntos_venta` varchar(100) DEFAULT NULL,
  `listas_precio` varchar(200) DEFAULT NULL,
  `foto` varchar(255) NOT NULL DEFAULT '',
  `estado` int(11) NOT NULL,
  `ultimo_login` datetime NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `uuid` varchar(34) NOT NULL,
  `codigo` int(11) NOT NULL,
  `cbte_tipo` int(11) DEFAULT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `productos` text NOT NULL,
  `neto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `neto_gravado` decimal(11,2) DEFAULT NULL COMMENT 'Es el neto mas los intereses de tarjeta y menos los descuentos',
  `base_imponible_0` decimal(10,2) DEFAULT NULL,
  `base_imponible_2` decimal(10,2) DEFAULT NULL,
  `base_imponible_5` decimal(10,2) DEFAULT NULL,
  `base_imponible_10` decimal(10,2) DEFAULT NULL,
  `base_imponible_21` decimal(10,2) DEFAULT NULL,
  `base_imponible_27` decimal(10,2) DEFAULT NULL,
  `iva_2` decimal(10,2) DEFAULT NULL,
  `iva_5` decimal(10,2) DEFAULT NULL,
  `iva_10` decimal(10,2) DEFAULT NULL,
  `iva_21` decimal(10,2) DEFAULT NULL,
  `iva_27` decimal(10,2) DEFAULT NULL,
  `impuesto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuesto_detalle` text DEFAULT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` text NOT NULL,
  `estado` int(11) DEFAULT 0,
  `observaciones_vta` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT NULL,
  `concepto` int(11) DEFAULT NULL,
  `pto_vta` int(11) DEFAULT NULL,
  `fec_desde` varchar(20) DEFAULT NULL,
  `fec_hasta` varchar(20) DEFAULT NULL,
  `fec_vencimiento` varchar(20) DEFAULT NULL,
  `asociado_tipo_cbte` int(11) DEFAULT NULL,
  `asociado_pto_vta` int(11) DEFAULT NULL,
  `asociado_nro_cbte` int(11) DEFAULT NULL,
  `pedido_afip` text DEFAULT NULL,
  `respuesta_afip` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas_factura`
--

CREATE TABLE `ventas_factura` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fec_factura` varchar(15) DEFAULT NULL,
  `nro_cbte` bigint(20) DEFAULT NULL,
  `cae` varchar(100) DEFAULT NULL,
  `fec_vto_cae` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `caja_cierres`
--
ALTER TABLE `caja_cierres`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes_cuenta_corriente`
--
ALTER TABLE `clientes_cuenta_corriente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `productos_historial`
--
ALTER TABLE `productos_historial`
  ADD PRIMARY KEY (`id`,`revision`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores_cuenta_corriente`
--
ALTER TABLE `proveedores_cuenta_corriente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`);

--
-- Indices de la tabla `ventas_factura`
--
ALTER TABLE `ventas_factura`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `caja_cierres`
--
ALTER TABLE `caja_cierres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes_cuenta_corriente`
--
ALTER TABLE `clientes_cuenta_corriente`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestos`
--
ALTER TABLE `presupuestos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos_historial`
--
ALTER TABLE `productos_historial`
  MODIFY `revision` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores_cuenta_corriente`
--
ALTER TABLE `proveedores_cuenta_corriente`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas_factura`
--
ALTER TABLE `ventas_factura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Estructura para la vista `productos_cambios`
--
DROP TABLE IF EXISTS `productos_cambios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `productos_cambios`  AS SELECT `t2`.`fecha_hora` AS `fecha_hora`, `t2`.`accion` AS `accion`, `t1`.`id` AS `id_prod`, `pro`.`descripcion` AS `descripcion`, if(`t1`.`stock` = `t2`.`stock`,`t1`.`stock`,concat(`t1`.`stock`,' a ',`t2`.`stock`)) AS `stock`, if(`t1`.`precio_compra` = `t2`.`precio_compra`,`t1`.`precio_compra`,concat(`t1`.`precio_compra`,' a ',`t2`.`precio_compra`)) AS `precio_compra`, if(`t1`.`precio_venta` = `t2`.`precio_venta`,`t1`.`precio_venta`,concat(`t1`.`precio_venta`,' a ',`t2`.`precio_venta`)) AS `precio_venta`, if(`t1`.`precio_venta_mayorista` = `t2`.`precio_venta_mayorista`,`t1`.`precio_venta_mayorista`,concat(`t1`.`precio_venta_mayorista`,' a ',`t2`.`precio_venta_mayorista`)) AS `precio_venta_mayorista`, `t2`.`nombre_usuario` AS `nombre_usuario`, `t2`.`cambio_desde` AS `cambio_desde` FROM ((`productos_historial` `t1` join `productos_historial` `t2` on(`t1`.`id` = `t2`.`id`)) left join `productos` `pro` on(`pro`.`id` = `t1`.`id`)) WHERE `t1`.`revision` = 1 AND `t2`.`revision` = 1 OR `t2`.`revision` = `t1`.`revision` + 1 ORDER BY `t1`.`id` ASC, `t2`.`revision` ASC ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
