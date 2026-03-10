-- =====================================================
-- VERIFICAR Y CORREGIR empresa.almacenes
-- Para Gutiérrez, Irigoyen, Ameghino con stock, stock2, stock3
-- =====================================================

-- 1. Ver configuración actual
SELECT id, razon_social, almacenes FROM empresa WHERE id = 1;

-- 2. Si la tabla productos tiene stock, deposito, ameghino (schema antigua):
--    Usar: stock, deposito, ameghino como stkProd
-- 3. Si tiene stock, stock2, stock3 (schema nueva):
--    Usar: stock, stock2, stock3

-- Corregir a schema nueva (stock, stock2, stock3):
UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock","det":"Gutiérrez"},{"stkProd":"stock2","det":"Irigoyen"},{"stkProd":"stock3","det":"Ameghino"}]' WHERE `id` = 1;

-- Si tu tabla tiene deposito/ameghino en vez de stock2/stock3, usar:
-- UPDATE `empresa` SET `almacenes` = '[{"stkProd":"stock","det":"Gutiérrez"},{"stkProd":"deposito","det":"Irigoyen"},{"stkProd":"ameghino","det":"Ameghino"}]' WHERE `id` = 1;
