-- ============================================
-- SISTEMA DE COMBOS DE PRODUCTOS
-- ============================================
-- Este script crea las tablas necesarias para el sistema de combos
-- Un combo es un producto compuesto por otros productos
-- Al vender un combo, se descuenta stock de todos los productos componentes

-- Tabla principal de combos
CREATE TABLE IF NOT EXISTS `combos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) NOT NULL COMMENT 'ID del producto combo (referencia a productos.id)',
  `codigo` varchar(255) NOT NULL COMMENT 'Código único del combo',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre del combo',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del combo',
  `precio_venta` decimal(11,2) DEFAULT 0.00 COMMENT 'Precio de venta del combo',
  `precio_venta_mayorista` decimal(11,2) DEFAULT NULL COMMENT 'Precio mayorista del combo',
  `tipo_iva` decimal(11,2) DEFAULT 21.00 COMMENT 'Tipo de IVA del combo',
  `imagen` text DEFAULT NULL COMMENT 'Ruta de la imagen del combo',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `tipo_descuento` enum('ninguno','global','por_producto','mixto') DEFAULT 'ninguno' COMMENT 'Tipo de descuento aplicable',
  `descuento_global` decimal(11,2) DEFAULT 0.00 COMMENT 'Descuento global en porcentaje o monto fijo',
  `aplicar_descuento_global` enum('porcentaje','monto_fijo') DEFAULT 'porcentaje' COMMENT 'Cómo aplicar el descuento global',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `nombre_usuario` varchar(50) DEFAULT NULL COMMENT 'Usuario que creó/modificó',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `id_producto` (`id_producto`),
  KEY `activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Tabla principal de combos de productos';

-- Tabla de relación entre combos y productos componentes
CREATE TABLE IF NOT EXISTS `combos_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_combo` int(11) NOT NULL COMMENT 'ID del combo',
  `id_producto` int(11) NOT NULL COMMENT 'ID del producto componente',
  `cantidad` decimal(11,2) NOT NULL DEFAULT 1.00 COMMENT 'Cantidad del producto en el combo',
  `precio_unitario` decimal(11,2) DEFAULT NULL COMMENT 'Precio unitario del producto en el combo (opcional, para override)',
  `descuento` decimal(11,2) DEFAULT 0.00 COMMENT 'Descuento específico para este producto en el combo',
  `aplicar_descuento` enum('porcentaje','monto_fijo') DEFAULT 'porcentaje' COMMENT 'Cómo aplicar el descuento',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `combo_producto` (`id_combo`,`id_producto`),
  KEY `id_combo` (`id_combo`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `fk_combos_productos_combo` FOREIGN KEY (`id_combo`) REFERENCES `combos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_combos_productos_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci COMMENT='Relación entre combos y productos componentes';

-- Agregar campo es_combo a la tabla productos (opcional, para identificar productos que son combos)
ALTER TABLE `productos` 
ADD COLUMN IF NOT EXISTS `es_combo` tinyint(1) DEFAULT 0 COMMENT '1=Es combo, 0=Producto normal' AFTER `cambio_desde`;

-- Índice para mejorar búsquedas
ALTER TABLE `productos` ADD INDEX IF NOT EXISTS `es_combo` (`es_combo`);

-- ============================================
-- NOTAS:
-- ============================================
-- 1. Un combo tiene un producto asociado (id_producto) que es el producto "combo" en sí
-- 2. Los productos componentes se almacenan en combos_productos
-- 3. Al vender un combo, se debe descontar stock de todos los productos en combos_productos
-- 4. Los descuentos pueden ser:
--    - Ninguno: Sin descuento
--    - Global: Descuento sobre el total del combo
--    - Por producto: Descuento individual sobre cada producto componente
--    - Mixto: Combinación de descuento global y por producto
-- 5. El campo es_combo en productos permite identificar rápidamente si un producto es combo
