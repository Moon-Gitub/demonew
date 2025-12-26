-- ================================================================
-- CREAR TABLA productos_venta
-- ================================================================
-- Esta tabla almacena los productos de cada venta de forma relacional
-- en lugar de usar JSON en el campo productos de la tabla ventas
-- ================================================================

CREATE TABLE IF NOT EXISTS `productos_venta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_venta` (`id_venta`),
  INDEX `idx_producto` (`id_producto`),
  INDEX `idx_venta_producto` (`id_venta`, `id_producto`),
  CONSTRAINT `fk_productos_venta_venta` 
    FOREIGN KEY (`id_venta`) 
    REFERENCES `ventas` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_venta_producto` 
    FOREIGN KEY (`id_producto`) 
    REFERENCES `productos` (`id`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- ================================================================
-- OPCIONAL: Deshabilitar temporalmente la FK para migración
-- ================================================================
-- Si necesitas migrar datos con productos inexistentes, puedes
-- deshabilitar temporalmente la restricción:
-- 
-- SET FOREIGN_KEY_CHECKS = 0;
-- -- Ejecutar migración aquí
-- SET FOREIGN_KEY_CHECKS = 1;
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- ================================================================
-- COMENTARIOS
-- ================================================================
-- id: ID único del registro
-- id_venta: ID de la venta (FK a ventas.id)
-- id_producto: ID del producto vendido (FK a productos.id)
-- cantidad: Cantidad vendida
-- precio_compra: Precio de compra al momento de la venta (histórico)
-- precio_venta: Precio de venta al momento de la venta (histórico)
-- created_at: Fecha de creación del registro
-- 
-- Índices:
-- - idx_venta: Para búsquedas rápidas por venta
-- - idx_producto: Para búsquedas rápidas por producto
-- - idx_venta_producto: Para búsquedas combinadas
-- 
-- Foreign Keys:
-- - fk_productos_venta_venta: Si se elimina una venta, se eliminan sus productos
-- - fk_productos_venta_producto: Si se elimina un producto, no se pueden eliminar ventas históricas
