-- ===============================================
-- TABLAS PARA SISTEMA DE MERCADOPAGO MEJORADO
-- ===============================================
-- Ejecutar en la base de datos principal

-- Tabla de intentos de pago (preferencias creadas)
CREATE TABLE IF NOT EXISTS `mercadopago_intentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL,
  `preference_id` VARCHAR(255) NOT NULL,
  `monto` DECIMAL(11,2) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `fecha_creacion` DATETIME NOT NULL,
  `fecha_actualizacion` DATETIME DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT 'pendiente' COMMENT 'pendiente, aprobado, rechazado, cancelado',
  PRIMARY KEY (`id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_preference` (`preference_id`),
  INDEX `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de pagos confirmados
CREATE TABLE IF NOT EXISTS `mercadopago_pagos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL,
  `payment_id` VARCHAR(255) NOT NULL,
  `preference_id` VARCHAR(255) DEFAULT NULL,
  `monto` DECIMAL(11,2) NOT NULL,
  `estado` VARCHAR(50) NOT NULL COMMENT 'approved, pending, rejected, cancelled',
  `fecha_pago` DATETIME NOT NULL,
  `payment_type` VARCHAR(50) DEFAULT NULL COMMENT 'credit_card, debit_card, ticket, etc',
  `payment_method_id` VARCHAR(50) DEFAULT NULL,
  `datos_json` TEXT DEFAULT NULL COMMENT 'Datos completos de la respuesta de MP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_id` (`payment_id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_fecha` (`fecha_pago`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de webhooks recibidos (para auditoría)
CREATE TABLE IF NOT EXISTS `mercadopago_webhooks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `topic` VARCHAR(50) NOT NULL COMMENT 'payment, merchant_order, etc',
  `resource_id` VARCHAR(255) NOT NULL,
  `datos_json` TEXT DEFAULT NULL,
  `fecha_recibido` DATETIME NOT NULL,
  `fecha_procesado` DATETIME DEFAULT NULL,
  `procesado` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `idx_topic` (`topic`),
  INDEX `idx_resource` (`resource_id`),
  INDEX `idx_fecha` (`fecha_recibido`),
  INDEX `idx_procesado` (`procesado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentarios para documentación
ALTER TABLE `mercadopago_intentos` 
  COMMENT = 'Registro de todas las preferencias de pago creadas';

ALTER TABLE `mercadopago_pagos` 
  COMMENT = 'Registro de pagos confirmados por MercadoPago';

ALTER TABLE `mercadopago_webhooks` 
  COMMENT = 'Log de todas las notificaciones recibidas desde MercadoPago';

-- ===============================================
-- VISTAS ÚTILES
-- ===============================================

-- Vista de resumen de pagos por cliente
CREATE OR REPLACE VIEW v_mercadopago_resumen AS
SELECT 
    id_cliente_moon,
    COUNT(*) as total_pagos,
    SUM(monto) as total_monto,
    MAX(fecha_pago) as ultimo_pago,
    MIN(fecha_pago) as primer_pago
FROM mercadopago_pagos
WHERE estado = 'approved'
GROUP BY id_cliente_moon;

-- Vista de pagos pendientes
CREATE OR REPLACE VIEW v_mercadopago_pendientes AS
SELECT 
    i.*,
    DATEDIFF(NOW(), i.fecha_creacion) as dias_pendiente
FROM mercadopago_intentos i
LEFT JOIN mercadopago_pagos p ON i.preference_id = p.preference_id
WHERE p.id IS NULL
AND i.estado = 'pendiente'
ORDER BY i.fecha_creacion DESC;

-- ===============================================
-- VERIFICAR INSTALACIÓN
-- ===============================================
SELECT 
    'Tablas creadas exitosamente' as mensaje,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = 'mercadopago_intentos') as tabla_intentos,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = 'mercadopago_pagos') as tabla_pagos,
    (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME = 'mercadopago_webhooks') as tabla_webhooks;

