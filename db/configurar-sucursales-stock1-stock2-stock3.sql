-- =====================================================
-- SUCURSALES GENÉRICAS: stock1, stock2, stock3
-- Display: Gutiérrez, Irigoyen, Ameghino
-- Ejecutar ANTES de actualizar el código PHP
-- =====================================================

-- 1. Renombrar columnas
ALTER TABLE `productos` CHANGE COLUMN `stock` `stock1` DECIMAL(11,2) DEFAULT 0.00;
ALTER TABLE `productos` CHANGE COLUMN `deposito` `stock2` DECIMAL(11,2) DEFAULT 0.00;

-- 2. Agregar stock3
ALTER TABLE `productos` ADD COLUMN `stock3` DECIMAL(11,2) DEFAULT 0.00 AFTER `stock2`;

-- 3. Actualizar triggers (productos_historial usa stock1)
DROP TRIGGER IF EXISTS `prod_eliminar`;
DROP TRIGGER IF EXISTS `prod_insertar`;
DROP TRIGGER IF EXISTS `prod_modificar`;

DELIMITER $$
CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW 
INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock1, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id$$
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW 
INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock1, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde 
FROM productos AS pro WHERE pro.id = NEW.id$$
CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW 
IF NEW.stock1 <> OLD.stock1 OR NEW.precio_compra <> OLD.precio_compra OR NEW.precio_venta <> OLD.precio_venta OR NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), pro.id, pro.stock1, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
END IF$$
DELIMITER ;

-- 4. Agregar columna sucursal a ventas si no existe (omitir si ya la tiene)
-- ALTER TABLE `ventas` ADD COLUMN `sucursal` VARCHAR(100) DEFAULT 'stock1' AFTER `productos`;

-- 5. Configurar empresa.almacenes
UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock1","det":"Gutiérrez"},{"stkProd":"stock2","det":"Irigoyen"},{"stkProd":"stock3","det":"Ameghino"}]' WHERE `id` = 1;
