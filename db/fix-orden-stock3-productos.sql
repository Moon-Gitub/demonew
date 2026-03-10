-- ================================================================
-- CORREGIR ORDEN DE COLUMNA stock3 EN productos
-- ================================================================
-- Si stock3 quedó después de cambio_desde (por ADD COLUMN sin AFTER),
-- este script la mueve a la posición correcta: después de stock2.
--
-- Orden correcto: stock, stock2, stock3, stock_medio, stock_bajo, ...
-- ================================================================

-- Mover stock3 para que quede después de stock2
ALTER TABLE `productos` MODIFY COLUMN `stock3` decimal(11,2) DEFAULT 0.00 AFTER `stock2`;
