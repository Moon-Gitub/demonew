-- ===============================================
-- ⚠️ IMPORTANTE: EJECUTAR ESTE SQL EN LA BD MOON
-- ===============================================
-- Base de datos: moondesa_moon
-- Servidor: 107.161.23.241
-- Usuario: moondesa_moon
--
-- PASOS:
-- 1. Abrir phpMyAdmin
-- 2. Conectar al servidor 107.161.23.241
-- 3. Seleccionar base de datos: moondesa_moon
-- 4. Ir a pestaña "SQL"
-- 5. Copiar y pegar TODO este archivo
-- 6. Hacer clic en "Continuar"
-- ===============================================

-- Tabla 1: mercadopago_intentos
-- Registra todas las preferencias de pago creadas
CREATE TABLE IF NOT EXISTS `mercadopago_intentos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL COMMENT 'ID del cliente en tabla clientes',
  `preference_id` VARCHAR(255) NOT NULL COMMENT 'ID de preferencia de MercadoPago',
  `monto` DECIMAL(11,2) NOT NULL COMMENT 'Monto a cobrar',
  `descripcion` VARCHAR(255) DEFAULT NULL COMMENT 'Descripción del cobro',
  `fecha_creacion` DATETIME NOT NULL COMMENT 'Cuándo se creó la preferencia',
  `fecha_actualizacion` DATETIME DEFAULT NULL COMMENT 'Última actualización',
  `estado` VARCHAR(50) DEFAULT 'pendiente' COMMENT 'pendiente, aprobado, rechazado, cancelado',
  PRIMARY KEY (`id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_preference` (`preference_id`),
  INDEX `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de todas las preferencias de pago creadas';

-- Tabla 2: mercadopago_pagos
-- Registra todos los pagos confirmados
CREATE TABLE IF NOT EXISTS `mercadopago_pagos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` INT(11) NOT NULL COMMENT 'ID del cliente en tabla clientes',
  `payment_id` VARCHAR(255) NOT NULL COMMENT 'ID único del pago en MercadoPago',
  `preference_id` VARCHAR(255) DEFAULT NULL COMMENT 'ID de preferencia asociada',
  `monto` DECIMAL(11,2) NOT NULL COMMENT 'Monto pagado',
  `estado` VARCHAR(50) NOT NULL COMMENT 'approved, pending, rejected, cancelled',
  `fecha_pago` DATETIME NOT NULL COMMENT 'Cuándo se realizó el pago',
  `payment_type` VARCHAR(50) DEFAULT NULL COMMENT 'credit_card, debit_card, ticket, etc',
  `payment_method_id` VARCHAR(50) DEFAULT NULL COMMENT 'visa, mastercard, efectivo, etc',
  `datos_json` TEXT DEFAULT NULL COMMENT 'Datos completos de la respuesta de MP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_id` (`payment_id`),
  INDEX `idx_cliente` (`id_cliente_moon`),
  INDEX `idx_fecha` (`fecha_pago`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de pagos confirmados por MercadoPago';

-- Tabla 3: mercadopago_webhooks
-- Registra todas las notificaciones recibidas de MercadoPago (auditoría)
CREATE TABLE IF NOT EXISTS `mercadopago_webhooks` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `topic` VARCHAR(50) NOT NULL COMMENT 'payment, merchant_order, etc',
  `resource_id` VARCHAR(255) NOT NULL COMMENT 'ID del recurso (payment_id, order_id, etc)',
  `datos_json` TEXT DEFAULT NULL COMMENT 'Datos completos del webhook',
  `fecha_recibido` DATETIME NOT NULL COMMENT 'Cuándo se recibió el webhook',
  `fecha_procesado` DATETIME DEFAULT NULL COMMENT 'Cuándo se procesó',
  `procesado` TINYINT(1) DEFAULT 0 COMMENT '0=no procesado, 1=procesado',
  PRIMARY KEY (`id`),
  INDEX `idx_topic` (`topic`),
  INDEX `idx_resource` (`resource_id`),
  INDEX `idx_fecha` (`fecha_recibido`),
  INDEX `idx_procesado` (`procesado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log de todas las notificaciones recibidas desde MercadoPago';

-- ===============================================
-- VISTAS ÚTILES PARA CONSULTAS
-- ===============================================

-- Vista 1: Resumen de pagos por cliente
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

-- Vista 2: Pagos pendientes (preferencias creadas pero no pagadas)
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
-- VERIFICAR QUE SE CREARON LAS TABLAS
-- ===============================================

-- Ver tablas de MercadoPago
SHOW TABLES LIKE 'mercadopago%';

-- Ver estructura de las tablas
DESCRIBE mercadopago_intentos;
DESCRIBE mercadopago_pagos;
DESCRIBE mercadopago_webhooks;

-- Ver las vistas creadas
SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW';

-- ===============================================
-- CONSULTAS ÚTILES PARA PROBAR
-- ===============================================

-- Ver todos los pagos aprobados
SELECT
    p.id,
    p.id_cliente_moon,
    p.payment_id,
    p.monto,
    p.estado,
    p.fecha_pago,
    p.payment_method_id
FROM mercadopago_pagos p
WHERE p.estado = 'approved'
ORDER BY p.fecha_pago DESC
LIMIT 10;

-- Ver intentos pendientes
SELECT * FROM v_mercadopago_pendientes LIMIT 10;

-- Ver resumen por cliente
SELECT * FROM v_mercadopago_resumen;

-- ===============================================
-- FIN DEL SCRIPT
-- ===============================================
-- ✅ Si ves este mensaje sin errores, las tablas se crearon correctamente
SELECT 'Tablas de MercadoPago creadas exitosamente' as mensaje;
