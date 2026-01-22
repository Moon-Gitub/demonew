-- =====================================================
-- TABLA: medios_pago
-- Descripción: Almacena los medios de pago personalizados
-- =====================================================

CREATE TABLE IF NOT EXISTS `medios_pago` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(10) NOT NULL COMMENT 'Código único del medio (ej: EF, TD, TC)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre descriptivo (ej: Efectivo, Tarjeta Débito)',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción opcional',
  `activo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `requiere_codigo` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere código de transacción',
  `requiere_banco` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere banco',
  `requiere_numero` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere número de referencia',
  `requiere_fecha` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si requiere fecha de vencimiento',
  `orden` int(11) NOT NULL DEFAULT 0 COMMENT 'Orden de visualización',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

-- =====================================================
-- INSERTAR MEDIOS DE PAGO POR DEFECTO
-- =====================================================

INSERT INTO `medios_pago` (`codigo`, `nombre`, `descripcion`, `activo`, `requiere_codigo`, `requiere_banco`, `requiere_numero`, `requiere_fecha`, `orden`) VALUES
('EF', 'Efectivo', 'Pago en efectivo', 1, 0, 0, 0, 0, 1),
('TD', 'Tarjeta Débito', 'Pago con tarjeta de débito', 1, 1, 0, 0, 0, 2),
('TC', 'Tarjeta Crédito', 'Pago con tarjeta de crédito', 1, 1, 0, 0, 0, 3),
('CH', 'Cheque', 'Pago con cheque', 1, 0, 1, 1, 1, 4),
('TR', 'Transferencia', 'Transferencia bancaria', 1, 0, 1, 1, 0, 5),
('CC', 'Cuenta Corriente', 'Pago a cuenta corriente', 1, 0, 0, 0, 0, 6);

-- NOTA: MercadoPago QR (MPQR) NO se agrega aquí porque es fijo y se maneja por separado
