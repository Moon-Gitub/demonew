-- =====================================================
-- REVERTIR: Si ya ejecutaste el script que renombra stock→stock1
-- y la tabla productos se rompió, ejecuta esto para volver a stock
-- =====================================================

-- 1. Renombrar stock1 → stock
ALTER TABLE `productos` CHANGE COLUMN `stock1` `stock` DECIMAL(11,2) DEFAULT 0.00;

-- 2. Recrear triggers (usan pro.stock)
DROP TRIGGER IF EXISTS `prod_eliminar`;
DROP TRIGGER IF EXISTS `prod_insertar`;
DROP TRIGGER IF EXISTS `prod_modificar`;

DELIMITER $$
CREATE TRIGGER `prod_eliminar` BEFORE DELETE ON `productos` FOR EACH ROW 
INSERT INTO productos_historial SELECT 'borrar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde
FROM productos AS pro WHERE pro.id = OLD.id$$
CREATE TRIGGER `prod_insertar` AFTER INSERT ON `productos` FOR EACH ROW 
INSERT INTO productos_historial SELECT 'insertar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), 
pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde 
FROM productos AS pro WHERE pro.id = NEW.id$$
CREATE TRIGGER `prod_modificar` AFTER UPDATE ON `productos` FOR EACH ROW 
IF NEW.stock <> OLD.stock OR NEW.precio_compra <> OLD.precio_compra OR NEW.precio_venta <> OLD.precio_venta OR NEW.precio_venta_mayorista <> OLD.precio_venta_mayorista THEN
INSERT INTO productos_historial SELECT 'modificar', NULL, CONVERT_TZ(NOW(), @@session.time_zone, '-3:00'), pro.id, pro.stock, pro.precio_compra, pro.precio_venta, pro.precio_venta_mayorista, pro.nombre_usuario, pro.cambio_desde FROM productos AS pro WHERE pro.id = NEW.id;
END IF$$
DELIMITER ;

-- 3. Actualizar empresa.almacenes
UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock","det":"Gutiérrez"},{"stkProd":"stock2","det":"Irigoyen"},{"stkProd":"stock3","det":"Ameghino"}]' WHERE `id` = 1;
