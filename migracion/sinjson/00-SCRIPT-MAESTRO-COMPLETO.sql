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

SET @inicio = NOW();
SET @mensajes = '';

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
    END AS estado;

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
    END AS estado;

-- Contar ventas con productos en JSON (solo si la tabla existe)
-- Usar consulta preparada para evitar error si la tabla no existe
SET @sql_ventas = IF(@existe_ventas > 0,
    'SELECT COUNT(*) as ventas_con_json, ''Ventas que tienen productos en formato JSON'' as descripcion FROM ventas WHERE productos IS NOT NULL AND productos != '''' AND productos != ''[]'' AND JSON_VALID(productos) = 1',
    'SELECT 0 as ventas_con_json, ''Tabla ventas no existe'' as descripcion'
);

PREPARE stmt FROM @sql_ventas;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ================================================================
-- PASO 2: CREAR TABLA productos_venta
-- ================================================================
SELECT '=== PASO 2: Creando tabla productos_venta ===' AS paso;

-- Eliminar tabla si existe (CUIDADO: solo si quieres empezar de cero)
-- DROP TABLE IF EXISTS productos_venta;

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

-- Deshabilitar temporalmente FK checks para evitar problemas durante migración
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar FK si existen (para recrearlas)
ALTER TABLE `productos_venta` 
DROP FOREIGN KEY IF EXISTS `fk_productos_venta_venta`;

ALTER TABLE `productos_venta` 
DROP FOREIGN KEY IF EXISTS `fk_productos_venta_producto`;

-- Crear FK a ventas
ALTER TABLE `productos_venta`
ADD CONSTRAINT `fk_productos_venta_venta` 
FOREIGN KEY (`id_venta`) 
REFERENCES `ventas` (`id`) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- Crear FK a productos
ALTER TABLE `productos_venta`
ADD CONSTRAINT `fk_productos_venta_producto` 
FOREIGN KEY (`id_producto`) 
REFERENCES `productos` (`id`) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

-- Rehabilitar FK checks
SET FOREIGN_KEY_CHECKS = 1;

SELECT '✅ Foreign keys creadas' AS resultado;

-- ================================================================
-- PASO 5: DIAGNOSTICAR PRODUCTOS INEXISTENTES (Opcional)
-- ================================================================
SELECT '=== PASO 5: Diagnosticando productos inexistentes ===' AS paso;

-- Solo diagnosticar si las tablas existen
-- Nota: Este paso es opcional y puede omitirse si hay problemas
SELECT '⚠️ Diagnóstico de productos inexistentes omitido (ejecutar manualmente si es necesario)' AS nota;


-- ================================================================
-- PASO 6: MIGRAR VENTAS PENDIENTES
-- ================================================================
SELECT '=== PASO 6: Migrando ventas pendientes ===' AS paso;

-- Contar ventas pendientes antes de migrar (solo si la tabla existe)
SET @sql_pendientes = IF(@existe_ventas > 0,
    CONCAT('SELECT COUNT(*) as ventas_pendientes_antes, ''Ventas que necesitan migración'' as descripcion FROM ventas v WHERE v.productos IS NOT NULL AND v.productos != '''' AND v.productos != ''[]'' AND JSON_VALID(v.productos) = 1 AND NOT EXISTS (SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id)'),
    'SELECT 0 as ventas_pendientes_antes, ''Tabla ventas no existe'' as descripcion'
);

PREPARE stmt_pendientes FROM @sql_pendientes;
EXECUTE stmt_pendientes;
DEALLOCATE PREPARE stmt_pendientes;

-- Procedimiento para migrar
DELIMITER $$

DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo$$

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
        
        IF v_ventas_migradas % 100 = 0 THEN
            SELECT CONCAT('Procesadas: ', v_ventas_migradas, ' ventas...') AS progreso;
        END IF;
        
    END LOOP;
    
    CLOSE cur_ventas;
    
    COMMIT;
    
    SELECT 
        '=== RESUMEN DE MIGRACIÓN ===' AS titulo,
        v_ventas_migradas AS ventas_migradas,
        v_productos_migrados AS productos_migrados,
        v_productos_omitidos AS productos_omitidos;
    
END$$

DELIMITER ;

-- Ejecutar migración
CALL migrar_ventas_pendientes_completo();

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo;

-- ================================================================
-- PASO 7: VERIFICACIÓN FINAL
-- ================================================================
SELECT '=== PASO 7: Verificación final ===' AS paso;

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

-- 3. Ventas aún pendientes (solo si la tabla existe)
SET @sql_pendientes_final = IF(@existe_ventas > 0,
    CONCAT('SELECT COUNT(*) AS ventas_pendientes, ''Ventas que aún necesitan migración'' AS descripcion FROM ventas v WHERE v.productos IS NOT NULL AND v.productos != '''' AND v.productos != ''[]'' AND JSON_VALID(v.productos) = 1 AND NOT EXISTS (SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id)'),
    'SELECT 0 AS ventas_pendientes, ''Tabla ventas no existe'' AS descripcion'
);

PREPARE stmt_pendientes_final FROM @sql_pendientes_final;
EXECUTE stmt_pendientes_final;
DEALLOCATE PREPARE stmt_pendientes_final;

-- 4. Verificar integridad de índices
SELECT 
    'Verificando índices...' AS verificacion;
    
SELECT 
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
ORDER BY INDEX_NAME, SEQ_IN_INDEX;

-- 5. Verificar foreign keys
SELECT 
    'Verificando foreign keys...' AS verificacion;
    
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- 6. Verificar diferencias en totales (solo si ambas tablas existen)
SET @sql_verificar_totales = IF(@existe_ventas > 0,
    'SELECT pv.id_venta, v.codigo, COUNT(pv.id) as productos_migrados, SUM(pv.cantidad * pv.precio_venta) as total_calculado, v.total as total_venta, ABS(SUM(pv.cantidad * pv.precio_venta) - v.total) as diferencia FROM productos_venta pv INNER JOIN ventas v ON pv.id_venta = v.id GROUP BY pv.id_venta, v.codigo, v.total HAVING ABS(diferencia) > 0.01 LIMIT 10',
    'SELECT 0 as id_venta, ''Tabla ventas no existe'' as codigo, 0 as productos_migrados, 0 as total_calculado, 0 as total_venta, 0 as diferencia'
);

PREPARE stmt_totales FROM @sql_verificar_totales;
EXECUTE stmt_totales;
DEALLOCATE PREPARE stmt_totales;

-- ================================================================
-- RESUMEN FINAL
-- ================================================================
SELECT 
    '=== MIGRACIÓN COMPLETADA ===' AS titulo,
    TIMESTAMPDIFF(SECOND, @inicio, NOW()) AS segundos_transcurridos,
    NOW() AS fecha_finalizacion;

-- Limpiar tabla temporal
DROP TEMPORARY TABLE IF EXISTS temp_productos_inexistentes;
