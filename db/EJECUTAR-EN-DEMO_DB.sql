-- ===============================================
-- ⚠️ IMPORTANTE: EJECUTAR ESTE SQL EN LA BD LOCAL
-- ===============================================
-- Base de datos: demo_db
-- Servidor: localhost
-- Usuario: demo_user
--
-- ESTE SQL CREA TODAS LAS TABLAS DEL SISTEMA POS
-- ===============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ===============================================
-- CREAR BASE DE DATOS (SI NO EXISTE)
-- ===============================================

CREATE DATABASE IF NOT EXISTS `demo_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `demo_db`;

-- ===============================================
-- TABLAS DEL SISTEMA POS
-- ===============================================

-- Tabla: cajas
CREATE TABLE IF NOT EXISTS `cajas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `punto_venta` int(11) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL COMMENT '0=egreso, 1=ingreso',
  `monto` decimal(10,2) DEFAULT 0.00,
  `medio_pago` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `codigo_venta` varchar(255) DEFAULT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `id_cliente_proveedor` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: caja_cierres
CREATE TABLE IF NOT EXISTS `caja_cierres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `diferencias` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: categorias
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: clientes (clientes del POS local)
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` text NOT NULL,
  `tipo_documento` int(11) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL,
  `condicion_iva` int(11) DEFAULT NULL,
  `email` text DEFAULT NULL,
  `telefono` text DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `compras` int(11) DEFAULT NULL,
  `ultima_compra` datetime DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documento` (`documento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: clientes_cuenta_corriente (cta cte local)
CREATE TABLE IF NOT EXISTS `clientes_cuenta_corriente` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fecha` datetime DEFAULT NULL,
  `id_cliente` bigint(20) DEFAULT NULL,
  `tipo` int(11) DEFAULT NULL COMMENT '0=debe, 1=haber',
  `descripcion` text DEFAULT NULL,
  `id_venta` bigint(20) DEFAULT NULL,
  `importe` decimal(11,2) DEFAULT 0.00,
  `metodo_pago` text DEFAULT NULL,
  `numero_recibo` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: compras
CREATE TABLE IF NOT EXISTS `compras` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `fechaIngreso` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: empresa
CREATE TABLE IF NOT EXISTS `empresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: pedidos
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) NOT NULL,
  `id_vendedor` varchar(255) NOT NULL,
  `productos` mediumtext NOT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `estado` int(11) NOT NULL,
  `usuarioConfirma` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: presupuestos
CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: productos
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `cambio_desde` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: productos_historial
CREATE TABLE IF NOT EXISTS `productos_historial` (
  `accion` varchar(9) DEFAULT 'insertar',
  `revision` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `id` int(11) NOT NULL,
  `stock` float DEFAULT NULL,
  `precio_compra` float DEFAULT NULL,
  `precio_venta` float DEFAULT NULL,
  `precio_venta_mayorista` float DEFAULT NULL,
  `nombre_usuario` varchar(255) DEFAULT NULL,
  `cambio_desde` varchar(255) NOT NULL,
  PRIMARY KEY (`id`,`revision`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: proveedores
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: proveedores_cuenta_corriente
CREATE TABLE IF NOT EXISTS `proveedores_cuenta_corriente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_compra` int(11) DEFAULT NULL,
  `id_proveedor` int(11) NOT NULL,
  `total_compra` double NOT NULL,
  `fecha_movimiento` date NOT NULL,
  `importe` double NOT NULL,
  `tipo` int(11) NOT NULL,
  `estado` int(11) NOT NULL,
  `metodo_pago` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_usuario` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ventas
CREATE TABLE IF NOT EXISTS `ventas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `respuesta_afip` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla: ventas_factura
CREATE TABLE IF NOT EXISTS `ventas_factura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) DEFAULT NULL,
  `fec_factura` varchar(15) DEFAULT NULL,
  `nro_cbte` bigint(20) DEFAULT NULL,
  `cae` varchar(100) DEFAULT NULL,
  `fec_vto_cae` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TRIGGERS PARA PRODUCTOS
-- ===============================================

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW
INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'),
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW
INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'),
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = NEW.id
$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW
BEGIN
IF NEW.stock <> OLD.stock ||
NEW.precio_compra <> OLD.precio_compra ||
NEW.precio_venta <> OLD.precio_venta ||
NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'),
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = NEW.id;
END IF;
END
$$
DELIMITER ;

-- ===============================================
-- VISTA: productos_cambios
-- ===============================================

CREATE OR REPLACE VIEW `productos_cambios` AS
SELECT
    `t2`.`fecha_hora` AS `fecha_hora`,
    `t2`.`accion` AS `accion`,
    `t1`.`id` AS `id_prod`,
    `pro`.`descripcion` AS `descripcion`,
    if(`t1`.`stock` = `t2`.`stock`,`t1`.`stock`,concat(`t1`.`stock`,' a ',`t2`.`stock`)) AS `stock`,
    if(`t1`.`precio_compra` = `t2`.`precio_compra`,`t1`.`precio_compra`,concat(`t1`.`precio_compra`,' a ',`t2`.`precio_compra`)) AS `precio_compra`,
    if(`t1`.`precio_venta` = `t2`.`precio_venta`,`t1`.`precio_venta`,concat(`t1`.`precio_venta`,' a ',`t2`.`precio_venta`)) AS `precio_venta`,
    if(`t1`.`precio_venta_mayorista` = `t2`.`precio_venta_mayorista`,`t1`.`precio_venta_mayorista`,concat(`t1`.`precio_venta_mayorista`,' a ',`t2`.`precio_venta_mayorista`)) AS `precio_venta_mayorista`,
    `t2`.`nombre_usuario` AS `nombre_usuario`,
    `t2`.`cambio_desde` AS `cambio_desde`
FROM ((`productos_historial` `t1` join `productos_historial` `t2` on(`t1`.`id` = `t2`.`id`))
left join `productos` `pro` on(`pro`.`id` = `t1`.`id`))
WHERE `t1`.`revision` = 1 AND `t2`.`revision` = 1 OR `t2`.`revision` = `t1`.`revision` + 1
ORDER BY `t1`.`id` ASC, `t2`.`revision` ASC;

-- ===============================================
-- VERIFICAR QUE SE CREARON LAS TABLAS
-- ===============================================

SHOW TABLES;

-- ===============================================
-- FIN DEL SCRIPT
-- ===============================================
SELECT 'Base de datos demo_db creada exitosamente con todas las tablas del sistema POS' as mensaje;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
