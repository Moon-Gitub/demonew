-- ================================================================
-- Tablas: listas_precio, medios_pago, pantallas, permisos_rol
-- Con datos iniciales usados en el proyecto
-- Ejecutar en la base de datos del sistema (una sola vez).
-- ================================================================

-- ===================== LISTAS PRECIO =====================

CREATE TABLE IF NOT EXISTS `listas_precio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL DEFAULT 1,
  `codigo` varchar(64) NOT NULL COMMENT 'Clave única: precio_venta, empleados, etc.',
  `nombre` varchar(128) NOT NULL COMMENT 'Nombre visible en ventas',
  `base_precio` varchar(64) NOT NULL DEFAULT 'precio_venta' COMMENT 'Columna producto: precio_venta, precio_compra',
  `tipo_descuento` varchar(32) NOT NULL DEFAULT 'ninguno' COMMENT 'ninguno, porcentaje',
  `valor_descuento` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje 0-100 si tipo_descuento=porcentaje',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_listas_precio_empresa_codigo` (`id_empresa`,`codigo`),
  KEY `idx_listas_precio_empresa_activo` (`id_empresa`,`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `listas_precio` (`id`, `id_empresa`, `codigo`, `nombre`, `base_precio`, `tipo_descuento`, `valor_descuento`, `orden`, `activo`) VALUES
(1, 1, 'precio_venta', 'Precio Público', 'precio_venta', 'ninguno', 0.00, 10, 1),
(2, 1, 'precio_compra', 'Precio Costo', 'precio_compra', 'ninguno', 0.00, 20, 1),
(3, 1, 'trabajadores_valle_grande', 'Trabajadores Valle Grande', 'precio_venta', 'porcentaje', 15.00, 30, 1),
(4, 1, 'empleados', 'Empleados', 'precio_venta', 'porcentaje', 20.00, 40, 1);


-- ===================== MEDIOS PAGO =====================

CREATE TABLE IF NOT EXISTS `medios_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL COMMENT 'Código único del medio (ej: EF, TD, TC)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `requiere_codigo` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_banco` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_numero` tinyint(1) NOT NULL DEFAULT 0,
  `requiere_fecha` tinyint(1) NOT NULL DEFAULT 0,
  `orden` int(11) NOT NULL DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT IGNORE INTO `medios_pago` (`id`, `codigo`, `nombre`, `descripcion`, `activo`, `requiere_codigo`, `requiere_banco`, `requiere_numero`, `requiere_fecha`, `orden`) VALUES
(1, 'EF', 'Efectivo', 'Pago en efectivo', 1, 0, 0, 0, 0, 1),
(2, 'TD', 'Tarjeta Débito', 'Pago con tarjeta de débito', 1, 1, 0, 0, 0, 2),
(3, 'TC', 'Tarjeta Crédito', 'Pago con tarjeta de crédito', 1, 1, 0, 0, 0, 3),
(4, 'CH', 'Cheque', 'Pago con cheque', 1, 0, 1, 1, 1, 4),
(5, 'TR', 'Transferencia', 'Transferencia bancaria', 1, 0, 1, 1, 0, 5),
(6, 'CC', 'Cuenta Corriente', 'Pago a cuenta corriente', 1, 0, 0, 0, 0, 6);


-- ===================== PANTALLAS =====================

CREATE TABLE IF NOT EXISTS `pantallas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(128) NOT NULL COMMENT 'Ruta del sistema (ej: crear-venta-caja)',
  `nombre` varchar(128) NOT NULL COMMENT 'Nombre para mostrar en el panel',
  `agrupacion` varchar(64) NOT NULL DEFAULT 'General',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pantallas_codigo` (`codigo`),
  KEY `idx_pantallas_agrupacion_orden` (`agrupacion`,`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `pantallas` (`id`, `codigo`, `nombre`, `agrupacion`, `orden`) VALUES
(1, 'inicio', 'Inicio', 'General', 10),
(2, 'empresa', 'Datos Empresa', 'Empresa', 20),
(3, 'usuarios', 'Usuarios', 'Empresa', 30),
(4, 'listas-precio', 'Listas de Precio', 'Empresa', 40),
(5, 'balanzas-formatos', 'Formatos de Balanza', 'Empresa', 50),
(6, 'medios-pago', 'Cargar Medios de Pago', 'Empresa', 60),
(7, 'permisos-rol', 'Permisos por Rol', 'Empresa', 70),
(8, 'productos', 'Administrar Productos', 'Productos', 100),
(9, 'categorias', 'Categorías', 'Productos', 110),
(10, 'combos', 'Combos', 'Productos', 120),
(11, 'impresion-precios', 'Imprimir Precios', 'Productos', 130),
(12, 'productos-importar-excel', 'Importar Excel (v1)', 'Productos', 140),
(13, 'productos-importar-excel2', 'Importar Excel', 'Productos', 150),
(14, 'productos-stock-medio', 'Productos Stock Medio', 'Productos', 160),
(15, 'productos-stock-bajo', 'Productos Stock Bajo', 'Productos', 170),
(16, 'productos-stock-valorizado', 'Productos Stock Valorizado', 'Productos', 180),
(17, 'productos-historial', 'Productos Historial', 'Productos', 190),
(18, 'pedidos-generar-movimiento', 'Generar Movimiento', 'Mov. Productos', 200),
(19, 'pedidos-nuevos', 'Validar Movimiento', 'Mov. Productos', 210),
(20, 'pedidos-validados', 'Movimientos Validados', 'Mov. Productos', 220),
(21, 'editar-pedido', 'Editar Pedido', 'Mov. Productos', 230),
(22, 'cajas', 'Administrar Caja', 'Cajas', 300),
(23, 'cajas-cajero', 'Caja (Cajero)', 'Cajas', 310),
(24, 'cajas-cierre', 'Cierres de Caja', 'Cajas', 320),
(25, 'ventas', 'Adm. Ventas', 'Ventas', 400),
(26, 'presupuestos', 'Adm. Presupuestos', 'Ventas', 410),
(27, 'crear-venta-caja', 'Crear Venta', 'Ventas', 420),
(28, 'ventas-productos', 'Productos Vendidos', 'Ventas', 430),
(29, 'ventas-rentabilidad', 'Informe Rentabilidad', 'Ventas', 440),
(30, 'ventas-categoria-proveedor-informe', 'Informe de Ventas', 'Ventas', 450),
(31, 'presupuesto-venta', 'Presupuesto Venta', 'Ventas', 460),
(32, 'crear-presupuesto-caja', 'Crear Presupuesto', 'Ventas', 470),
(33, 'crear-presupuesto-caja2', 'Crear Presupuesto 2', 'Ventas', 480),
(34, 'crear-venta', 'Crear Venta (otro)', 'Ventas', 490),
(35, 'editar-venta', 'Editar Venta', 'Ventas', 500),
(36, 'crear-venta-caja-impresion', 'Crear Venta Impresión', 'Ventas', 510),
(37, 'libro-iva-ventas', 'Libro IVA Ventas', 'Ventas', 520),
(38, 'clientes', 'Clientes', 'Clientes', 600),
(39, 'clientes_cuenta', 'Clientes Cuenta', 'Clientes', 610),
(40, 'clientes-cuenta-saldos', 'Clientes Cuenta Saldos', 'Clientes', 620),
(41, 'clientes-cuenta-deuda', 'Clientes Cuenta Deuda', 'Clientes', 630),
(42, 'compras', 'Adm. Compras', 'Compras', 700),
(43, 'crear-compra', 'Crear Compra', 'Compras', 710),
(44, 'ingreso', 'Ingreso Mercadería', 'Compras', 720),
(45, 'editar-ingreso', 'Editar Ingreso', 'Compras', 730),
(46, 'proveedores', 'Proveedores', 'Proveedores', 800),
(47, 'proveedores_cuenta', 'Proveedores Cuenta', 'Proveedores', 810),
(48, 'proveedores-cuenta-saldos', 'Proveedores Cuenta Saldos', 'Proveedores', 820),
(49, 'proveedores-saldo', 'Proveedores Saldo', 'Proveedores', 830),
(50, 'proveedores-pagos', 'Proveedores Pagos', 'Proveedores', 840),
(51, 'integraciones', 'Gestionar Integraciones', 'Integraciones', 900),
(52, 'chat', 'Asistente Virtual', 'Integraciones', 910),
(53, 'reportes', 'Reportes', 'Reportes', 1000),
(54, 'parametros-facturacion', 'Parámetros Facturación', 'Configuración', 1100),
(55, 'factura-manual', 'Factura Manual', 'Configuración', 1110),
(56, 'procesar-pago', 'Procesar Pago', 'Configuración', 1120),
(57, 'info', 'Info', 'General', 15),
(58, 'impresionPreciosCuidados', 'Impr. Precios Cuidados', 'Productos', 135),
(59, 'impresionPreciosOfertas', 'Impr. Precios Ofertas', 'Productos', 136),
(60, 'impresionPreciosCuidadosGrande', 'Impr. Precios Cuidados Grande', 'Productos', 137),
(61, 'salir', 'Salir', 'General', 999);


-- ===================== PERMISOS ROL =====================

CREATE TABLE IF NOT EXISTS `permisos_rol` (
  `rol` varchar(64) NOT NULL,
  `id_pantalla` int(11) NOT NULL,
  PRIMARY KEY (`rol`,`id_pantalla`),
  KEY `fk_permisos_rol_pantalla` (`id_pantalla`),
  CONSTRAINT `fk_permisos_rol_pantalla` FOREIGN KEY (`id_pantalla`) REFERENCES `pantallas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Administrador: todas las pantallas activas
INSERT IGNORE INTO `permisos_rol` (`rol`, `id_pantalla`)
SELECT 'Administrador', id FROM `pantallas` WHERE activo = 1;

-- Vendedor: pantallas habituales del menú
INSERT IGNORE INTO `permisos_rol` (`rol`, `id_pantalla`)
SELECT 'Vendedor', id FROM `pantallas` WHERE codigo IN (
  'inicio', 'productos', 'impresion-precios', 'cajas-cajero', 'ventas', 'crear-venta-caja', 'clientes', 'chat'
) AND activo = 1;
