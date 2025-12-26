-- ================================================================
-- DIAGNÓSTICO: Productos inexistentes en ventas
-- ================================================================
-- Este script identifica productos en el JSON de ventas que no existen
-- en la tabla productos, para ayudar a diagnosticar el problema
-- ================================================================

-- 1. Encontrar productos inexistentes en todas las ventas
SELECT 
    v.id as id_venta,
    v.codigo as codigo_venta,
    v.fecha,
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id'))) as id_producto_inexistente,
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].descripcion'))) as descripcion_producto,
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].cantidad'))) as cantidad
FROM ventas v
CROSS JOIN (
    SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) n
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id')) IS NOT NULL
AND CAST(JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id'))) AS UNSIGNED) NOT IN (SELECT id FROM productos)
ORDER BY v.id, n.n;

-- 2. Resumen de productos inexistentes
SELECT 
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id'))) as id_producto_inexistente,
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].descripcion'))) as descripcion,
    COUNT(*) as veces_usado,
    SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].cantidad'))) AS DECIMAL(10,2))) as total_cantidad
FROM ventas v
CROSS JOIN (
    SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) n
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id')) IS NOT NULL
AND CAST(JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id'))) AS UNSIGNED) NOT IN (SELECT id FROM productos)
GROUP BY id_producto_inexistente, descripcion
ORDER BY veces_usado DESC;

-- 3. Ventas afectadas (con productos inexistentes)
SELECT DISTINCT
    v.id as id_venta,
    v.codigo as codigo_venta,
    v.fecha,
    COUNT(DISTINCT JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id')))) as productos_inexistentes
FROM ventas v
CROSS JOIN (
    SELECT 0 as n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION 
    SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION
    SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION
    SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION
    SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION
    SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) n
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id')) IS NOT NULL
AND CAST(JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', n.n, '].id'))) AS UNSIGNED) NOT IN (SELECT id FROM productos)
GROUP BY v.id, v.codigo, v.fecha
ORDER BY productos_inexistentes DESC;
