-- ================================================================
-- Tablas para ABM de permisos por rol (qué pantallas ve cada rol)
-- ================================================================
-- Ejecutar una sola vez. Si las tablas ya existen, no hace nada.
-- ================================================================

CREATE TABLE IF NOT EXISTS `pantallas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(128) NOT NULL COMMENT 'Ruta del sistema (ej: crear-venta-caja)',
  `nombre` varchar(128) NOT NULL COMMENT 'Nombre para mostrar en el panel',
  `agrupacion` varchar(64) NOT NULL DEFAULT 'General' COMMENT 'Grupo en menú: Empresa, Ventas, etc.',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pantallas_codigo` (`codigo`),
  KEY `idx_pantallas_agrupacion_orden` (`agrupacion`,`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permisos_rol` (
  `rol` varchar(64) NOT NULL,
  `id_pantalla` int(11) NOT NULL,
  PRIMARY KEY (`rol`,`id_pantalla`),
  KEY `fk_permisos_rol_pantalla` (`id_pantalla`),
  CONSTRAINT `fk_permisos_rol_pantalla` FOREIGN KEY (`id_pantalla`) REFERENCES `pantallas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Datos iniciales: todas las pantallas del sistema
-- ================================================================

INSERT IGNORE INTO `pantallas` (`codigo`, `nombre`, `agrupacion`, `orden`) VALUES
('inicio', 'Inicio', 'General', 10),
('empresa', 'Datos Empresa', 'Empresa', 20),
('usuarios', 'Usuarios', 'Empresa', 30),
('listas-precio', 'Listas de Precio', 'Empresa', 40),
('balanzas-formatos', 'Formatos de Balanza', 'Empresa', 50),
('medios-pago', 'Cargar Medios de Pago', 'Empresa', 60),
('permisos-rol', 'Permisos por Rol', 'Empresa', 70),
('productos', 'Administrar Productos', 'Productos', 100),
('categorias', 'Categorías', 'Productos', 110),
('combos', 'Combos', 'Productos', 120),
('impresion-precios', 'Imprimir Precios', 'Productos', 130),
('productos-importar-excel', 'Importar Excel (v1)', 'Productos', 140),
('productos-importar-excel2', 'Importar Excel', 'Productos', 150),
('productos-stock-medio', 'Productos Stock Medio', 'Productos', 160),
('productos-stock-bajo', 'Productos Stock Bajo', 'Productos', 170),
('productos-stock-valorizado', 'Productos Stock Valorizado', 'Productos', 180),
('productos-historial', 'Productos Historial', 'Productos', 190),
('pedidos-generar-movimiento', 'Generar Movimiento', 'Mov. Productos', 200),
('pedidos-nuevos', 'Validar Movimiento', 'Mov. Productos', 210),
('pedidos-validados', 'Movimientos Validados', 'Mov. Productos', 220),
('editar-pedido', 'Editar Pedido', 'Mov. Productos', 230),
('cajas', 'Administrar Caja', 'Cajas', 300),
('cajas-cajero', 'Caja (Cajero)', 'Cajas', 310),
('cajas-cierre', 'Cierres de Caja', 'Cajas', 320),
('ventas', 'Adm. Ventas', 'Ventas', 400),
('presupuestos', 'Adm. Presupuestos', 'Ventas', 410),
('crear-venta-caja', 'Crear Venta', 'Ventas', 420),
('ventas-productos', 'Productos Vendidos', 'Ventas', 430),
('ventas-rentabilidad', 'Informe Rentabilidad', 'Ventas', 440),
('ventas-categoria-proveedor-informe', 'Informe de Ventas', 'Ventas', 450),
('presupuesto-venta', 'Presupuesto Venta', 'Ventas', 460),
('crear-presupuesto-caja', 'Crear Presupuesto', 'Ventas', 470),
('crear-presupuesto-caja2', 'Crear Presupuesto 2', 'Ventas', 480),
('crear-venta', 'Crear Venta (otro)', 'Ventas', 490),
('editar-venta', 'Editar Venta', 'Ventas', 500),
('crear-venta-caja-impresion', 'Crear Venta Impresión', 'Ventas', 510),
('libro-iva-ventas', 'Libro IVA Ventas', 'Ventas', 520),
('clientes', 'Clientes', 'Clientes', 600),
('clientes_cuenta', 'Clientes Cuenta', 'Clientes', 610),
('clientes-cuenta-saldos', 'Clientes Cuenta Saldos', 'Clientes', 620),
('clientes-cuenta-deuda', 'Clientes Cuenta Deuda', 'Clientes', 630),
('compras', 'Adm. Compras', 'Compras', 700),
('crear-compra', 'Crear Compra', 'Compras', 710),
('ingreso', 'Ingreso Mercadería', 'Compras', 720),
('editar-ingreso', 'Editar Ingreso', 'Compras', 730),
('proveedores', 'Proveedores', 'Proveedores', 800),
('proveedores_cuenta', 'Proveedores Cuenta', 'Proveedores', 810),
('proveedores-cuenta-saldos', 'Proveedores Cuenta Saldos', 'Proveedores', 820),
('proveedores-saldo', 'Proveedores Saldo', 'Proveedores', 830),
('proveedores-pagos', 'Proveedores Pagos', 'Proveedores', 840),
('integraciones', 'Gestionar Integraciones', 'Integraciones', 900),
('chat', 'Asistente Virtual', 'Integraciones', 910),
('reportes', 'Reportes', 'Reportes', 1000),
('parametros-facturacion', 'Parámetros Facturación', 'Configuración', 1100),
('factura-manual', 'Factura Manual', 'Configuración', 1110),
('procesar-pago', 'Procesar Pago', 'Configuración', 1120),
('info', 'Info', 'General', 15),
('impresionPreciosCuidados', 'Impr. Precios Cuidados', 'Productos', 135),
('impresionPreciosOfertas', 'Impr. Precios Ofertas', 'Productos', 136),
('impresionPreciosCuidadosGrande', 'Impr. Precios Cuidados Grande', 'Productos', 137),
('salir', 'Salir', 'General', 999);

-- ================================================================
-- Permisos iniciales: Administrador = todas; Vendedor = menú actual
-- ================================================================

-- Administrador: todas las pantallas activas
INSERT IGNORE INTO `permisos_rol` (`rol`, `id_pantalla`)
SELECT 'Administrador', id FROM `pantallas` WHERE activo = 1;

-- Vendedor: solo las que tiene hoy en el menú (+ inicio + permisos-rol no aplica para vendedor)
INSERT IGNORE INTO `permisos_rol` (`rol`, `id_pantalla`)
SELECT 'Vendedor', id FROM `pantallas` WHERE codigo IN (
  'inicio', 'productos', 'impresion-precios', 'cajas-cajero', 'ventas', 'crear-venta-caja', 'clientes', 'chat'
) AND activo = 1;
