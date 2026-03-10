-- ================================================================
-- FIX: Error #1062 - Duplicate entry '0' for key 'PRIMARY'
-- ================================================================
-- Corrige registros con id=0 en productos_venta y resetea AUTO_INCREMENT
-- Ejecutar ANTES de volver a correr migrar_ventas_pendientes_completo()
-- ================================================================

SELECT '=== Corrigiendo id=0 en productos_venta ===' AS paso;

-- 1. Ver cuántos registros tienen id=0
SELECT 
    COUNT(*) AS registros_con_id_cero,
    CASE WHEN COUNT(*) > 0 THEN '⚠️ Se corregirán' ELSE '✅ No hay id=0' END AS accion
FROM productos_venta 
WHERE id = 0;

-- 2. Inicializar variable e actualizar id=0 con valores únicos
SET @max_id = COALESCE((SELECT MAX(id) FROM productos_venta WHERE id > 0), 0);

UPDATE productos_venta 
CROSS JOIN (SELECT @rn := @max_id) init
SET id = @rn := @rn + 1
WHERE id = 0
ORDER BY id_venta, id_producto, COALESCE(created_at, '1970-01-01');

-- 3. Resetear AUTO_INCREMENT al siguiente valor libre
SET @next_id = COALESCE((SELECT MAX(id) FROM productos_venta), 0) + 1;
SET @sql = CONCAT('ALTER TABLE productos_venta AUTO_INCREMENT = ', @next_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT '✅ Corrección aplicada. Podés ejecutar: CALL migrar_ventas_pendientes_completo();' AS siguiente_paso;
