-- =============================================
-- SCRIPT DE INSTALACIÓN DEL SISTEMA DE COBRO MOON POS
-- Base de datos: Moon (Remota)
-- =============================================

-- =============================================
-- TABLA 1: mercadopago_intentos
-- Registra cada vez que se crea un link de pago
-- =============================================
CREATE TABLE IF NOT EXISTS `mercadopago_intentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` int(11) NOT NULL COMMENT 'ID del cliente en tabla clientes',
  `preference_id` varchar(255) NOT NULL COMMENT 'ID de la preferencia de MercadoPago',
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto total del intento de pago',
  `descripcion` varchar(255) DEFAULT NULL COMMENT 'Descripción del pago',
  `fecha_creacion` datetime NOT NULL COMMENT 'Fecha y hora de creación del intento',
  `fecha_actualizacion` datetime DEFAULT NULL COMMENT 'Última actualización del intento',
  `estado` varchar(50) NOT NULL DEFAULT 'pendiente' COMMENT 'Estado: pendiente, completado, cancelado, expirado',
  PRIMARY KEY (`id`),
  KEY `idx_cliente` (`id_cliente_moon`),
  KEY `idx_preference` (`preference_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Intentos de pago creados (preferencias de MercadoPago)';

-- =============================================
-- TABLA 2: mercadopago_pagos
-- Registra los pagos confirmados
-- =============================================
CREATE TABLE IF NOT EXISTS `mercadopago_pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_cliente_moon` int(11) NOT NULL COMMENT 'ID del cliente en tabla clientes',
  `payment_id` varchar(255) NOT NULL COMMENT 'ID único del pago en MercadoPago',
  `preference_id` varchar(255) DEFAULT NULL COMMENT 'ID de la preferencia asociada',
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto pagado',
  `estado` varchar(50) NOT NULL COMMENT 'Estado: approved, pending, rejected, cancelled',
  `fecha_pago` datetime NOT NULL COMMENT 'Fecha y hora del pago',
  `payment_type` varchar(50) DEFAULT NULL COMMENT 'Tipo de pago: credit_card, debit_card, ticket, etc',
  `payment_method_id` varchar(50) DEFAULT NULL COMMENT 'Método específico: visa, mastercard, efectivo, etc',
  `datos_json` text COMMENT 'Respuesta completa de MercadoPago en JSON',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_payment_id` (`payment_id`),
  KEY `idx_cliente` (`id_cliente_moon`),
  KEY `idx_preference` (`preference_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_pago`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos confirmados de MercadoPago';

-- =============================================
-- TABLA 3: mercadopago_webhooks
-- Registra las notificaciones recibidas de MercadoPago
-- =============================================
CREATE TABLE IF NOT EXISTS `mercadopago_webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic` varchar(50) NOT NULL COMMENT 'Tipo de notificación: payment, merchant_order',
  `resource_id` varchar(255) NOT NULL COMMENT 'ID del recurso notificado',
  `datos_json` text NOT NULL COMMENT 'Datos completos del webhook en JSON',
  `fecha_recibido` datetime NOT NULL COMMENT 'Fecha y hora de recepción',
  `procesado` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=No procesado, 1=Procesado',
  `fecha_procesado` datetime DEFAULT NULL COMMENT 'Fecha y hora de procesamiento',
  PRIMARY KEY (`id`),
  KEY `idx_topic` (`topic`),
  KEY `idx_resource` (`resource_id`),
  KEY `idx_procesado` (`procesado`),
  KEY `idx_fecha` (`fecha_recibido`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Webhooks recibidos de MercadoPago';

-- =============================================
-- VERIFICAR COLUMNA estado_bloqueo EN TABLA clientes
-- Si no existe, se crea
-- =============================================
SET @dbname = DATABASE();
SET @tablename = 'clientes';
SET @columnname = 'estado_bloqueo';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD ', @columnname, ' TINYINT(1) NOT NULL DEFAULT 0 COMMENT "0=Activo, 1=Bloqueado por falta de pago"')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =============================================
-- MENSAJE DE CONFIRMACIÓN
-- =============================================
SELECT 'Instalación completada exitosamente' AS Resultado,
       'Tablas creadas: mercadopago_intentos, mercadopago_pagos, mercadopago_webhooks' AS Detalle;

-- =============================================
-- NOTAS IMPORTANTES:
-- =============================================
-- 1. Este script debe ejecutarse en la BASE DE DATOS MOON (remota)
-- 2. Las tablas usan InnoDB para soporte de transacciones
-- 3. Los índices están optimizados para consultas frecuentes
-- 4. La columna estado_bloqueo se agrega a la tabla clientes existente
-- 5. Todos los campos tienen comentarios descriptivos
-- =============================================
