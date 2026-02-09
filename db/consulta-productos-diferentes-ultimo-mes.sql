-- Cantidad de productos DIFERENTES vendidos en el último mes
-- Para que coincida con el informe "Ventas por producto" (ruta ventas-productos):
--   - Elegir en el informe el rango "Último mes" (mismo período).
--   - El informe cuenta cada LÍNEA de venta (una fila por ítem); aquí contamos productos DISTINTOS (mismo producto en varias ventas = 1).
--   - Si el informe no filtra por tipo de comprobante, usar la "Versión B".

-- =============================================================================
-- Versión A: Solo ventas (excluye notas de crédito 3, 8, 13, 203, 208, 213, 999)
-- Mes calendario anterior, con hora (igual que el informe: desde 00:00 hasta 23:59)
-- =============================================================================
SELECT COUNT(DISTINCT pv.id_producto) AS cantidad_productos_diferentes
FROM productos_venta pv
INNER JOIN ventas v ON pv.id_venta = v.id
WHERE v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
  AND v.fecha >= CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), ' 00:00:00')
  AND v.fecha <= CONCAT(LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)), ' 23:59:59');

-- =============================================================================
-- Versión B: Misma lógica que el informe "Ventas por producto" (NO excluye cbte_tipo)
-- Use esta si en el informe no se filtran tipos de comprobante.
-- =============================================================================
-- SELECT COUNT(DISTINCT pv.id_producto) AS cantidad_productos_diferentes
-- FROM productos_venta pv
-- INNER JOIN ventas v ON pv.id_venta = v.id
-- WHERE v.fecha >= CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), ' 00:00:00')
--   AND v.fecha <= CONCAT(LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)), ' 23:59:59');

-- =============================================================================
-- Últimos 30 días (rolling) - por si el informe usa "Últimos 30 días"
-- =============================================================================
-- SELECT COUNT(DISTINCT pv.id_producto) AS cantidad_productos_diferentes
-- FROM productos_venta pv
-- INNER JOIN ventas v ON pv.id_venta = v.id
-- WHERE v.cbte_tipo NOT IN (3, 8, 13, 203, 208, 213, 999)
--   AND v.fecha >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH);
