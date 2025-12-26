-- ============================================
-- SCRIPT COMPLETO PARA CONFIGURAR MERCADO PAGO
-- Ejecutar este script en la base de datos
-- ============================================

-- 1. Agregar campos de credenciales
SOURCE agregar-campos-mercadopago-empresa.sql;

-- 2. Agregar campo POS estático
SOURCE agregar-campo-pos-estatico-empresa.sql;

SELECT 'Scripts de Mercado Pago ejecutados correctamente' AS resultado;
