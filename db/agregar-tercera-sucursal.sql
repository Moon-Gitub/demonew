-- =====================================================
-- AGREGAR TERCERA SUCURSAL (Depósito 2)
-- Ejecutar en phpMyAdmin o MySQL
-- =====================================================

-- 1. Agregar columna de stock para la tercera sucursal
ALTER TABLE `productos` ADD COLUMN `deposito2` DECIMAL(11,2) DEFAULT 0 AFTER `deposito`;

-- 2. Actualizar empresa.almacenes con las 3 sucursales
-- (Ajusta los nombres "det" si usas otros: Local, Depósito 1, Depósito 2)
UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock","det":"Local"},{"stkProd":"deposito","det":"Depósito 1"},{"stkProd":"deposito2","det":"Depósito 2"}]' WHERE `id` = 1;
