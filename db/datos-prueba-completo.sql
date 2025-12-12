-- =====================================================
-- SCRIPT COMPLETO: RECREAR TABLAS Y DATOS DE PRUEBA
-- =====================================================
-- Este script RECREA todas las tablas (excepto usuarios) 
-- y luego inserta datos de prueba coherentes
-- =====================================================
-- IMPORTANTE: La tabla usuarios NO se toca
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
START TRANSACTION;

-- =====================================================
-- ELIMINAR TRIGGERS, VISTAS Y TABLAS (excepto usuarios)
-- =====================================================

DROP TRIGGER IF EXISTS `prod_eliminar`;
DROP TRIGGER IF EXISTS `prod_insertar`;
DROP TRIGGER IF EXISTS `prod_modificar`;
DROP VIEW IF EXISTS `productos_cambios`;

-- Eliminar todas las tablas excepto usuarios
DROP TABLE IF EXISTS `cajas`;
DROP TABLE IF EXISTS `caja_cierres`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `clientes`;
DROP TABLE IF EXISTS `clientes_cuenta_corriente`;
DROP TABLE IF EXISTS `compras`;
DROP TABLE IF EXISTS `empresa`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `presupuestos`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `productos_historial`;
DROP TABLE IF EXISTS `proveedores`;
DROP TABLE IF EXISTS `proveedores_cuenta_corriente`;
DROP TABLE IF EXISTS `ventas`;
DROP TABLE IF EXISTS `ventas_factura`;


-- =====================================================
-- CREAR TABLAS
-- =====================================================

-- Tabla cajas
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

-- Tabla caja_cierres
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

-- Tabla categorias
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `categoria` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- Tabla clientes
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
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
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- Tabla clientes_cuenta_corriente
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

-- Tabla compras
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

-- Tabla empresa
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


-- Tabla pedidos
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

-- Tabla presupuestos
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

-- Tabla productos
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

-- Tabla productos_historial
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

-- Tabla proveedores
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

-- Tabla proveedores_cuenta_corriente
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

-- Tabla ventas
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

-- Tabla ventas_factura
CREATE TABLE `ventas_factura` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) DEFAULT NULL,
  `fec_factura` varchar(15) DEFAULT NULL,
  `nro_cbte` bigint(20) DEFAULT NULL,
  `cae` varchar(100) DEFAULT NULL,
  `fec_vto_cae` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- =====================================================
-- INSERTAR DATOS DE PRUEBA
-- =====================================================

-- 1. CATEGORIAS
INSERT INTO `categorias` (`id`, `categoria`, `fecha`) VALUES
(1, 'GENERAL', NOW()),
(2, 'BEBIDAS', NOW()),
(3, 'LACTEOS', NOW()),
(4, 'CARNES', NOW()),
(5, 'FRUTAS Y VERDURAS', NOW()),
(6, 'LIMPIEZA', NOW()),
(7, 'PANADERIA', NOW()),
(8, 'GOLOSINAS', NOW());

-- 2. PROVEEDORES
INSERT INTO `proveedores` (`id`, `nombre`, `tipo_documento`, `cuit`, `localidad`, `direccion`, `telefono`, `email`, `inicio_actividades`, `ingresos_brutos`, `fecha`, `observaciones`) VALUES
(1, 'DISTRIBUIDORA SAN RAFAEL S.A.', 80, '30123456789', 'San Rafael', 'Av. Libertador 1500', '(260) 442-1000', 'ventas@distribuidora.com', '2010-01-15', '30-12345678-9', NOW(), 'Proveedor principal'),
(2, 'LACTEOS MENDOZA S.R.L.', 80, '30234567890', 'Mendoza', 'Ruta 40 Km 120', '(261) 425-2000', 'pedidos@lacteos.com', '2015-03-20', '30-23456789-0', NOW(), 'Especialista en lácteos'),
(3, 'CARNICERIA EL CAMPO', 96, '12345678', 'San Rafael', 'Av. San Martín 800', '(260) 443-3000', 'info@carniceria.com', '2018-06-10', NULL, NOW(), 'Carnes frescas'),
(4, 'BEBIDAS ANDINAS', 80, '30345678901', 'Mendoza', 'Rivadavia 2000', '(261) 430-4000', 'contacto@bebidas.com', '2012-09-05', '30-34567890-1', NOW(), 'Bebidas sin alcohol');

-- 3. PRODUCTOS
INSERT INTO `productos` (`id`, `id_categoria`, `codigo`, `id_proveedor`, `descripcion`, `imagen`, `stock`, `deposito`, `stock_medio`, `stock_bajo`, `precio_compra`, `precio_compra_dolar`, `margen_ganancia`, `precio_venta_neto`, `tipo_iva`, `precio_venta`, `precio_venta_mayorista`, `ventas`, `fecha`, `nombre_usuario`, `cambio_desde`) VALUES
(1, 1, 'PROD001', 1, 'Arroz Integral 1kg', NULL, 150.00, 150.00, 100.00, 50.00, 800.00, 0.00, 50.00, 1200.00, 21.00, 1452.00, 1300.00, 45, NOW(), 'moondesa', 'sistema'),
(2, 1, 'PROD002', 1, 'Fideos Spaghetti 500g', NULL, 200.00, 200.00, 150.00, 75.00, 450.00, 0.00, 55.00, 697.50, 21.00, 843.98, 750.00, 32, NOW(), 'moondesa', 'sistema'),
(3, 1, 'PROD003', 1, 'Aceite Girasol 900ml', NULL, 120.00, 120.00, 80.00, 40.00, 650.00, 0.00, 60.00, 1040.00, 21.00, 1258.40, 1150.00, 28, NOW(), 'moondesa', 'sistema'),
(4, 2, 'PROD004', 4, 'Agua Mineral 1.5L', NULL, 300.00, 300.00, 200.00, 100.00, 200.00, 0.00, 40.00, 280.00, 21.00, 338.80, 300.00, 18, NOW(), 'moondesa', 'sistema'),
(5, 2, 'PROD005', 4, 'Gaseosa Cola 2.25L', NULL, 180.00, 180.00, 120.00, 60.00, 350.00, 0.00, 50.00, 525.00, 21.00, 635.25, 580.00, 25, NOW(), 'moondesa', 'sistema'),
(6, 2, 'PROD006', 4, 'Jugo Naranja 1L', NULL, 100.00, 100.00, 70.00, 35.00, 280.00, 0.00, 45.00, 406.00, 21.00, 491.26, 450.00, 15, NOW(), 'moondesa', 'sistema'),
(7, 3, 'PROD007', 2, 'Leche Entera 1L', NULL, 250.00, 250.00, 150.00, 75.00, 180.00, 0.00, 35.00, 243.00, 10.50, 268.52, 250.00, 40, NOW(), 'moondesa', 'sistema'),
(8, 3, 'PROD008', 2, 'Yogur Natural 1kg', NULL, 150.00, 150.00, 100.00, 50.00, 320.00, 0.00, 50.00, 480.00, 21.00, 580.80, 530.00, 22, NOW(), 'moondesa', 'sistema'),
(9, 3, 'PROD009', 2, 'Queso Cremoso 500g', NULL, 80.00, 80.00, 50.00, 25.00, 850.00, 0.00, 55.00, 1317.50, 21.00, 1594.18, 1450.00, 12, NOW(), 'moondesa', 'sistema'),
(10, 4, 'PROD010', 3, 'Carne Molida 1kg', NULL, 50.00, 50.00, 30.00, 15.00, 2500.00, 0.00, 40.00, 3500.00, 21.00, 4235.00, 3900.00, 8, NOW(), 'moondesa', 'sistema'),
(11, 4, 'PROD011', 3, 'Pollo Entero 2kg', NULL, 40.00, 40.00, 25.00, 12.00, 1800.00, 0.00, 45.00, 2610.00, 21.00, 3158.10, 2900.00, 6, NOW(), 'moondesa', 'sistema'),
(12, 5, 'PROD012', 1, 'Tomate 1kg', NULL, 200.00, 200.00, 150.00, 75.00, 300.00, 0.00, 50.00, 450.00, 0.00, 450.00, 420.00, 30, NOW(), 'moondesa', 'sistema'),
(13, 5, 'PROD013', 1, 'Cebolla 1kg', NULL, 180.00, 180.00, 120.00, 60.00, 250.00, 0.00, 48.00, 370.00, 0.00, 370.00, 350.00, 25, NOW(), 'moondesa', 'sistema'),
(14, 6, 'PROD014', 1, 'Detergente 750ml', NULL, 100.00, 100.00, 70.00, 35.00, 420.00, 0.00, 52.00, 638.40, 21.00, 772.46, 700.00, 20, NOW(), 'moondesa', 'sistema'),
(15, 6, 'PROD015', 1, 'Lavandina 1L', NULL, 90.00, 90.00, 60.00, 30.00, 280.00, 0.00, 50.00, 420.00, 21.00, 508.20, 470.00, 18, NOW(), 'moondesa', 'sistema'),
(16, 7, 'PROD016', 1, 'Pan Lactal 500g', NULL, 120.00, 120.00, 80.00, 40.00, 380.00, 0.00, 45.00, 551.00, 10.50, 608.86, 570.00, 35, NOW(), 'moondesa', 'sistema'),
(17, 7, 'PROD017', 1, 'Facturas x12', NULL, 80.00, 80.00, 50.00, 25.00, 450.00, 0.00, 50.00, 675.00, 21.00, 816.75, 750.00, 15, NOW(), 'moondesa', 'sistema'),
(18, 8, 'PROD018', 1, 'Chocolate 100g', NULL, 200.00, 200.00, 150.00, 75.00, 180.00, 0.00, 60.00, 288.00, 21.00, 348.48, 320.00, 28, NOW(), 'moondesa', 'sistema'),
(19, 8, 'PROD019', 1, 'Caramelos x100g', NULL, 150.00, 150.00, 100.00, 50.00, 150.00, 0.00, 55.00, 232.50, 21.00, 281.33, 260.00, 22, NOW(), 'moondesa', 'sistema'),
(20, 8, 'PROD020', 1, 'Galletas Dulces 300g', NULL, 180.00, 180.00, 120.00, 60.00, 320.00, 0.00, 50.00, 480.00, 21.00, 580.80, 540.00, 30, NOW(), 'moondesa', 'sistema');

-- 4. CLIENTES
INSERT INTO `clientes` (`id`, `nombre`, `tipo_documento`, `documento`, `condicion_iva`, `email`, `telefono`, `direccion`, `fecha_nacimiento`, `compras`, `ultima_compra`, `fecha`, `observaciones`) VALUES
(100, 'SUPERMERCADO CENTRAL', 80, '20123456789', 1, 'contacto@supercentral.com', '(260) 444-1234', 'Av. San Martín 500', NULL, 45, '2025-01-20 14:30:00', NOW(), 'Cliente frecuente'),
(101, 'ALMACEN DON JUAN', 80, '20987654321', 1, 'ventas@donjuan.com', '(260) 444-5678', 'Rivadavia 850', NULL, 32, '2025-01-19 10:15:00', NOW(), NULL),
(102, 'KIOSCO LA ESQUINA', 96, '12345678', 5, 'kiosco@laesquina.com', '(260) 444-9012', 'Esquina Mitre y Belgrano', NULL, 18, '2025-01-18 16:45:00', NOW(), 'Cliente minorista'),
(103, 'MINIMARKET SAN RAFAEL', 80, '20345678901', 1, 'info@minimarket.com', '(260) 444-3456', 'Av. Libertador 1200', NULL, 28, '2025-01-17 09:20:00', NOW(), NULL),
(104, 'DISTRIBUIDORA EL PROGRESO', 80, '20456789012', 1, 'pedidos@elprogreso.com', '(260) 444-7890', 'Ruta 40 Km 5', NULL, 15, '2025-01-16 11:00:00', NOW(), 'Cliente mayorista'),
(105, 'CONSUMIDOR FINAL', 99, '0', 5, NULL, NULL, NULL, NULL, 0, NULL, NOW(), 'Cliente genérico');

-- 5. EMPRESA
INSERT INTO `empresa` (`id`, `razon_social`, `titular`, `cuit`, `domicilio`, `localidad`, `codigo_postal`, `mail`, `telefono`, `ptos_venta`, `pto_venta_defecto`, `almacenes`, `listas_precio`, `condicion_iva`, `condicion_iibb`, `numero_iibb`, `numero_establecimiento`, `cbu`, `cbu_alias`, `inicio_actividades`, `concepto_defecto`, `tipos_cbtes`, `entorno_facturacion`, `ws_padron`, `csr`, `passphrase`, `pem`, `logo`) VALUES
(1, 'MOON DESARROLLOS S.A.', 'Moon Desarrollos', '30-12345678-9', 'Av. San Martín 1000', 'San Rafael', '5600', 'info@moondesa.com', '(260) 444-0000', '["1","2"]', '1', '["stock"]', '["precio_venta","precio_venta_mayorista"]', '1', '1', '30-12345678-9', '0001', NULL, NULL, '2020-01-01', '1', '["1","6","11"]', 'testing', 'ws_sr_padron_a5', NULL, NULL, NULL, NULL);

-- 6. VENTAS
INSERT INTO `ventas` (`id`, `uuid`, `codigo`, `cbte_tipo`, `id_cliente`, `id_vendedor`, `productos`, `neto`, `neto_gravado`, `base_imponible_0`, `base_imponible_2`, `base_imponible_5`, `base_imponible_10`, `base_imponible_21`, `base_imponible_27`, `iva_2`, `iva_5`, `iva_10`, `iva_21`, `iva_27`, `impuesto`, `impuesto_detalle`, `total`, `metodo_pago`, `estado`, `observaciones_vta`, `observaciones`, `fecha`, `concepto`, `pto_vta`, `fec_desde`, `fec_hasta`, `fec_vencimiento`, `asociado_tipo_cbte`, `asociado_pto_vta`, `asociado_nro_cbte`, `pedido_afip`, `respuesta_afip`) VALUES
(1000, '550e8400-e29b-41d4-a716-446655440001', 1001, 1, 100, 1, '[{"id":"1","descripcion":"Arroz Integral 1kg","cantidad":"10","categoria":"1","stock":"0","precio_compra":"800.00","precio":"1452","total":"14520"}]', 14520.00, 14520.00, 0.00, 0.00, 0.00, 0.00, 14520.00, 0.00, 0.00, 0.00, 0.00, 3049.20, 0.00, 3049.20, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"14520","iva":"3049.20"}]', 17569.20, '[{"tipo":"Efectivo","entrega":"17569.20"}]', 1, '', NULL, '2025-01-20 14:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1001, '550e8400-e29b-41d4-a716-446655440002', 1002, 1, 101, 1, '[{"id":"2","descripcion":"Fideos Spaghetti 500g","cantidad":"5","categoria":"1","stock":"0","precio_compra":"450.00","precio":"843.98","total":"4219.90"}]', 4219.90, 4219.90, 0.00, 0.00, 0.00, 0.00, 4219.90, 0.00, 0.00, 0.00, 0.00, 886.18, 0.00, 886.18, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"4219.90","iva":"886.18"}]', 5106.08, '[{"tipo":"TD-","entrega":"5106.08"}]', 1, '', NULL, '2025-01-19 10:15:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1002, '550e8400-e29b-41d4-a716-446655440003', 1003, 1, 102, 1, '[{"id":"4","descripcion":"Agua Mineral 1.5L","cantidad":"20","categoria":"2","stock":"0","precio_compra":"200.00","precio":"338.80","total":"6776"}]', 6776.00, 6776.00, 0.00, 0.00, 0.00, 0.00, 6776.00, 0.00, 0.00, 0.00, 0.00, 1422.96, 0.00, 1422.96, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"6776","iva":"1422.96"}]', 8198.96, '[{"tipo":"Efectivo","entrega":"8198.96"}]', 1, '', NULL, '2025-01-18 16:45:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1003, '550e8400-e29b-41d4-a716-446655440004', 1004, 1, 103, 1, '[{"id":"7","descripcion":"Leche Entera 1L","cantidad":"8","categoria":"3","stock":"0","precio_compra":"180.00","precio":"268.52","total":"2148.16"}]', 2148.16, 2148.16, 0.00, 0.00, 0.00, 0.00, 2148.16, 0.00, 0.00, 0.00, 225.56, 0.00, 0.00, 225.56, '[{"id":4,"descripcion":"IVA 10.5%","baseImponible":"2148.16","iva":"225.56"}]', 2373.72, '[{"tipo":"TR--","entrega":"2373.72"}]', 1, '', NULL, '2025-01-17 09:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1004, '550e8400-e29b-41d4-a716-446655440005', 1005, 1, 104, 1, '[{"id":"10","descripcion":"Carne Molida 1kg","cantidad":"15","categoria":"4","stock":"0","precio_compra":"2500.00","precio":"4235","total":"63525"}]', 63525.00, 63525.00, 0.00, 0.00, 0.00, 0.00, 63525.00, 0.00, 0.00, 0.00, 0.00, 13340.25, 0.00, 13340.25, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"63525","iva":"13340.25"}]', 76865.25, '[{"tipo":"TC-","entrega":"76865.25"}]', 1, '', NULL, '2025-01-16 11:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1005, '550e8400-e29b-41d4-a716-446655440006', 1006, 1, 100, 1, '[{"id":"1","descripcion":"Arroz Integral 1kg","cantidad":"12","categoria":"1","stock":"0","precio_compra":"800.00","precio":"1452","total":"17424"}]', 17424.00, 17424.00, 0.00, 0.00, 0.00, 0.00, 17424.00, 0.00, 0.00, 0.00, 0.00, 3659.04, 0.00, 3659.04, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"17424","iva":"3659.04"}]', 21083.04, '[{"tipo":"Efectivo","entrega":"21083.04"}]', 1, '', NULL, '2025-01-15 15:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1006, '550e8400-e29b-41d4-a716-446655440007', 1007, 1, 101, 1, '[{"id":"5","descripcion":"Gaseosa Cola 2.25L","cantidad":"6","categoria":"2","stock":"0","precio_compra":"350.00","precio":"635.25","total":"3811.50"}]', 3811.50, 3811.50, 0.00, 0.00, 0.00, 0.00, 3811.50, 0.00, 0.00, 0.00, 0.00, 800.42, 0.00, 800.42, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"3811.50","iva":"800.42"}]', 4611.92, '[{"tipo":"TD-","entrega":"4611.92"}]', 1, '', NULL, '2025-01-14 13:20:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1007, '550e8400-e29b-41d4-a716-446655440008', 1008, 1, 105, 1, '[{"id":"18","descripcion":"Chocolate 100g","cantidad":"3","categoria":"8","stock":"0","precio_compra":"180.00","precio":"348.48","total":"1045.44"}]', 1045.44, 1045.44, 0.00, 0.00, 0.00, 0.00, 1045.44, 0.00, 0.00, 0.00, 0.00, 219.54, 0.00, 219.54, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"1045.44","iva":"219.54"}]', 1264.98, '[{"tipo":"Efectivo","entrega":"1264.98"}]', 1, '', NULL, '2025-01-13 17:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1008, '550e8400-e29b-41d4-a716-446655440009', 1009, 1, 102, 1, '[{"id":"12","descripcion":"Tomate 1kg","cantidad":"25","categoria":"5","stock":"0","precio_compra":"300.00","precio":"450","total":"11250"}]', 11250.00, 11250.00, 11250.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{"id":3,"descripcion":"IVA 0%","baseImponible":"11250","iva":"0"}]', 11250.00, '[{"tipo":"Efectivo","entrega":"8000"},{"tipo":"TD-","entrega":"3250"}]', 1, '', NULL, '2025-01-12 11:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1009, '550e8400-e29b-41d4-a716-446655440010', 1010, 1, 103, 1, '[{"id":"8","descripcion":"Yogur Natural 1kg","cantidad":"10","categoria":"3","stock":"0","precio_compra":"320.00","precio":"580.80","total":"5808"}]', 5808.00, 5808.00, 0.00, 0.00, 0.00, 0.00, 5808.00, 0.00, 0.00, 0.00, 0.00, 1219.68, 0.00, 1219.68, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"5808","iva":"1219.68"}]', 7027.68, '[{"tipo":"TC-","entrega":"7027.68"}]', 1, '', NULL, '2025-01-11 14:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1010, '550e8400-e29b-41d4-a716-446655440011', 1011, 1, 100, 1, '[{"id":"9","descripcion":"Queso Cremoso 500g","cantidad":"10","categoria":"3","stock":"0","precio_compra":"850.00","precio":"1594.18","total":"15941.80"}]', 15941.80, 15941.80, 0.00, 0.00, 0.00, 0.00, 15941.80, 0.00, 0.00, 0.00, 0.00, 3347.78, 0.00, 3347.78, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"15941.80","iva":"3347.78"}]', 19289.58, '[{"tipo":"CC","entrega":"19289.58"}]', 2, '', NULL, '2025-01-10 09:00:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL),
(1011, '550e8400-e29b-41d4-a716-446655440012', 1012, 1, 104, 1, '[{"id":"11","descripcion":"Pollo Entero 2kg","cantidad":"20","categoria":"4","stock":"0","precio_compra":"1800.00","precio":"3158.10","total":"63162"}]', 63162.00, 63162.00, 0.00, 0.00, 0.00, 0.00, 63162.00, 0.00, 0.00, 0.00, 0.00, 13264.02, 0.00, 13264.02, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"63162","iva":"13264.02"}]', 76426.02, '[{"tipo":"CC","entrega":"76426.02"}]', 2, '', NULL, '2025-01-09 14:30:00', 1, 1, '', '', '', 0, 0, 0, NULL, NULL);

-- 7. VENTAS FACTURADAS
INSERT INTO `ventas_factura` (`id`, `id_venta`, `fec_factura`, `nro_cbte`, `cae`, `fec_vto_cae`) VALUES
(100, 1000, '20250120', 1, '71234567890123', '20250220'),
(101, 1001, '20250119', 2, '71234567890124', '20250219'),
(102, 1002, '20250118', 3, '71234567890125', '20250218'),
(103, 1003, '20250117', 4, '71234567890126', '20250217'),
(104, 1004, '20250116', 5, '71234567890127', '20250216'),
(105, 1005, '20250115', 6, '71234567890128', '20250215'),
(106, 1006, '20250114', 7, '71234567890129', '20250214'),
(107, 1007, '20250113', 8, '71234567890130', '20250213'),
(108, 1008, '20250112', 9, '71234567890131', '20250212'),
(109, 1009, '20250111', 10, '71234567890132', '20250211');

-- 8. CUENTA CORRIENTE CLIENTES
INSERT INTO `clientes_cuenta_corriente` (`id`, `fecha`, `id_cliente`, `tipo`, `descripcion`, `id_venta`, `importe`, `metodo_pago`, `numero_recibo`) VALUES
(1000, '2025-01-10 09:00:00', 100, 0, 'Venta a crédito - Factura 1011', 1010, 19289.58, NULL, NULL),
(1001, '2025-01-09 14:30:00', 104, 0, 'Venta a crédito - Factura 1012', 1011, 76426.02, NULL, NULL),
(1002, '2025-01-11 10:00:00', 100, 1, 'Pago parcial cuenta corriente', 1010, 10000.00, 'Efectivo', 1),
(1003, '2025-01-11 11:30:00', 100, 1, 'Pago parcial cuenta corriente', 1010, 5000.00, 'Transferencia', 2),
(1004, '2025-01-10 15:00:00', 104, 1, 'Pago parcial cuenta corriente', 1011, 30000.00, 'Tarjeta Crédito', 3),
(1005, '2025-01-10 16:00:00', 104, 1, 'Pago parcial cuenta corriente', 1011, 20000.00, 'Transferencia', 4);

-- 9. CAJAS
INSERT INTO `cajas` (`id`, `fecha`, `id_usuario`, `punto_venta`, `tipo`, `monto`, `medio_pago`, `descripcion`, `codigo_venta`, `id_venta`, `id_cliente_proveedor`, `observaciones`) VALUES
(1000, '2025-01-20 14:30:00', 1, 1, 1, 17569.20, 'Efectivo', 'Venta Factura 1001 - SUPERMERCADO CENTRAL', '1001', 1000, 100, NULL),
(1001, '2025-01-18 16:45:00', 1, 1, 1, 8198.96, 'Efectivo', 'Venta Factura 1003 - KIOSCO LA ESQUINA', '1003', 1002, 102, NULL),
(1002, '2025-01-15 15:30:00', 1, 1, 1, 21083.04, 'Efectivo', 'Venta Factura 1006 - SUPERMERCADO CENTRAL', '1006', 1005, 100, NULL),
(1003, '2025-01-13 17:00:00', 1, 1, 1, 1264.98, 'Efectivo', 'Venta Factura 1008 - CONSUMIDOR FINAL', '1008', 1007, 105, NULL),
(1004, '2025-01-12 11:30:00', 1, 1, 1, 11250.00, 'Efectivo', 'Venta Factura 1009 - KIOSCO LA ESQUINA', '1009', 1008, 102, NULL),
(1005, '2025-01-19 10:15:00', 1, 1, 1, 5106.08, 'Tarjeta Débito', 'Venta Factura 1002 - ALMACEN DON JUAN', '1002', 1001, 101, NULL),
(1006, '2025-01-14 13:20:00', 1, 1, 1, 4611.92, 'Tarjeta Débito', 'Venta Factura 1007 - ALMACEN DON JUAN', '1007', 1006, 101, NULL),
(1007, '2025-01-16 11:00:00', 1, 1, 1, 76865.25, 'Tarjeta Crédito', 'Venta Factura 1005 - DISTRIBUIDORA EL PROGRESO', '1005', 1004, 104, NULL),
(1008, '2025-01-11 14:00:00', 1, 1, 1, 7027.68, 'Tarjeta Crédito', 'Venta Factura 1010 - MINIMARKET SAN RAFAEL', '1010', 1009, 103, NULL),
(1009, '2025-01-17 09:20:00', 1, 1, 1, 2373.72, 'Transferencia', 'Venta Factura 1004 - MINIMARKET SAN RAFAEL', '1004', 1003, 103, NULL),
(1010, '2025-01-11 10:00:00', 1, 1, 1, 10000.00, 'Efectivo', 'Cobro Cta. Cte. - SUPERMERCADO CENTRAL', NULL, NULL, 100, 'Pago parcial'),
(1011, '2025-01-11 11:30:00', 1, 1, 1, 5000.00, 'Transferencia', 'Cobro Cta. Cte. - SUPERMERCADO CENTRAL', NULL, NULL, 100, 'Pago parcial'),
(1012, '2025-01-10 15:00:00', 1, 1, 1, 30000.00, 'Tarjeta Crédito', 'Cobro Cta. Cte. - DISTRIBUIDORA EL PROGRESO', NULL, NULL, 104, 'Pago parcial'),
(1013, '2025-01-10 16:00:00', 1, 1, 1, 20000.00, 'Transferencia', 'Cobro Cta. Cte. - DISTRIBUIDORA EL PROGRESO', NULL, NULL, 104, 'Pago parcial'),
(1014, '2025-01-15 16:00:00', 1, 1, 2, 5000.00, 'Efectivo', 'Pago a proveedor', NULL, NULL, NULL, 'Compra de mercadería'),
(1015, '2025-01-14 12:00:00', 1, 1, 2, 2500.00, 'Transferencia', 'Gasto administrativo', NULL, NULL, NULL, 'Servicios');

-- 10. COMPRAS
INSERT INTO `compras` (`id`, `codigo`, `id_proveedor`, `usuarioPedido`, `sucursalDestino`, `usuarioConfirma`, `productos`, `totalNeto`, `iva`, `precepcionesIngresosBrutos`, `precepcionesIva`, `precepcionesGanancias`, `impuestoInterno`, `total`, `estado`, `descuento`, `fecha`, `fechaEntrega`, `fechaPago`, `medioPago`, `observacion`, `tipo`, `remitoNumero`, `numeroFactura`, `fechaEmision`, `observacionFactura`, `fechaIngreso`) VALUES
(1, 1, 1, 'moondesa', 'stock', 'moondesa', '[{"id":"1","descripcion":"Arroz Integral 1kg","cantidad":"200","precio":"800","total":"160000"},{"id":"2","descripcion":"Fideos Spaghetti 500g","cantidad":"150","precio":"450","total":"67500"}]', 227500.00, 47775.00, 0.00, 0.00, 0.00, 0.00, '275275', 1, 0.00, '2025-01-05 10:00:00', '2025-01-10', '2025-01-15', 1, 'Compra inicial', 'compra', 'RE-0001', 'FC-0001', '2025-01-05', 'Factura A', '2025-01-10'),
(2, 2, 2, 'moondesa', 'stock', 'moondesa', '[{"id":"7","descripcion":"Leche Entera 1L","cantidad":"300","precio":"180","total":"54000"},{"id":"8","descripcion":"Yogur Natural 1kg","cantidad":"200","precio":"320","total":"64000"}]', 118000.00, 12390.00, 0.00, 0.00, 0.00, 0.00, '130390', 1, 0.00, '2025-01-08 11:00:00', '2025-01-12', '2025-01-18', 2, 'Compra lácteos', 'compra', 'RE-0002', 'FC-0002', '2025-01-08', 'Factura A', '2025-01-12'),
(3, 3, 3, 'moondesa', 'stock', 'moondesa', '[{"id":"10","descripcion":"Carne Molida 1kg","cantidad":"100","precio":"2500","total":"250000"},{"id":"11","descripcion":"Pollo Entero 2kg","cantidad":"80","precio":"1800","total":"144000"}]', 394000.00, 82740.00, 0.00, 0.00, 0.00, 0.00, '476740', 1, 0.00, '2025-01-10 09:00:00', '2025-01-15', '2025-01-20', 3, 'Compra carnes', 'compra', 'RE-0003', 'FC-0003', '2025-01-10', 'Factura A', '2025-01-15');

-- 11. PROVEEDORES CUENTA CORRIENTE
INSERT INTO `proveedores_cuenta_corriente` (`id`, `id_compra`, `id_proveedor`, `total_compra`, `fecha_movimiento`, `importe`, `tipo`, `estado`, `metodo_pago`, `descripcion`, `id_usuario`) VALUES
(1, 1, 1, 275275.00, '2025-01-05', 275275.00, 0, 1, NULL, 'Compra inicial', 'moondesa'),
(2, 1, 1, 275275.00, '2025-01-15', 275275.00, 1, 1, 'Transferencia', 'Pago compra inicial', 'moondesa'),
(3, 2, 2, 130390.00, '2025-01-08', 130390.00, 0, 1, NULL, 'Compra lácteos', 'moondesa'),
(4, 2, 2, 130390.00, '2025-01-18', 130390.00, 1, 1, 'Efectivo', 'Pago compra lácteos', 'moondesa'),
(5, 3, 3, 476740.00, '2025-01-10', 476740.00, 0, 0, NULL, 'Compra carnes', 'moondesa');

-- 12. PRESUPUESTOS
INSERT INTO `presupuestos` (`id`, `id_cliente`, `id_vendedor`, `productos`, `neto`, `neto_gravado`, `base_imponible_0`, `base_imponible_2`, `base_imponible_5`, `base_imponible_10`, `base_imponible_21`, `base_imponible_27`, `iva_2`, `iva_5`, `iva_10`, `iva_21`, `iva_27`, `impuesto`, `impuesto_detalle`, `total`, `metodo_pago`, `estado`, `observaciones`, `fecha`) VALUES
(1, 100, 1, '[{"id":"1","descripcion":"Arroz Integral 1kg","cantidad":"50","precio":"1452","total":"72600"}]', 72600.00, 72600.00, 0.00, 0.00, 0.00, 0.00, 72600.00, 0.00, 0.00, 0.00, 0.00, 15246.00, 0.00, 15246.00, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"72600","iva":"15246"}]', 87846.00, '[{"tipo":"CC","entrega":"87846"}]', 0, 'Presupuesto pendiente', NOW()),
(2, 101, 1, '[{"id":"5","descripcion":"Gaseosa Cola 2.25L","cantidad":"30","precio":"635.25","total":"19057.50"}]', 19057.50, 19057.50, 0.00, 0.00, 0.00, 0.00, 19057.50, 0.00, 0.00, 0.00, 0.00, 4002.08, 0.00, 4002.08, '[{"id":5,"descripcion":"IVA 21%","baseImponible":"19057.50","iva":"4002.08"}]', 23059.58, '[{"tipo":"Efectivo","entrega":"23059.58"}]', 0, 'Presupuesto pendiente', NOW());

-- 13. PEDIDOS
INSERT INTO `pedidos` (`id`, `codigo`, `id_vendedor`, `productos`, `origen`, `destino`, `estado`, `usuarioConfirma`, `fecha`) VALUES
(1, 1, '1', '[{"id":"1","descripcion":"Arroz Integral 1kg","cantidad":"50","precio":"1452"}]', 'stock', 'sucursal1', 1, 'moondesa', NOW()),
(2, 2, '1', '[{"id":"4","descripcion":"Agua Mineral 1.5L","cantidad":"100","precio":"338.80"}]', 'stock', 'sucursal2', 0, '', NOW());

-- 14. CAJA CIERRES
INSERT INTO `caja_cierres` (`id`, `fecha_hora`, `punto_venta_cobro`, `ultimo_id_caja`, `total_ingresos`, `total_egresos`, `detalle_ingresos`, `detalle_egresos`, `apertura_siguiente_monto`, `id_usuario_cierre`, `detalle`, `detalle_ingresos_manual`, `detalle_egresos_manual`, `diferencias`) VALUES
(1, '2025-01-20 18:00:00', 1, 1000, 17569.20, 0.00, '{"Efectivo":17569.20}', '{}', 17569.20, 1, 'Cierre diario', NULL, NULL, NULL),
(2, '2025-01-19 18:00:00', 1, 1005, 13218.00, 0.00, '{"Tarjeta Débito":5106.08,"Efectivo":8111.92}', '{}', 13218.00, 1, 'Cierre diario', NULL, NULL, NULL);


-- =====================================================
-- CONFIGURAR ÍNDICES Y AUTO_INCREMENT
-- =====================================================

ALTER TABLE `cajas` ADD PRIMARY KEY (`id`) USING BTREE;
ALTER TABLE `caja_cierres` ADD PRIMARY KEY (`id`) USING BTREE;
ALTER TABLE `categorias` ADD PRIMARY KEY (`id`);
ALTER TABLE `clientes` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `documento` (`documento`);
ALTER TABLE `clientes_cuenta_corriente` ADD PRIMARY KEY (`id`);
ALTER TABLE `compras` ADD PRIMARY KEY (`id`);
ALTER TABLE `empresa` ADD PRIMARY KEY (`id`);
ALTER TABLE `pedidos` ADD PRIMARY KEY (`id`);
ALTER TABLE `presupuestos` ADD PRIMARY KEY (`id`);
ALTER TABLE `productos` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `codigo` (`codigo`);
ALTER TABLE `productos_historial` ADD PRIMARY KEY (`id`,`revision`);
ALTER TABLE `proveedores` ADD PRIMARY KEY (`id`);
ALTER TABLE `proveedores_cuenta_corriente` ADD PRIMARY KEY (`id`);
ALTER TABLE `ventas` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `uuid` (`uuid`);
ALTER TABLE `ventas_factura` ADD PRIMARY KEY (`id`);

ALTER TABLE `cajas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1016;
ALTER TABLE `caja_cierres` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `clientes` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;
ALTER TABLE `clientes_cuenta_corriente` MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1006;
ALTER TABLE `compras` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `empresa` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `pedidos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `presupuestos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `productos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
ALTER TABLE `productos_historial` MODIFY `revision` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `proveedores` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `proveedores_cuenta_corriente` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `ventas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1012;
ALTER TABLE `ventas_factura` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

-- =====================================================
-- CREAR TRIGGERS (sin DEFINER problemático)
-- =====================================================

DELIMITER $$

CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` 
FOR EACH ROW 
INSERT INTO productos_historial 
SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id$$

CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` 
FOR EACH ROW 
INSERT INTO productos_historial 
SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde 
FROM productos AS pro WHERE pro.id = NEW.id$$

CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` 
FOR EACH ROW 
IF NEW.stock <> OLD.stock || 
NEW.precio_compra <> OLD.precio_compra || 
NEW.precio_venta <> OLD.precio_venta ||
NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial 
SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde 
FROM productos AS pro WHERE pro.id = NEW.id;
END IF$$

DELIMITER ;

-- =====================================================
-- CREAR VISTA productos_cambios (sin DEFINER problemático)
-- =====================================================

CREATE VIEW `productos_cambios` AS 
SELECT 
  `t2`.`fecha_hora` AS `fecha_hora`, 
  `t2`.`accion` AS `accion`, 
  `t1`.`id` AS `id_prod`, 
  `pro`.`descripcion` AS `descripcion`, 
  IF(`t1`.`stock` = `t2`.`stock`, `t1`.`stock`, CONCAT(`t1`.`stock`, ' a ', `t2`.`stock`)) AS `stock`, 
  IF(`t1`.`precio_compra` = `t2`.`precio_compra`, `t1`.`precio_compra`, CONCAT(`t1`.`precio_compra`, ' a ', `t2`.`precio_compra`)) AS `precio_compra`, 
  IF(`t1`.`precio_venta` = `t2`.`precio_venta`, `t1`.`precio_venta`, CONCAT(`t1`.`precio_venta`, ' a ', `t2`.`precio_venta`)) AS `precio_venta`, 
  IF(`t1`.`precio_venta_mayorista` = `t2`.`precio_venta_mayorista`, `t1`.`precio_venta_mayorista`, CONCAT(`t1`.`precio_venta_mayorista`, ' a ', `t2`.`precio_venta_mayorista`)) AS `precio_venta_mayorista`, 
  `t2`.`nombre_usuario` AS `nombre_usuario`, 
  `t2`.`cambio_desde` AS `cambio_desde` 
FROM ((`productos_historial` `t1` 
JOIN `productos_historial` `t2` ON(`t1`.`id` = `t2`.`id`)) 
LEFT JOIN `productos` `pro` ON(`pro`.`id` = `t1`.`id`)) 
WHERE (`t1`.`revision` = 1 AND `t2`.`revision` = 1) OR (`t2`.`revision` = `t1`.`revision` + 1) 
ORDER BY `t1`.`id` ASC, `t2`.`revision` ASC;

-- =====================================================
-- FINALIZAR TRANSACCIÓN
-- =====================================================

COMMIT;

-- =====================================================
-- RESUMEN
-- =====================================================
-- ✅ Todas las tablas recreadas (excepto usuarios)
-- ✅ Datos de prueba insertados
-- ✅ Índices y AUTO_INCREMENT configurados
-- ✅ Triggers recreados sin DEFINER problemático
-- ✅ Vista productos_cambios recreada
-- ✅ Tabla usuarios preservada intacta
-- =====================================================
