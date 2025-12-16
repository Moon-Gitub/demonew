-- Script para agregar campos de configuración del login a la tabla empresa
-- Ejecutar este script en la base de datos

ALTER TABLE `empresa` 
ADD COLUMN `login_fondo` VARCHAR(255) DEFAULT NULL COMMENT 'Fondo de la página de login (color o imagen)',
ADD COLUMN `login_logo` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta del logo del login',
ADD COLUMN `login_fondo_form` VARCHAR(255) DEFAULT NULL COMMENT 'Fondo del formulario de login',
ADD COLUMN `login_color_boton` VARCHAR(50) DEFAULT '#52658d' COMMENT 'Color del botón de ingresar',
ADD COLUMN `login_fuente` VARCHAR(100) DEFAULT 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' COMMENT 'Fuente del login';
