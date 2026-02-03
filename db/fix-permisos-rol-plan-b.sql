-- ================================================================
-- PLAN B: Vacía pantallas y permisos_rol y vuelve a insertar con ids correctos.
-- No toca la FK (no hace DROP ni ADD), así no da error si la FK no existe o tiene otro nombre.
-- Ejecutar TODO este archivo en la base de datos (barbas_db).
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1) Vaciar tablas (primero la hija, luego la padre)
DELETE FROM `permisos_rol`;
DELETE FROM `pantallas`;

-- 2) Insertar pantallas con ids 1 a 61
INSERT INTO `pantallas` (`id`, `codigo`, `nombre`, `agrupacion`, `orden`) VALUES
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

-- 3) Permisos: Administrador = todas; Vendedor = menú habitual
INSERT INTO `permisos_rol` (`rol`, `id_pantalla`) SELECT 'Administrador', id FROM `pantallas` WHERE activo = 1;
INSERT INTO `permisos_rol` (`rol`, `id_pantalla`) SELECT 'Vendedor', id FROM `pantallas` WHERE codigo IN ('inicio', 'productos', 'impresion-precios', 'cajas-cajero', 'ventas', 'crear-venta-caja', 'clientes', 'chat') AND activo = 1;

-- 4) Contador para futuras filas
ALTER TABLE `pantallas` AUTO_INCREMENT = 62;

SET FOREIGN_KEY_CHECKS = 1;
