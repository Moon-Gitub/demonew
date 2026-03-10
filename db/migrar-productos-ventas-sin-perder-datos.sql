-- ================================================================
-- MIGRACIÓN: productos (stock/deposito → stock2/stock3) y ventas (sucursal)
-- ================================================================
-- Objetivo: Dejar la estructura igual al DESTINO sin borrar ningún dato.
--
-- PRODUCTOS:
--   - deposito → se RENOMBRA a stock2 (los datos se preservan)
--   - stock3 → se AGREGA (nuevo, valor 0 por defecto)
--
-- VENTAS:
--   - sucursal → se AGREGA (default 'stock')
--
-- ⚠️  IMPORTANTE: Hacer backup antes de ejecutar
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ================================================================
-- TABLA: productos
-- ================================================================

-- Caso 1: Existe deposito y NO existe stock2 → RENOMBRAR (preserva datos)
SET @tiene_deposito = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'deposito');
SET @tiene_stock2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'stock2');

-- Si hay deposito y no hay stock2: renombrar deposito → stock2
SET @sql = IF(@tiene_deposito = 1 AND @tiene_stock2 = 0,
  'ALTER TABLE `productos` CHANGE COLUMN `deposito` `stock2` DECIMAL(11,2) DEFAULT 0.00',
  'SELECT ''productos: deposito→stock2 ya aplicado o no aplicable'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Caso 2: Existen AMBOS deposito y stock2 (ej. script anterior mal ejecutado)
-- Copiar datos de deposito a stock2 donde stock2 sea 0, luego eliminar deposito
SET @tiene_deposito = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'deposito');
SET @tiene_stock2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'stock2');

SET @sql = IF(@tiene_deposito = 1 AND @tiene_stock2 = 1,
  'UPDATE `productos` SET stock2 = COALESCE(NULLIF(stock2, 0), deposito) WHERE deposito IS NOT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(@tiene_deposito = 1 AND @tiene_stock2 = 1,
  'ALTER TABLE `productos` DROP COLUMN `deposito`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Caso 3: No existe stock2 (ni deposito para renombrar) → agregar stock2
SET @tiene_stock2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'stock2');
SET @sql = IF(@tiene_stock2 = 0,
  'ALTER TABLE `productos` ADD COLUMN `stock2` DECIMAL(11,2) DEFAULT 0.00 AFTER `stock`',
  'SELECT ''Columna stock2 ya existe en productos'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar stock3 (solo si no existe)
SET @tiene_stock3 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'productos' AND COLUMN_NAME = 'stock3');
SET @sql = IF(@tiene_stock3 = 0,
  'ALTER TABLE `productos` ADD COLUMN `stock3` DECIMAL(11,2) DEFAULT 0.00 AFTER `stock2`',
  'SELECT ''Columna stock3 ya existe en productos'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- TABLA: ventas
-- ================================================================

SET @tiene_sucursal = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ventas' AND COLUMN_NAME = 'sucursal');
SET @sql = IF(@tiene_sucursal = 0,
  'ALTER TABLE `ventas` ADD COLUMN `sucursal` VARCHAR(50) DEFAULT ''stock'' AFTER `productos`',
  'SELECT ''Columna sucursal ya existe en ventas'' AS mensaje'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- RESUMEN
-- ================================================================
-- ✅ productos: deposito → stock2 (renombrado, datos preservados)
-- ✅ productos: stock3 agregado
-- ✅ ventas: sucursal agregada
-- ✅ Sin pérdida de datos
-- ================================================================
