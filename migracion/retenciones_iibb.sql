-- ================================================================
-- Retenciones IIBB (SIRCAR Diseño N° 1 - Mendoza 913)
-- Ejecutar una sola vez en la base del cliente.
-- ================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Empresa: configuración agente de retención
ALTER TABLE `empresa` ADD COLUMN `agente_retencion_iibb` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=agente de retención IIBB habilitado';
ALTER TABLE `empresa` ADD COLUMN `codigo_jurisdiccion_iibb` INT NOT NULL DEFAULT 913 COMMENT 'Código CM SIRCAR (913=Mendoza)';
ALTER TABLE `empresa` ADD COLUMN `tipo_regimen_retencion_default` INT NOT NULL DEFAULT 101 COMMENT 'Tipo régimen retención SIRCAR';
ALTER TABLE `empresa` ADD COLUMN `proximo_numero_recibo` INT NOT NULL DEFAULT 1 COMMENT 'Próximo nº recibo interno de retención';

-- Proveedores: alícuota por defecto
ALTER TABLE `proveedores` ADD COLUMN `tipo_alicuota_iibb` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Alícuota retención IIBB %';

-- Cuenta corriente proveedor: datos factura y retención
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `factura_numero` VARCHAR(20) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `factura_neto_previo` DECIMAL(14,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `factura_descuento` DECIMAL(14,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `factura_neto` DECIMAL(14,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `factura_iva` DECIMAL(14,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `total` DECIMAL(14,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `fecha_retencion` DATE NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `numero_recibo` INT NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `alicuota_retencion` DECIMAL(5,2) NULL DEFAULT NULL;
ALTER TABLE `proveedores_cuenta_corriente` ADD COLUMN `monto_retencion` DECIMAL(14,2) NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- Pantalla en permisos por rol (si existe tabla pantallas)
INSERT IGNORE INTO `pantallas` (`codigo`, `nombre`, `agrupacion`, `orden`) VALUES
('retenciones-iibb', 'Retenciones IIBB', 'Proveedores', 835);

INSERT IGNORE INTO `permisos_rol` (`rol`, `id_pantalla`)
SELECT 'Administrador', id FROM `pantallas` WHERE codigo = 'retenciones-iibb' AND activo = 1;
