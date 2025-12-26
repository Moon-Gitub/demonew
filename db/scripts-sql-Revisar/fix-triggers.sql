-- =====================================================
-- SCRIPT PARA CORREGIR ERROR #1449 (DEFINER no existe)
-- =====================================================
-- Este script elimina los triggers problemáticos que tienen
-- un DEFINER que no existe en la base de datos
-- =====================================================
-- INSTRUCCIONES:
-- 1. Ejecuta este script PRIMERO en phpMyAdmin
-- 2. Luego ejecuta datos-prueba.sql
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Eliminar triggers existentes
DROP TRIGGER IF EXISTS `prod_eliminar`;
DROP TRIGGER IF EXISTS `prod_insertar`;
DROP TRIGGER IF EXISTS `prod_modificar`;

-- Verificar que se eliminaron correctamente
SELECT 'Triggers eliminados correctamente' AS resultado;
