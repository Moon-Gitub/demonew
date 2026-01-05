-- ================================================================
-- SCRIPT MAESTRO COMPLETO: Migración JSON a Tabla Relacional
-- ================================================================
-- Este script ejecuta TODO el proceso de migración en orden:
--   1. Crear tabla productos_venta
--   2. Crear índices y foreign keys
--   3. Migrar ventas pendientes
--   4. Verificar resultados
-- ================================================================
-- IMPORTANTE: Hacer backup antes de ejecutar
-- ================================================================
-- NOTA: Ejecutar desde línea de comandos o phpMyAdmin sección por sección
-- ================================================================

SET @inicio = NOW();

-- ================================================================
-- PASO 1: VERIFICAR PREREQUISITOS
-- ================================================================
SELECT '=== PASO 1: Verificando prerequisitos ===' AS paso;

-- Verificar que existe la tabla ventas
SET @existe_ventas = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'ventas'
);

SELECT 
    CASE 
        WHEN @existe_ventas > 0 THEN '✅ Tabla ventas existe'
        ELSE '❌ ERROR: Tabla ventas no existe'
    END AS estado_ventas;

-- Verificar que existe la tabla productos
SET @existe_productos = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'productos'
);

SELECT 
    CASE 
        WHEN @existe_productos > 0 THEN '✅ Tabla productos existe'
        ELSE '❌ ERROR: Tabla productos no existe'
    END AS estado_productos;

-- ================================================================
-- PASO 2: CREAR TABLA productos_venta
-- ================================================================
SELECT '=== PASO 2: Creando tabla productos_venta ===' AS paso;

CREATE TABLE IF NOT EXISTS `productos_venta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

SELECT '✅ Tabla productos_venta creada' AS resultado;

-- ================================================================
-- PASO 3: CREAR ÍNDICES
-- ================================================================
SELECT '=== PASO 3: Creando índices ===' AS paso;

-- Índice para búsquedas por venta
CREATE INDEX IF NOT EXISTS `idx_venta` ON `productos_venta` (`id_venta`);

-- Índice para búsquedas por producto
CREATE INDEX IF NOT EXISTS `idx_producto` ON `productos_venta` (`id_producto`);

-- Índice compuesto para búsquedas combinadas
CREATE INDEX IF NOT EXISTS `idx_venta_producto` ON `productos_venta` (`id_venta`, `id_producto`);

-- Índice para ordenamiento por fecha
CREATE INDEX IF NOT EXISTS `idx_created_at` ON `productos_venta` (`created_at`);

SELECT '✅ Índices creados' AS resultado;

-- ================================================================
-- PASO 4: CREAR FOREIGN KEYS
-- ================================================================
SELECT '=== PASO 4: Creando foreign keys ===' AS paso;

-- Guardar estado actual de FK checks
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;

-- Verificar y eliminar FK a ventas si existe
SET @fk_venta_name = NULL;
SELECT CONSTRAINT_NAME INTO @fk_venta_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
AND CONSTRAINT_NAME = 'fk_productos_venta_venta'
LIMIT 1;

SET @sql_drop_fk1 = IF(@fk_venta_name IS NOT NULL,
    CONCAT('ALTER TABLE productos_venta DROP FOREIGN KEY `', @fk_venta_name, '`'),
    'SELECT 1'
);

PREPARE stmt_drop1 FROM @sql_drop_fk1;
EXECUTE stmt_drop1;
DEALLOCATE PREPARE stmt_drop1;

-- Verificar y eliminar FK a productos si existe
SET @fk_producto_name = NULL;
SELECT CONSTRAINT_NAME INTO @fk_producto_name
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
AND CONSTRAINT_NAME = 'fk_productos_venta_producto'
LIMIT 1;

SET @sql_drop_fk2 = IF(@fk_producto_name IS NOT NULL,
    CONCAT('ALTER TABLE productos_venta DROP FOREIGN KEY ', QUOTE(@fk_producto_name)),
    'SELECT 1'
);

PREPARE stmt_drop2 FROM @sql_drop_fk2;
EXECUTE stmt_drop2;
DEALLOCATE PREPARE stmt_drop2;

-- Crear FK a ventas (solo si la tabla existe)
IF @existe_ventas > 0 THEN
    SET @sql_fk_venta = 'ALTER TABLE productos_venta ADD CONSTRAINT fk_productos_venta_venta FOREIGN KEY (id_venta) REFERENCES ventas (id) ON DELETE CASCADE ON UPDATE CASCADE';
    PREPARE stmt_fk_venta FROM @sql_fk_venta;
    EXECUTE stmt_fk_venta;
    DEALLOCATE PREPARE stmt_fk_venta;
END IF;

-- Crear FK a productos (solo si la tabla existe)
IF @existe_productos > 0 THEN
    SET @sql_fk_producto = 'ALTER TABLE productos_venta ADD CONSTRAINT fk_productos_venta_producto FOREIGN KEY (id_producto) REFERENCES productos (id) ON DELETE RESTRICT ON UPDATE CASCADE';
    PREPARE stmt_fk_producto FROM @sql_fk_producto;
    EXECUTE stmt_fk_producto;
    DEALLOCATE PREPARE stmt_fk_producto;
END IF;

-- Rehabilitar FK checks ANTES de ejecutar el procedimiento
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;

SELECT '✅ Foreign keys creadas' AS resultado;

-- ================================================================
-- PASO 5: CREAR Y EJECUTAR PROCEDIMIENTO DE MIGRACIÓN
-- ================================================================
SELECT '=== PASO 5: Migrando ventas pendientes ===' AS paso;

-- Eliminar procedimiento si existe
DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo;

-- Crear procedimiento (sin DELIMITER para compatibilidad)
DELIMITER $$

CREATE PROCEDURE migrar_ventas_pendientes_completo()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_venta INT;
    DECLARE v_productos_json TEXT;
    DECLARE v_producto_id INT;
    DECLARE v_cantidad DECIMAL(10,2);
    DECLARE v_precio_compra DECIMAL(10,2);
    DECLARE v_precio_venta DECIMAL(10,2);
    DECLARE i INT DEFAULT 0;
    DECLARE productos_count INT;
    DECLARE v_ventas_migradas INT DEFAULT 0;
    DECLARE v_productos_migrados INT DEFAULT 0;
    DECLARE v_productos_omitidos INT DEFAULT 0;
    
    DECLARE cur_ventas CURSOR FOR 
        SELECT v.id, v.productos 
        FROM ventas v
        WHERE v.productos IS NOT NULL 
        AND v.productos != '' 
        AND v.productos != '[]'
        AND JSON_VALID(v.productos) = 1
        AND NOT EXISTS (
            SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
        );
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    START TRANSACTION;
    
    OPEN cur_ventas;
    
    read_loop: LOOP
        FETCH cur_ventas INTO v_id_venta, v_productos_json;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SET productos_count = JSON_LENGTH(v_productos_json);
        SET i = 0;
        
        WHILE i < productos_count DO
            SET v_producto_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].id'))) AS UNSIGNED);
            SET v_cantidad = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].cantidad'))) AS DECIMAL(10,2));
            SET v_precio_compra = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_compra'))) AS DECIMAL(10,2));
            
            SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio'))) AS DECIMAL(10,2));
            IF v_precio_venta = 0 OR v_precio_venta IS NULL THEN
                SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_venta'))) AS DECIMAL(10,2));
            END IF;
            
            IF v_producto_id IS NOT NULL AND v_producto_id > 0 AND v_cantidad > 0 THEN
                IF EXISTS(SELECT 1 FROM productos WHERE id = v_producto_id) THEN
                    INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta)
                    VALUES (v_id_venta, v_producto_id, v_cantidad, 
                            IFNULL(v_precio_compra, 0), 
                            IFNULL(v_precio_venta, 0));
                    SET v_productos_migrados = v_productos_migrados + 1;
                ELSE
                    SET v_productos_omitidos = v_productos_omitidos + 1;
                END IF;
            END IF;
            
            SET i = i + 1;
        END WHILE;
        
        SET v_ventas_migradas = v_ventas_migradas + 1;
        
    END LOOP;
    
    CLOSE cur_ventas;
    
    COMMIT;
    
    -- Solo un SELECT al final para evitar problemas de sincronización
    SELECT 
        '=== RESUMEN DE MIGRACIÓN ===' AS titulo,
        v_ventas_migradas AS ventas_migradas,
        v_productos_migrados AS productos_migrados,
        v_productos_omitidos AS productos_omitidos;
    
END$$

DELIMITER ;

-- Ejecutar migración SOLO si la tabla ventas existe
-- IMPORTANTE: Ejecutar directamente, NO con PREPARE/EXECUTE
-- para evitar problemas de sincronización
SET @ejecutar_migracion = @existe_ventas;

-- ================================================================
-- PASO 6: VERIFICACIÓN FINAL
-- ================================================================
SELECT '=== PASO 6: Verificación final ===' AS paso;

-- 1. Total de registros en productos_venta
SELECT 
    COUNT(*) AS total_productos_venta,
    'Total de productos migrados' AS descripcion
FROM productos_venta;

-- 2. Ventas migradas
SELECT 
    COUNT(DISTINCT id_venta) AS ventas_migradas,
    'Ventas que tienen productos_venta' AS descripcion
FROM productos_venta;

-- 3. Verificar integridad de índices
SELECT 
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 4. Verificar foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ================================================================
-- RESUMEN FINAL
-- ================================================================
SELECT 
    '=== MIGRACIÓN COMPLETADA ===' AS titulo,
    TIMESTAMPDIFF(SECOND, @inicio, NOW()) AS segundos_transcurridos,
    NOW() AS fecha_finalizacion,
    CASE 
        WHEN @ejecutar_migracion > 0 THEN '✅ Ejecuta: CALL migrar_ventas_pendientes_completo();'
        ELSE '⚠️ Tabla ventas no existe, omitir migración'
    END AS siguiente_paso;
