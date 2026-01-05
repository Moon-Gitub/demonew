-- ================================================================
-- SCRIPT DE MIGRACIÓN: Migrar ventas pendientes a productos_venta
-- ================================================================
-- Este script migra SOLO las ventas que:
--   1. Tienen productos en JSON (campo productos)
--   2. NO tienen productos en la tabla productos_venta
-- ================================================================
-- IMPORTANTE: Ejecutar después de crear la tabla productos_venta
-- ================================================================

-- Procedimiento almacenado para migrar solo ventas pendientes
DELIMITER $$

DROP PROCEDURE IF EXISTS migrar_ventas_pendientes$$

CREATE PROCEDURE migrar_ventas_pendientes()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_venta INT;
    DECLARE v_productos_json TEXT;
    DECLARE v_producto_id INT;
    DECLARE v_cantidad DECIMAL(10,2);
    DECLARE v_precio_compra DECIMAL(10,2);
    DECLARE v_precio_venta DECIMAL(10,2);
    DECLARE v_total DECIMAL(10,2);
    DECLARE v_descripcion VARCHAR(255);
    DECLARE i INT DEFAULT 0;
    DECLARE productos_count INT;
    DECLARE v_ventas_migradas INT DEFAULT 0;
    DECLARE v_productos_migrados INT DEFAULT 0;
    DECLARE v_errores INT DEFAULT 0;
    
    -- Cursor para recorrer solo las ventas que tienen JSON pero NO tienen productos_venta
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
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION 
    BEGIN
        SET v_errores = v_errores + 1;
        ROLLBACK;
        RESIGNAL;
    END;
    
    -- Crear tabla temporal para reporte
    DROP TEMPORARY TABLE IF EXISTS temp_migracion_reporte;
    CREATE TEMPORARY TABLE temp_migracion_reporte (
        id_venta INT,
        codigo_venta INT,
        productos_json INT,
        productos_migrados INT,
        estado VARCHAR(50),
        error TEXT
    );
    
    -- Iniciar transacción
    START TRANSACTION;
    
    OPEN cur_ventas;
    
    read_loop: LOOP
        FETCH cur_ventas INTO v_id_venta, v_productos_json;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        BEGIN
            DECLARE productos_migrados_venta INT DEFAULT 0;
            DECLARE productos_json_count INT;
            
            -- Obtener cantidad de productos en el JSON
            SET productos_json_count = JSON_LENGTH(v_productos_json);
            SET i = 0;
            SET productos_migrados_venta = 0;
            
            -- Recorrer cada producto del JSON
            WHILE i < productos_json_count DO
                -- Extraer datos del producto del JSON
                SET v_producto_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].id'))) AS UNSIGNED);
                SET v_cantidad = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].cantidad'))) AS DECIMAL(10,2));
                SET v_precio_compra = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_compra'))) AS DECIMAL(10,2));
                
                -- Intentar obtener precio_venta de "precio" primero
                SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio'))) AS DECIMAL(10,2));
                
                -- Si precio_venta viene como "precio_venta" o si "precio" es 0, usar "precio_venta"
                IF v_precio_venta = 0 OR v_precio_venta IS NULL THEN
                    SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_venta'))) AS DECIMAL(10,2));
                END IF;
                
                -- Validar que tenemos datos mínimos Y que el producto existe
                IF v_producto_id IS NOT NULL AND v_producto_id > 0 AND v_cantidad > 0 THEN
                    -- Verificar que el producto existe en la tabla productos
                    IF EXISTS(SELECT 1 FROM productos WHERE id = v_producto_id) THEN
                        -- Insertar en productos_venta
                        INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta)
                        VALUES (v_id_venta, v_producto_id, v_cantidad, 
                                IFNULL(v_precio_compra, 0), 
                                IFNULL(v_precio_venta, 0));
                        
                        SET productos_migrados_venta = productos_migrados_venta + 1;
                    END IF;
                END IF;
                
                SET i = i + 1;
            END WHILE;
            
            -- Registrar en reporte
            INSERT INTO temp_migracion_reporte (id_venta, codigo_venta, productos_json, productos_migrados, estado)
            SELECT 
                v_id_venta,
                v.codigo,
                productos_json_count,
                productos_migrados_venta,
                CASE 
                    WHEN productos_migrados_venta = productos_json_count THEN 'OK'
                    WHEN productos_migrados_venta = 0 THEN 'ERROR: Ningún producto migrado'
                    ELSE CONCAT('PARCIAL: ', productos_migrados_venta, ' de ', productos_json_count)
                END
            FROM ventas v
            WHERE v.id = v_id_venta;
            
            SET v_ventas_migradas = v_ventas_migradas + 1;
            SET v_productos_migrados = v_productos_migrados + productos_migrados_venta;
            
        END;
        
    END LOOP;
    
    CLOSE cur_ventas;
    
    -- Confirmar transacción
    COMMIT;
    
    -- Mostrar resumen
    SELECT 
        '=== RESUMEN DE MIGRACIÓN ===' AS titulo,
        v_ventas_migradas AS ventas_migradas,
        v_productos_migrados AS productos_migrados,
        v_errores AS errores;
    
    -- Mostrar reporte detallado
    SELECT 
        id_venta,
        codigo_venta,
        productos_json,
        productos_migrados,
        estado
    FROM temp_migracion_reporte
    ORDER BY id_venta;
    
    -- Mostrar ventas con problemas
    SELECT 
        id_venta,
        codigo_venta,
        productos_json,
        productos_migrados,
        estado
    FROM temp_migracion_reporte
    WHERE estado != 'OK'
    ORDER BY id_venta;
    
    -- Limpiar tabla temporal
    DROP TEMPORARY TABLE IF EXISTS temp_migracion_reporte;
    
END$$

DELIMITER ;

-- ================================================================
-- EJECUTAR MIGRACIÓN
-- ================================================================

-- Primero, ver cuántas ventas necesitan migración
SELECT 
    COUNT(*) as ventas_pendientes,
    'Ventas con productos en JSON pero sin productos_venta' as descripcion
FROM ventas v
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);

-- Ejecutar la migración
CALL migrar_ventas_pendientes();

-- ================================================================
-- VERIFICACIÓN POST-MIGRACIÓN
-- ================================================================

-- 1. Contar total de registros en productos_venta
SELECT COUNT(*) as total_productos_venta FROM productos_venta;

-- 2. Contar ventas que tienen productos_venta
SELECT COUNT(DISTINCT id_venta) as ventas_con_productos_venta FROM productos_venta;

-- 3. Verificar que no queden ventas pendientes
SELECT 
    COUNT(*) as ventas_aun_pendientes,
    'Ventas que aún necesitan migración' as descripcion
FROM ventas v
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);

-- 4. Verificar integridad: comparar totales
SELECT 
    pv.id_venta,
    v.codigo,
    COUNT(pv.id) as productos_migrados,
    SUM(pv.cantidad * pv.precio_venta) as total_calculado,
    v.total as total_venta,
    ABS(SUM(pv.cantidad * pv.precio_venta) - v.total) as diferencia
FROM productos_venta pv
INNER JOIN ventas v ON pv.id_venta = v.id
GROUP BY pv.id_venta, v.codigo, v.total
HAVING ABS(diferencia) > 0.01
LIMIT 20;

-- ================================================================
-- LIMPIAR PROCEDIMIENTO (opcional, después de verificar)
-- ================================================================
-- DROP PROCEDURE IF EXISTS migrar_ventas_pendientes;
