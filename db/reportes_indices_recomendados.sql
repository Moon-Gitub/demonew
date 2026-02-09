-- =============================================================================
-- ÍNDICES RECOMENDADOS PARA INFORMES EJECUTIVOS (Gestión de pedidos, Dashboard, etc.)
-- Ejecutar en la base de datos si las consultas son lentas.
-- Verificar que no existan ya: SHOW INDEX FROM ventas; SHOW INDEX FROM productos_venta;
-- =============================================================================

-- Ventas: filtrar por fecha y tipo de comprobante (todas las consultas de reportes)
-- Si ya existe un índice que empiece por fecha, no hace falta duplicar.
ALTER TABLE ventas ADD INDEX idx_ventas_fecha_cbte (fecha, cbte_tipo);

-- productos_venta: join ventas -> productos_venta por id_venta (consultas optimizadas)
ALTER TABLE productos_venta ADD INDEX idx_pv_id_venta (id_venta);
ALTER TABLE productos_venta ADD INDEX idx_pv_id_producto (id_producto);

-- Opcional: índice compuesto para agrupar por producto en reportes
-- ALTER TABLE productos_venta ADD INDEX idx_pv_venta_producto (id_venta, id_producto);

-- Cajas: saldo acumulado por fecha (Dashboard ejecutivo)
ALTER TABLE cajas ADD INDEX idx_cajas_fecha (fecha);
