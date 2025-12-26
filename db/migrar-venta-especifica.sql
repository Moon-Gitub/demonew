-- ================================================================
-- SCRIPT PARA MIGRAR UNA VENTA ESPECÍFICA
-- ================================================================
-- Este script migra los productos de una venta específica desde JSON
-- a la tabla relacional productos_venta
-- ================================================================
-- USO: Cambiar @id_venta por el ID de la venta a migrar
-- ================================================================

SET @id_venta = 245690; -- Cambiar por el ID de la venta a migrar

-- Obtener el JSON de productos de la venta
SET @productos_json = (SELECT productos FROM ventas WHERE id = @id_venta);

-- Verificar que la venta existe y tiene productos
SELECT 
    @id_venta as id_venta,
    CASE 
        WHEN @productos_json IS NULL THEN 'Venta no encontrada'
        WHEN @productos_json = '' THEN 'Sin productos JSON'
        WHEN @productos_json = '[]' THEN 'JSON vacío'
        WHEN JSON_VALID(@productos_json) = 0 THEN 'JSON inválido'
        ELSE 'JSON válido'
    END as estado_json,
    JSON_LENGTH(@productos_json) as cantidad_productos_json,
    @productos_json as productos_json;

-- Procedimiento temporal para migrar esta venta específica
DELIMITER $$

DROP PROCEDURE IF EXISTS migrar_venta_especifica$$

CREATE PROCEDURE migrar_venta_especifica()
BEGIN
    DECLARE v_producto_id INT;
    DECLARE v_cantidad DECIMAL(10,2);
    DECLARE v_precio_compra DECIMAL(10,2);
    DECLARE v_precio_venta DECIMAL(10,2);
    DECLARE i INT DEFAULT 0;
    DECLARE productos_count INT;
    DECLARE v_productos_json TEXT;
    
    -- Obtener el JSON de productos
    SET v_productos_json = (SELECT productos FROM ventas WHERE id = @id_venta);
    
    -- Validar que existe y tiene datos
    IF v_productos_json IS NULL OR v_productos_json = '' OR v_productos_json = '[]' OR JSON_VALID(v_productos_json) = 0 THEN
        SELECT CONCAT('Error: La venta ', @id_venta, ' no tiene productos válidos en JSON') AS mensaje;
        LEAVE migrar_venta_especifica;
    END IF;
    
    -- Obtener cantidad de productos
    SET productos_count = JSON_LENGTH(v_productos_json);
    
    -- Eliminar productos existentes de esta venta en productos_venta (por si acaso)
    DELETE FROM productos_venta WHERE id_venta = @id_venta;
    
    -- Recorrer cada producto del JSON
    WHILE i < productos_count DO
        -- Extraer datos del producto del JSON
        SET v_producto_id = JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].id')));
        SET v_cantidad = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].cantidad'))) AS DECIMAL(10,2));
        SET v_precio_compra = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_compra'))) AS DECIMAL(10,2));
        SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio'))) AS DECIMAL(10,2));
        
        -- Si precio_venta viene como "precio" o "precio_venta", usar el que exista
        IF v_precio_venta = 0 OR v_precio_venta IS NULL THEN
            SET v_precio_venta = CAST(JSON_UNQUOTE(JSON_EXTRACT(v_productos_json, CONCAT('$[', i, '].precio_venta'))) AS DECIMAL(10,2));
        END IF;
        
        -- Validar que tenemos datos mínimos Y que el producto existe
        IF v_producto_id IS NOT NULL AND v_producto_id > 0 AND v_cantidad > 0 THEN
            -- Verificar que el producto existe en la tabla productos
            IF EXISTS(SELECT 1 FROM productos WHERE id = v_producto_id) THEN
                -- Insertar en productos_venta
                INSERT INTO productos_venta (id_venta, id_producto, cantidad, precio_compra, precio_venta)
                VALUES (@id_venta, v_producto_id, v_cantidad, 
                        IFNULL(v_precio_compra, 0), 
                        IFNULL(v_precio_venta, 0));
            END IF;
        END IF;
        
        SET i = i + 1;
    END WHILE;
    
    -- Mostrar resultado
    SELECT 
        CONCAT('Migración completada para venta ', @id_venta) AS mensaje,
        (SELECT COUNT(*) FROM productos_venta WHERE id_venta = @id_venta) AS productos_migrados;
END$$

DELIMITER ;

-- Ejecutar la migración
CALL migrar_venta_especifica();

-- Verificar resultado
SELECT * FROM productos_venta WHERE id_venta = @id_venta;

-- Limpiar procedimiento
DROP PROCEDURE IF EXISTS migrar_venta_especifica;
