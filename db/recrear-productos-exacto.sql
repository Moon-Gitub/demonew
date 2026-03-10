-- ================================================================
-- RECREAR TABLA PRODUCTOS - Estructura exacta del dump kioscoelfacu
-- ================================================================
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- Este script elimina la tabla productos y la recrea con la estructura
-- y datos exactos del dump proporcionado (orden de columnas, tipos, etc.)
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar triggers primero (dependen de la tabla)
DROP TRIGGER IF EXISTS `prod_eliminar`;
DROP TRIGGER IF EXISTS `prod_insertar`;
DROP TRIGGER IF EXISTS `prod_modificar`;

-- Eliminar vista si existe (referencia productos)
DROP VIEW IF EXISTS `productos_cambios`;

-- Eliminar tabla productos (CASCADE si hay FKs)
DROP TABLE IF EXISTS `productos`;

-- --------------------------------------------------------
-- Estructura EXACTA de tabla `productos` (del dump)
-- Orden de columnas: id, id_categoria, codigo, id_proveedor, descripcion, 
-- imagen, stock, stock2, stock3, stock_medio, stock_bajo, precio_compra, 
-- precio_compra_dolar, margen_ganancia, precio_venta_neto, tipo_iva, 
-- precio_venta, precio_venta_mayorista, ventas, fecha, nombre_usuario, 
-- cambio_desde, es_combo, activo
-- --------------------------------------------------------

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `codigo` varchar(255) NOT NULL DEFAULT '',
  `id_proveedor` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL DEFAULT '',
  `imagen` text DEFAULT NULL,
  `stock` decimal(11,2) DEFAULT 0.00,
  `stock2` decimal(11,2) DEFAULT 0.00,
  `stock3` decimal(11,2) DEFAULT 0.00,
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
  `es_combo` tinyint(1) DEFAULT 0 COMMENT '1=Es combo, 0=Producto normal',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

--
-- Volcado de datos para la tabla `productos` (orden exacto del dump)
--

INSERT INTO `productos` (`id`, `id_categoria`, `codigo`, `id_proveedor`, `descripcion`, `imagen`, `stock`, `stock2`, `stock3`, `stock_medio`, `stock_bajo`, `precio_compra`, `precio_compra_dolar`, `margen_ganancia`, `precio_venta_neto`, `tipo_iva`, `precio_venta`, `precio_venta_mayorista`, `ventas`, `fecha`, `nombre_usuario`, `cambio_desde`, `es_combo`, `activo`) VALUES
(2, 1, '2', 1, 'Varios2', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2025-12-25 15:02:53', 'Moon Desarrollos', 'Administrar Productos', 0, 1),
(3, 1, '3', 1, 'Varios3', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10.50, 0.00, 0.00, 0, '2025-12-18 18:02:25', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(4, 1, '4', 1, 'Varios4', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2025-12-18 18:02:25', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(5, 1, '5', 1, 'Varios5', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2025-12-18 18:02:25', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(6, 1, '6', 1, 'Varios6', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 19000.00, 0.00, 0, '2026-03-10 03:47:15', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(7, 1, '7', 1, 'Varios7', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2026-03-10 03:46:57', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(8, 1, '8', 1, 'Varios8', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2025-12-18 18:02:25', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(9, 1, '9', 1, 'Varios9', 'vistas/img/productos/default/anonymous.png', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 21.00, 0.00, 0.00, 0, '2025-12-18 18:02:25', 'Moon Desarrollos', 'Administrar Categorias', 0, 1),
(1000, 1, 'PRUEBA001', 1000, 'Producto Prueba 1 - Arroz', NULL, 100.00, 99.00, 0.00, 80.00, 40.00, 500.00, 0.00, 50.00, 750.00, 21.00, 907.50, 850.00, 0, '2026-03-10 03:52:17', 'Claudio', 'Crear venta (6)', 0, 1),
(1001, 1, 'PRUEBA002', 1000, 'Producto Prueba 2 - Fideos', NULL, 120.00, 120.00, 0.00, 90.00, 45.00, 350.00, 0.00, 55.00, 542.50, 21.00, 656.43, 600.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1002, 1, 'PRUEBA003', 1001, 'Producto Prueba 3 - Aceite', NULL, 80.00, 80.00, 0.00, 60.00, 30.00, 600.00, 0.00, 60.00, 960.00, 21.00, 1161.60, 1050.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1003, 2, 'PRUEBA004', 1001, 'Producto Prueba 4 - Agua', NULL, 200.00, 200.00, 0.00, 150.00, 75.00, 180.00, 0.00, 40.00, 252.00, 21.00, 304.92, 280.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1004, 2, 'PRUEBA005', 1002, 'Producto Prueba 5 - Gaseosa', NULL, 150.00, 150.00, 0.00, 100.00, 50.00, 320.00, 0.00, 50.00, 480.00, 21.00, 580.80, 540.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1005, 2, 'PRUEBA006', 1002, 'Producto Prueba 6 - Jugo', NULL, 90.00, 90.00, 0.00, 60.00, 30.00, 250.00, 0.00, 45.00, 362.50, 21.00, 438.63, 400.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1006, 3, 'PRUEBA007', 1003, 'Producto Prueba 7 - Leche', NULL, 180.00, 180.00, 0.00, 120.00, 60.00, 160.00, 0.00, 35.00, 216.00, 10.50, 238.68, 220.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1007, 3, 'PRUEBA008', 1003, 'Producto Prueba 8 - Yogur', NULL, 110.00, 110.00, 0.00, 80.00, 40.00, 280.00, 0.00, 50.00, 420.00, 21.00, 508.20, 470.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1008, 3, 'PRUEBA009', 1004, 'Producto Prueba 9 - Queso', NULL, 70.00, 70.00, 0.00, 50.00, 25.00, 800.00, 0.00, 55.00, 1240.00, 21.00, 1500.40, 1380.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1009, 4, 'PRUEBA010', 1004, 'Producto Prueba 10 - Carne', NULL, 45.00, 44.00, 0.00, 30.00, 15.00, 2400.00, 0.00, 40.00, 3360.00, 21.00, 4065.60, 3750.00, 0, '2026-03-10 03:53:09', 'Claudio', 'Crear venta (7)', 0, 1),
(1010, 4, 'PRUEBA011', 1005, 'Producto Prueba 11 - Pollo', NULL, 35.00, 33.00, 0.00, 25.00, 12.00, 1700.00, 0.00, 45.00, 2465.00, 21.00, 2982.65, 2750.00, 0, '2026-03-10 03:56:04', 'Claudio', 'Crear venta (8)', 0, 1),
(1011, 5, 'PRUEBA012', 1005, 'Producto Prueba 12 - Tomate', NULL, 160.00, 160.00, 0.00, 120.00, 60.00, 280.00, 0.00, 50.00, 420.00, 0.00, 420.00, 390.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1012, 5, 'PRUEBA013', 1006, 'Producto Prueba 13 - Cebolla', NULL, 140.00, 120.00, 0.00, 100.00, 50.00, 220.00, 0.00, 48.00, 325.60, 0.00, 325.60, 300.00, 0, '2026-03-10 03:57:05', 'Claudio', 'Ajuste de Stock', 0, 1),
(1013, 6, 'PRUEBA014', 1006, 'Producto Prueba 14 - Detergente', NULL, 85.00, 85.00, 0.00, 60.00, 30.00, 400.00, 0.00, 52.00, 608.00, 21.00, 735.68, 680.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1014, 6, 'PRUEBA015', 1007, 'Producto Prueba 15 - Lavandina', NULL, 95.00, 93.00, 0.00, 65.00, 32.00, 260.00, 0.00, 50.00, 390.00, 21.00, 471.90, 440.00, 0, '2026-03-10 03:56:04', 'Claudio', 'Crear venta (8)', 0, 1),
(1015, 7, 'PRUEBA016', 1007, 'Producto Prueba 16 - Pan', NULL, 100.00, 100.00, 0.00, 70.00, 35.00, 350.00, 0.00, 45.00, 507.50, 10.50, 560.79, 520.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1016, 7, 'PRUEBA017', 1008, 'Producto Prueba 17 - Facturas', NULL, 65.00, 65.00, 0.00, 45.00, 22.00, 420.00, 0.00, 50.00, 630.00, 21.00, 762.30, 700.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1017, 8, 'PRUEBA018', 1008, 'Producto Prueba 18 - Chocolate', NULL, 170.00, 170.00, 0.00, 120.00, 60.00, 165.00, 0.00, 60.00, 264.00, 21.00, 319.44, 295.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1018, 8, 'PRUEBA019', 1009, 'Producto Prueba 19 - Caramelos', NULL, 130.00, 130.00, 0.00, 90.00, 45.00, 140.00, 0.00, 55.00, 217.00, 21.00, 262.57, 240.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1),
(1019, 8, 'PRUEBA020', 1009, 'Producto Prueba 20 - Galletas', NULL, 155.00, 155.00, 0.00, 110.00, 55.00, 300.00, 0.00, 50.00, 450.00, 21.00, 544.50, 500.00, 0, '2026-03-10 03:48:04', 'sistema', 'sistema', 0, 1);

--
-- Índices
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `es_combo` (`es_combo`);

--
-- AUTO_INCREMENT
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1020;

--
-- Disparadores `productos`
--
DELIMITER $$
CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id
$$
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id
$$
CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW IF NEW.stock <> OLD.stock || 
NEW.precio_compra <> OLD.precio_compra || 
NEW.precio_venta <> OLD.precio_venta ||
NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
END IF
$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- NOTA: Si usas la vista productos_cambios, recréala con:
-- db/crear-vista-productos-cambios-sin-definer.sql
-- ================================================================
