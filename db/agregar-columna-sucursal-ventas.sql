-- =====================================================
-- AGREGAR COLUMNAS sucursal e id_empresa A TABLA ventas
-- Para multi-sucursal y multi-empresa
-- Ejecutar en phpMyAdmin o MySQL
-- =====================================================

-- Si alguna columna ya existe, ese ALTER dará error (es normal, continúa con el siguiente)
-- 1. id_empresa (si no existe)
ALTER TABLE `ventas` ADD COLUMN `id_empresa` INT DEFAULT 1 AFTER `uuid`;

-- 2. sucursal (si no existe)
ALTER TABLE `ventas` ADD COLUMN `sucursal` VARCHAR(50) DEFAULT 'stock' AFTER `productos`;
