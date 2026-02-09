-- =============================================================================
-- CONSULTAS PARA INFORMES EJECUTIVOS - SISTEMA POS MOON
-- Esquema real: ventas, productos_venta, cajas, clientes, productos, etc.
-- Exclusión ventas válidas: cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
-- Cajas: tipo = 1 ingresos, tipo = 0 egresos
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1. DASHBOARD EJECUTIVO DIARIO - Resumen del día
-- -----------------------------------------------------------------------------
-- Ventas totales del día, cantidad transacciones, ticket promedio, clientes atendidos
SELECT
  DATE(v.fecha) AS fecha,
  COALESCE(SUM(v.total), 0) AS ventas_totales,
  COUNT(*) AS cantidad_transacciones,
  COALESCE(AVG(v.total), 0) AS ticket_promedio,
  COUNT(DISTINCT v.id_cliente) AS clientes_atendidos
FROM ventas v
WHERE DATE(v.fecha) = :fecha
  AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY DATE(v.fecha);

-- Ventas día anterior (para comparativa)
SELECT COALESCE(SUM(total), 0) AS total
FROM ventas
WHERE DATE(fecha) = :fecha_ayer
  AND cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999);

-- Top 10 productos más vendidos del día
SELECT
  p.descripcion AS nombre,
  SUM(pv.cantidad) AS cantidad_vendida,
  SUM(pv.cantidad * pv.precio_venta) AS monto_total
FROM productos_venta pv
INNER JOIN productos p ON pv.id_producto = p.id
INNER JOIN ventas v ON pv.id_venta = v.id
WHERE DATE(v.fecha) = :fecha
  AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY p.id, p.descripcion
ORDER BY cantidad_vendida DESC
LIMIT 10;

-- Distribución por medio de pago (ventas.metodo_pago es texto)
SELECT
  v.metodo_pago AS nombre,
  COUNT(*) AS cantidad,
  COALESCE(SUM(v.total), 0) AS monto_total
FROM ventas v
WHERE DATE(v.fecha) = :fecha
  AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY v.metodo_pago
ORDER BY monto_total DESC;

-- Saldo caja al cierre del día (ingresos tipo=1 menos egresos tipo=0)
SELECT
  COALESCE(SUM(CASE WHEN tipo = 1 THEN monto ELSE 0 END), 0) -
  COALESCE(SUM(CASE WHEN tipo = 0 THEN monto ELSE 0 END), 0) AS saldo_caja
FROM cajas
WHERE DATE(fecha) <= :fecha;

-- -----------------------------------------------------------------------------
-- 2. RENTABILIDAD POR PRODUCTO (resumen por producto en período)
-- -----------------------------------------------------------------------------
SELECT
  p.id,
  p.codigo,
  p.descripcion,
  p.precio_compra,
  p.precio_venta,
  c.categoria,
  pr.nombre AS proveedor,
  COALESCE(SUM(pv.cantidad), 0) AS unidades_vendidas,
  COALESCE(SUM(pv.cantidad * pv.precio_venta), 0) AS total_venta,
  COALESCE(SUM(pv.cantidad * pv.precio_compra), 0) AS total_costo,
  COALESCE(SUM(pv.cantidad * (pv.precio_venta - pv.precio_compra)), 0) AS margen_total
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
LEFT JOIN productos_venta pv ON p.id = pv.id_producto
LEFT JOIN ventas v ON pv.id_venta = v.id AND v.fecha BETWEEN :fecha_desde AND :fecha_hasta
  AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY p.id, p.codigo, p.descripcion, p.precio_compra, p.precio_venta, c.categoria, pr.nombre;

-- -----------------------------------------------------------------------------
-- 3. CONTROL INVENTARIO - Productos bajo stock mínimo
-- -----------------------------------------------------------------------------
SELECT
  p.codigo,
  p.descripcion,
  COALESCE(p.stock, 0) AS stock_actual,
  COALESCE(p.stock_minimo, 0) AS stock_minimo,
  p.precio_compra,
  (COALESCE(p.stock, 0) * COALESCE(p.precio_compra, 0)) AS valorizado,
  c.categoria,
  pr.nombre AS proveedor
FROM productos p
LEFT JOIN categorias c ON p.id_categoria = c.id
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id
WHERE p.activo = 1
  AND (COALESCE(p.stock, 0) < COALESCE(p.stock_minimo, 0) OR COALESCE(p.stock, 0) <= 0)
ORDER BY p.stock ASC;

-- -----------------------------------------------------------------------------
-- 4. ANÁLISIS VENTAS PERIÓDICO - Por mes
-- -----------------------------------------------------------------------------
SELECT
  DATE_FORMAT(v.fecha, '%Y-%m') AS periodo,
  COALESCE(SUM(v.total), 0) AS ventas_totales,
  COUNT(*) AS cantidad_transacciones,
  COALESCE(AVG(v.total), 0) AS ticket_promedio
FROM ventas v
WHERE v.fecha BETWEEN :fecha_desde AND :fecha_hasta
  AND v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
GROUP BY DATE_FORMAT(v.fecha, '%Y-%m')
ORDER BY periodo ASC;

-- -----------------------------------------------------------------------------
-- 5. FLUJO DE CAJA - Resumen diario
-- -----------------------------------------------------------------------------
SELECT
  DATE(c.fecha) AS fecha,
  COALESCE(SUM(CASE WHEN c.tipo = 1 THEN c.monto ELSE 0 END), 0) AS ingresos,
  COALESCE(SUM(CASE WHEN c.tipo = 0 THEN c.monto ELSE 0 END), 0) AS egresos
FROM cajas c
WHERE c.fecha BETWEEN :fecha_desde AND :fecha_hasta
GROUP BY DATE(c.fecha)
ORDER BY fecha ASC;

-- -----------------------------------------------------------------------------
-- 6. GESTIÓN INTELIGENTE DE PEDIDOS - Ver modelo reporte-gestion-pedidos.modelo.php
-- Productos con ventas: stock, stock_bajo, ventas 7/30 días; días cobertura y cantidad
-- sugerida se calculan en PHP (promedio_venta_diaria, cantidad_sugerida, ROI).
-- -----------------------------------------------------------------------------
