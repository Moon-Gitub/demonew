-- ================================================================
-- Tabla listas_precio: ABM de listas de precio por empresa
-- Base de precio + descuento opcional (porcentaje)
-- ================================================================
-- Ejecutar una sola vez. Si la tabla ya existe, no hace nada.
-- ================================================================

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

-- ================================================================
-- Datos iniciales (empresa 1). No falla si ya existen.
-- ================================================================

INSERT IGNORE INTO `listas_precio` (`id_empresa`, `codigo`, `nombre`, `base_precio`, `tipo_descuento`, `valor_descuento`, `orden`, `activo`) VALUES
(1, 'precio_venta', 'Precio Público', 'precio_venta', 'ninguno', 0.00, 10, 1),
(1, 'precio_compra', 'Precio Costo', 'precio_compra', 'ninguno', 0.00, 20, 1),
(1, 'trabajadores_valle_grande', 'Trabajadores Valle Grande', 'precio_venta', 'porcentaje', 15.00, 30, 1),
(1, 'empleados', 'Empleados', 'precio_venta', 'porcentaje', 20.00, 40, 1);
