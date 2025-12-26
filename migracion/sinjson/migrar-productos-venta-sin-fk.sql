-- ================================================================
-- SCRIPT DE MIGRACIÓN: JSON a Tabla Relacional (SIN FK)
-- ================================================================
-- Este script migra los datos del campo JSON 'productos' de la tabla 'ventas'
-- a la nueva tabla relacional 'productos_venta'
-- 
-- IMPORTANTE: Este script deshabilita temporalmente las FOREIGN KEYs
-- para permitir migrar datos con productos que puedan no existir
-- ================================================================
-- PASOS:
-- 1. Ejecutar este script
-- 2. Revisar productos_venta para identificar productos inexistentes
-- 3. Corregir o eliminar productos inexistentes
-- 4. Re-habilitar las FOREIGN KEYs
-- ================================================================

-- Deshabilitar verificación de FOREIGN KEYs temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- Procedimiento almacenado para migrar datos
DELIMITER $$

DROP PROCEDURE IF EXISTS migrar_productos_venta_sin_fk$$

CREATE PROCEDURE migrar_productos_venta_sin_fk()
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
    DECLARE productos_omitidos INT DEFAULT 0;
    DECLARE productos_migrados INT DEFAULT 0;
    
    -- Cursor para recorrer todas las ventas
    DECLARE cur_ventas CURSOR FOR 
        SELECT id, productos 
        FROM ventas 
        WHERE productos IS NOT NULL 
        AND productos != '' 
        AND productos != '[]'
        AND JSON_VALID(productos) = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Iniciar transacción
    START TRANSACTION;
    
    OPEN cur_ventas;
    
    read_loop: LOOP
        FETCH cur_ventas INTO v_id_venta, v_productos_json;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Obtener cantidad de productos en el JSON
        SET productos_count = JSON_LENGTH(v_productos_json);
        SET i = 0;
        
        -- Recorrer cada producto del JSON
        WHILE i < productos_count DO
            -- Extraer datos del producto del JSON
            SET v_producto_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].id'))) AS UNSIGNED);
            SET v_cantidad = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].cantidad'))) AS DECIMAL(10,2));
            SET v_precio_compra = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_compra'))) AS DECIMAL(10,2));
            SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio'))) AS DECIMAL(10,2));
            
            -- Si precio_venta viene como "precio" o "precio_venta", usar el que exista
            IF v_precio_venta = 0 OR v_precio_venta IS NULL THEN
                SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_venta'))) AS DECIMAL(10,2));
            END IF;
            
            -- Validar que tenemos datos mínimos
            IF v_producto_id IS NOT NULL AND v_producto_id > 0 AND v_cantidad > 0 THEN
                -- Verificar que el producto existe en la tabla productos
                SET @producto_existe = (SELECT COUNT(*) FROM productos WHERE id = v_producto_id);
                
                IF @producto_existe > 0 THEN
                    -- Insertar en productos_venta solo si el producto existe
                    INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta)
                    VALUES (v_id_venta, v_producto_id, v_cantidad, 
                            IFNULL(v_precio_compra, 0), 
                            IFNULL(v_precio_venta, 0))
                    ON DUPLICATE KEY UPDATE
                        cantidad = v_cantidad,
                        precio_compra = IFNULL(v_precio_compra, 0),
                        precio_venta = IFNULL(v_precio_venta, 0);
                    SET productos_migrados = productos_migrados + 1;
                ELSE
                    -- Producto no existe, omitir pero contar
                    SET productos_omitidos = productos_omitidos + 1;
                END IF;
            END IF;
            
            SET i = i + 1;
        END WHILE;
        
    END LOOP;
    
    CLOSE cur_ventas;
    
    -- Confirmar transacción
    COMMIT;
    
    -- Mostrar resumen
    SELECT 
        CONCAT('Migración completada. ') AS mensaje,
        productos_migrados AS productos_migrados,
        productos_omitidos AS productos_omitidos_inexistentes,
        (SELECT COUNT(DISTINCT id_venta) FROM productos_venta) AS ventas_migradas;
    
END$$

DELIMITER ;

-- ================================================================
-- EJECUTAR MIGRACIÓN
-- ================================================================
CALL migrar_productos_venta_sin_fk();

-- ================================================================
-- RE-HABILITAR FOREIGN KEYs
-- ================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ================================================================
-- VERIFICAR MIGRACIÓN
-- ================================================================
-- 1. Contar registros migrados
SELECT COUNT(*) as total_productos_venta FROM productos_venta;

-- 2. Identificar productos inexistentes (si los hay)
SELECT DISTINCT
    pv.id_producto,
    COUNT(*) as veces_usado,
    GROUP_CONCAT(DISTINCT pv.id_venta ORDER BY pv.id_venta SEPARATOR ', ') as ventas_afectadas
FROM productos_venta pv
LEFT JOIN productos p ON pv.id_producto = p.id
WHERE p.id IS NULL
GROUP BY pv.id_producto;

-- 3. Comparar con ventas que tienen productos
SELECT COUNT(*) as ventas_con_productos 
FROM ventas 
WHERE productos IS NOT NULL 
AND productos != '' 
AND productos != '[]';

-- 4. Verificar que cada venta tenga sus productos
SELECT 
    v.id as id_venta,
    v.codigo,
    JSON_LENGTH(v.productos) as productos_en_json,
    COUNT(pv.id) as productos_migrados,
    CASE 
        WHEN JSON_LENGTH(v.productos) = COUNT(pv.id) THEN 'OK'
        ELSE 'REVISAR'
    END as estado
FROM ventas v
LEFT JOIN productos_venta pv ON v.id = pv.id_venta
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
GROUP BY v.id, v.codigo
HAVING estado = 'REVISAR'
LIMIT 20;

-- ================================================================
-- LIMPIAR PROCEDIMIENTO (opcional, después de verificar)
-- ================================================================
-- DROP PROCEDURE IF EXISTS migrar_productos_venta_sin_fk;
