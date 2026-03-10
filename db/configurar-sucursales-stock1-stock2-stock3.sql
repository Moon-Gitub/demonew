-- =====================================================
-- SUCURSALES: stock, stock2, stock3 (Gutiérrez, Irigoyen, Ameghino)
-- NO renombra "stock" - mantiene compatibilidad con triggers
-- =====================================================

-- 1. Renombrar deposito → stock2 (stock se mantiene)
ALTER TABLE `productos` CHANGE COLUMN `deposito` `stock2` DECIMAL(11,2) DEFAULT 0.00;

-- 2. Agregar stock3
ALTER TABLE `productos` ADD COLUMN `stock3` DECIMAL(11,2) DEFAULT 0.00 AFTER `stock2`;

-- 3. Configurar empresa.almacenes
UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock","det":"Gutiérrez"},{"stkProd":"stock2","det":"Irigoyen"},{"stkProd":"stock3","det":"Ameghino"}]' WHERE `id` = 1;
