-- ================================================================
-- ELIMINAR REGISTROS DUPLICADOS (mantiene el de menor id)
-- ================================================================
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- Mantiene 1 registro por cada codigo/documento/uuid duplicado
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. PRODUCTOS: eliminar duplicados por codigo (mantiene el de menor id)
-- ================================================================
DELETE p1 FROM productos p1
INNER JOIN productos p2 ON p1.codigo = p2.codigo AND p1.codigo != '' AND p1.id > p2.id;

-- 2. CLIENTES: eliminar duplicados por documento (mantiene el de menor id)
-- ================================================================
DELETE c1 FROM clientes c1
INNER JOIN clientes c2 ON c1.documento = c2.documento AND c1.documento != '' AND c1.id > c2.id;

-- 3. VENTAS: eliminar duplicados por uuid (mantiene el de menor id)
-- ================================================================
DELETE v1 FROM ventas v1
INNER JOIN ventas v2 ON v1.uuid = v2.uuid AND v1.uuid != '' AND v1.id > v2.id;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificar que no queden duplicados (debe devolver 0 filas)
-- SELECT codigo, COUNT(*) FROM productos WHERE codigo != '' GROUP BY codigo HAVING COUNT(*) > 1;
