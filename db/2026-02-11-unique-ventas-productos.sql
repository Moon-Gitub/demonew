-- =============================================================================
-- Unicidad en ventas y detalle de productos_venta
-- Ejecutar una sola vez en cada base que migres.
-- =============================================================================

-- 1) Asegurar índice UNIQUE sobre uuid en ventas (si no existe)
--    Omitir si ya lo creaste previamente.
ALTER TABLE ventas
ADD UNIQUE KEY uq_ventas_uuid (uuid);

-- 2) Evitar duplicados de productos por venta en la tabla productos_venta
--    Un solo registro por (id_venta, id_producto).
ALTER TABLE productos_venta
ADD UNIQUE KEY uq_venta_producto (id_venta, id_producto);

