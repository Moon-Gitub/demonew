-- Script actualizado para agregar campos de configuración del login a la tabla empresa
-- EJECUTAR SOLO UNA VEZ - Si ya ejecutaste agregar-campos-login-config.sql, ejecutá solo la última línea

-- Si NO ejecutaste el anterior, ejecutá todo:
ALTER TABLE `empresa` 
ADD COLUMN `login_fondo` VARCHAR(255) DEFAULT NULL COMMENT 'Fondo de la página de login (color o imagen)',
ADD COLUMN `login_logo` VARCHAR(255) DEFAULT NULL COMMENT 'Ruta del logo del login',
ADD COLUMN `login_fondo_form` VARCHAR(255) DEFAULT NULL COMMENT 'Fondo del formulario de login',
ADD COLUMN `login_color_boton` VARCHAR(50) DEFAULT '#52658d' COMMENT 'Color del botón de ingresar',
ADD COLUMN `login_fuente` VARCHAR(100) DEFAULT 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif' COMMENT 'Fuente del login',
ADD COLUMN `login_color_texto_titulo` VARCHAR(50) DEFAULT '#ffffff' COMMENT 'Color del texto del título "Ingresar al sistema"';

-- Si YA ejecutaste el anterior, ejecutá SOLO esta línea:
-- ALTER TABLE `empresa` ADD COLUMN `login_color_texto_titulo` VARCHAR(50) DEFAULT '#ffffff' COMMENT 'Color del texto del título "Ingresar al sistema"';
