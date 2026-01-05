-- ================================================================
-- FIX: Corregir PRIMARY KEY en productos_venta
-- ================================================================
-- Este script corrige el problema de la tabla productos_venta
-- que fue creada sin PRIMARY KEY o con id = 0
-- ================================================================
-- IMPORTANTE: Ejecutar SOLO si la tabla ya existe sin PRIMARY KEY
-- ================================================================

SELECT '=== Corrigiendo PRIMARY KEY en productos_venta ===' AS paso;

-- Verificar si la tabla existe
SET @tabla_existe = (
    SELECT COUNT(*) 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'productos_venta'
);

SELECT 
    CASE 
        WHEN @tabla_existe > 0 THEN '✅ Tabla productos_venta existe'
        ELSE '❌ ERROR: Tabla productos_venta no existe. Ejecuta primero 01-CREAR-ESTRUCTURA.sql'
    END AS estado_tabla;

-- Verificar si tiene PRIMARY KEY
SET @tiene_pk = (
    SELECT COUNT(*) 
    FROM information_schema.table_constraints 
    WHERE table_schema = DATABASE() 
    AND table_name = 'productos_venta'
    AND constraint_type = 'PRIMARY KEY'
);

SELECT 
    CASE 
        WHEN @tiene_pk > 0 THEN '✅ Tabla ya tiene PRIMARY KEY'
        ELSE '⚠️ Tabla NO tiene PRIMARY KEY, corrigiendo...'
    END AS estado_pk;

-- Crear procedimiento para corregir PRIMARY KEY
DELIMITER $$

DROP PROCEDURE IF EXISTS fix_primary_key_productos_venta$$

CREATE PROCEDURE fix_primary_key_productos_venta()
BEGIN
    DECLARE v_tabla_existe INT;
    DECLARE v_tiene_pk INT;
    DECLARE v_hay_ceros INT;
    DECLARE v_max_id INT;
    DECLARE v_next_id INT;
    DECLARE v_counter INT DEFAULT 0;
    
    -- Verificar si la tabla existe
    SET v_tabla_existe = (
        SELECT COUNT(*) 
        FROM information_schema.tables 
        WHERE table_schema = DATABASE() 
        AND table_name = 'productos_venta'
    );
    
    -- Verificar si tiene PRIMARY KEY
    SET v_tiene_pk = (
        SELECT COUNT(*) 
        FROM information_schema.table_constraints 
        WHERE table_schema = DATABASE() 
        AND table_name = 'productos_venta'
        AND constraint_type = 'PRIMARY KEY'
    );
    
    IF v_tabla_existe > 0 AND v_tiene_pk = 0 THEN
        -- Verificar si hay datos con id = 0
        SET v_hay_ceros = (
            SELECT COUNT(*) 
            FROM productos_venta 
            WHERE id = 0
        );
        
        SELECT 
            CASE 
                WHEN v_hay_ceros > 0 THEN CONCAT('⚠️ Encontrados ', v_hay_ceros, ' registros con id = 0. Se actualizarán.')
                ELSE '✅ No hay registros con id = 0'
            END AS estado_ceros;
        
        -- Si hay registros con id = 0, corregirlos
        IF v_hay_ceros > 0 THEN
            -- Crear columna temporal si no existe
            SET @sql_add_temp = 'ALTER TABLE productos_venta ADD COLUMN temp_id INT(11) NULL';
            SET @sql_check_temp = (
                SELECT COUNT(*) 
                FROM information_schema.columns 
                WHERE table_schema = DATABASE() 
                AND table_name = 'productos_venta'
                AND column_name = 'temp_id'
            );
            
            IF @sql_check_temp = 0 THEN
                PREPARE stmt_add_temp FROM @sql_add_temp;
                EXECUTE stmt_add_temp;
                DEALLOCATE PREPARE stmt_add_temp;
            END IF;
            
            -- Obtener el máximo id actual (excluyendo 0)
            SET @max_id = COALESCE((SELECT MAX(id) FROM productos_venta WHERE id > 0), 0);
            
            -- Actualizar los id = 0 con valores incrementales únicos
            UPDATE productos_venta 
            SET id = (@max_id := @max_id + 1)
            WHERE id = 0
            ORDER BY id_venta, id_producto, created_at;
            
            -- Eliminar columna temporal
            ALTER TABLE productos_venta DROP COLUMN IF EXISTS temp_id;
        END IF;
        
        -- Modificar la columna id para que sea AUTO_INCREMENT y PRIMARY KEY
        ALTER TABLE productos_venta 
        MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT,
        ADD PRIMARY KEY (id);
        
        -- Resetear AUTO_INCREMENT al siguiente valor correcto
        SET v_next_id = (SELECT MAX(id) + 1 FROM productos_venta);
        SET @sql_reset = CONCAT('ALTER TABLE productos_venta AUTO_INCREMENT = ', v_next_id);
        PREPARE stmt_reset FROM @sql_reset;
        EXECUTE stmt_reset;
        DEALLOCATE PREPARE stmt_reset;
        
        SELECT '✅ PRIMARY KEY agregado y registros corregidos' AS resultado;
    ELSE
        SELECT 'ℹ️ No se requiere corrección' AS resultado;
    END IF;
END$$

DELIMITER ;

-- Ejecutar el procedimiento solo si la tabla existe y no tiene PRIMARY KEY
SET @sql_call = IF(@tabla_existe > 0 AND @tiene_pk = 0,
    'CALL fix_primary_key_productos_venta()',
    'SELECT ''No se requiere corrección'' AS mensaje'
);

PREPARE stmt_call FROM @sql_call;
EXECUTE stmt_call;
DEALLOCATE PREPARE stmt_call;

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS fix_primary_key_productos_venta;

-- Verificación final
SELECT 
    '=== VERIFICACIÓN FINAL ===' AS titulo,
    COUNT(*) AS total_registros,
    COUNT(DISTINCT id) AS ids_unicos,
    MIN(id) AS id_minimo,
    MAX(id) AS id_maximo,
    SUM(CASE WHEN id = 0 THEN 1 ELSE 0 END) AS registros_con_id_cero
FROM productos_venta;

SELECT 
    CASE 
        WHEN SUM(CASE WHEN id = 0 THEN 1 ELSE 0 END) = 0 THEN '✅ Todos los registros tienen id único'
        ELSE '⚠️ Aún hay registros con id = 0'
    END AS estado_final;
