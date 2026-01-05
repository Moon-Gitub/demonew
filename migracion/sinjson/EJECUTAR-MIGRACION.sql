-- ================================================================
-- EJECUTAR MIGRACIÓN DE VENTAS
-- ================================================================
-- Este script debe ejecutarse DESPUÉS de 00-SCRIPT-MAESTRO-COMPLETO.sql
-- Solo si la tabla ventas existe
-- ================================================================

-- Ejecutar migración
CALL migrar_ventas_pendientes_completo();

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo;

-- Verificar ventas pendientes
SELECT 
    COUNT(*) AS ventas_pendientes,
    'Ventas que aún necesitan migración' AS descripcion
FROM ventas v 
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1 
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);
