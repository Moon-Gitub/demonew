-- ================================================================
-- OPTIMIZAR ÍNDICES PARA DASHBOARD
-- ================================================================
-- Índices adicionales para mejorar el rendimiento del dashboard
-- ================================================================

-- Índice compuesto para consultas de ventas por fecha y tipo
ALTER TABLE `ventas` 
ADD INDEX IF NOT EXISTS `idx_fecha_cbte_tipo` (`fecha`, `cbte_tipo`);

-- Índice para productos_venta con fecha de venta (para productos más vendidos)
ALTER TABLE `productos_venta` 
ADD INDEX IF NOT EXISTS `idx_venta_producto_cantidad` (`id_venta`, `id_producto`, `cantidad`);

-- Índice para consultas de productos más vendidos por fecha
-- (se usa a través del JOIN con ventas)
-- El índice idx_venta ya existe, pero podemos agregar uno específico para cantidad
ALTER TABLE `productos_venta` 
ADD INDEX IF NOT EXISTS `idx_producto_cantidad` (`id_producto`, `cantidad`);

-- ================================================================
-- VERIFICAR ÍNDICES EXISTENTES
-- ================================================================
-- Ejecutar para ver los índices actuales:
-- SHOW INDEX FROM ventas;
-- SHOW INDEX FROM productos_venta;
