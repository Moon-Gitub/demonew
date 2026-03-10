-- ================================================================
-- FIX DEFINITIVO: Recrear productos_venta y migrar
-- ================================================================
-- Si el error #1062 persiste, este script:
--   1. Guarda los datos existentes (por si acaso)
--   2. Elimina y recrea la tabla productos_venta
--   3. Recrea el procedimiento de migración
--   4. Ejecuta la migración
-- ================================================================
-- EJECUTAR TODO EN UNA SOLA VEZ en phpMyAdmin (pestaña SQL)
-- ================================================================

-- PASO 1: Backup opcional (los datos se recuperan del JSON de ventas)
-- Si querés guardar productos_venta actual, exportalo antes manualmente

-- PASO 2: Eliminar procedimiento si existe
DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo;

-- PASO 3: Desactivar FK temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- PASO 4: Eliminar tabla y recrearla limpia
DROP TABLE IF EXISTS productos_venta;

CREATE TABLE `productos_venta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_venta` (`id_venta`),
  KEY `idx_producto` (`id_producto`),
  KEY `idx_venta_producto` (`id_venta`, `id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Asegurar que AUTO_INCREMENT empiece en 1
ALTER TABLE productos_venta AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- PASO 5: Crear procedimiento de migración
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
    
    SELECT 
        '=== RESUMEN DE MIGRACIÓN ===' AS titulo,
        v_ventas_migradas AS ventas_migradas,
        v_productos_migrados AS productos_migrados,
        v_productos_omitidos AS productos_omitidos;
    
END$$

DELIMITER ;

-- PASO 6: Ejecutar migración
CALL migrar_ventas_pendientes_completo();

-- PASO 7: Verificación
SELECT COUNT(*) AS total_productos_venta FROM productos_venta;
SELECT COUNT(DISTINCT id_venta) AS ventas_migradas FROM productos_venta;
