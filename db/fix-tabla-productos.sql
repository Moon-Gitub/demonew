-- ================================================================
-- FIX TABLA PRODUCTOS - Corregir inconsistencias
-- ================================================================
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- Ejecutar en phpMyAdmin o MySQL
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Corregir producto con id = 0 (inválido para PRIMARY KEY)
-- ================================================================
-- Si existe un producto con id 0, asignarle el siguiente id disponible
-- y actualizar referencias en productos_historial
SET @max_id = (SELECT COALESCE(MAX(id), 0) FROM productos WHERE id > 0);
SET @nuevo_id = @max_id + 1;

-- Actualizar productos_historial que referencian id 0
UPDATE productos_historial SET id = @nuevo_id WHERE id = 0;

-- Actualizar productos_venta si existe referencia a id_producto 0
UPDATE productos_venta SET id_producto = @nuevo_id WHERE id_producto = 0;

-- Actualizar combos_productos si existe referencia a id_producto 0
UPDATE combos_productos SET id_producto = @nuevo_id WHERE id_producto = 0;

-- Actualizar el producto con id 0
UPDATE productos SET id = @nuevo_id WHERE id = 0;


-- 2. Corregir stock2 NULL → 0.00
-- ================================================================
UPDATE productos SET stock2 = 0.00 WHERE stock2 IS NULL;


-- 3. Corregir stock3 NULL → 0.00
-- ================================================================
UPDATE productos SET stock3 = 0.00 WHERE stock3 IS NULL;


-- 4. Corregir ventas NULL → 0
-- ================================================================
UPDATE productos SET ventas = 0 WHERE ventas IS NULL;


-- 5. Asegurar PRIMARY KEY y AUTO_INCREMENT en id
-- ================================================================
-- Si da error "Multiple primary key" o "Duplicate key", la tabla ya tiene PK - continuar
ALTER TABLE productos ADD PRIMARY KEY (id);

-- Asegurar que id sea AUTO_INCREMENT
ALTER TABLE productos MODIFY id int(11) NOT NULL AUTO_INCREMENT;


-- 6. Ajustar AUTO_INCREMENT al siguiente valor disponible
-- ================================================================
SET @siguiente = (SELECT COALESCE(MAX(id), 0) + 1 FROM productos);
SET @sql = CONCAT('ALTER TABLE productos AUTO_INCREMENT = ', @siguiente);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- VERIFICACIONES (ejecutar después para comprobar)
-- ================================================================
-- No debe haber productos con id 0:
-- SELECT * FROM productos WHERE id = 0;

-- No debe haber stock2 o stock3 NULL:
-- SELECT id, codigo, stock2, stock3 FROM productos WHERE stock2 IS NULL OR stock3 IS NULL;

-- No debe haber ventas NULL:
-- SELECT id, codigo, ventas FROM productos WHERE ventas IS NULL;
