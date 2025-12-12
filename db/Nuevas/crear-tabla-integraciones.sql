-- SQL para crear la tabla de integraciones
-- Ejecutar en la base de datos del sistema

CREATE TABLE IF NOT EXISTS `integraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre de la integración',
  `tipo` varchar(50) NOT NULL COMMENT 'Tipo: n8n, api, webhook, etc.',
  `webhook_url` varchar(500) DEFAULT NULL COMMENT 'URL del webhook',
  `api_key` varchar(255) DEFAULT NULL COMMENT 'API Key si es necesario',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción de la integración',
  `activo` tinyint(1) DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de integraciones con servicios externos';

