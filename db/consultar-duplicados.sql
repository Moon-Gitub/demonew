-- ================================================================
-- CONSULTAR REGISTROS DUPLICADOS
-- ================================================================
-- Ejecutar en phpMyAdmin o MySQL
-- ================================================================

-- 1. PRODUCTOS: códigos duplicados
-- ================================================================
SELECT 
  codigo,
  COUNT(*) AS cantidad,
  GROUP_CONCAT(id ORDER BY id) AS ids,
  GROUP_CONCAT(descripcion ORDER BY id SEPARATOR ' | ') AS descripciones
FROM productos
WHERE codigo IS NOT NULL AND codigo != ''
GROUP BY codigo
HAVING COUNT(*) > 1
ORDER BY cantidad DESC;

-- 2. CLIENTES: documentos duplicados
-- ================================================================
SELECT 
  documento,
  COUNT(*) AS cantidad,
  GROUP_CONCAT(id ORDER BY id) AS ids,
  GROUP_CONCAT(nombre ORDER BY id SEPARATOR ' | ') AS nombres
FROM clientes
WHERE documento IS NOT NULL AND documento != ''
GROUP BY documento
HAVING COUNT(*) > 1
ORDER BY cantidad DESC;

-- 3. VENTAS: uuid duplicados (no debería haber)
-- ================================================================
SELECT 
  uuid,
  COUNT(*) AS cantidad,
  GROUP_CONCAT(id ORDER BY id) AS ids
FROM ventas
WHERE uuid IS NOT NULL AND uuid != ''
GROUP BY uuid
HAVING COUNT(*) > 1;
