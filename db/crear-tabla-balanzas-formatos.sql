-- ================================================================
-- Tabla balanzas_formatos: configuración de tickets de balanza
-- ================================================================
-- Permite definir, por empresa, cómo se interpreta un código de
-- balanza (prefijo, posiciones de producto y peso/cantidad, etc.).
-- El frontend lee esta tabla y aplica las reglas sin hardcodear
-- posiciones en el JavaScript.
--
-- NOTA:
-- - Las posiciones se guardan en base 0 (primer carácter = 0),
--   porque así trabaja JavaScript con substr().
-- - longitud_* es la cantidad de caracteres a tomar.
-- - modo_cantidad:
--     * 'peso'   -> se toma substring y se divide por factor_divisor
--     * 'unidad' -> usa cantidad_fija (por defecto 1)
--     * 'ninguno'-> usa la cantidad ingresada manualmente
-- ================================================================

CREATE TABLE IF NOT EXISTS `balanzas_formatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_empresa` int(11) NOT NULL DEFAULT 1,
  `nombre` varchar(128) NOT NULL,
  `prefijo` varchar(32) NOT NULL COMMENT 'Prefijo que debe tener el código (ej: 20, 21, 20000)',
  `longitud_min` int(11) DEFAULT NULL,
  `longitud_max` int(11) DEFAULT NULL,
  `pos_producto` int(11) NOT NULL DEFAULT 0 COMMENT 'Posición inicial (0-based) del id de producto en el código',
  `longitud_producto` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de caracteres del id de producto',
  `modo_cantidad` varchar(16) NOT NULL DEFAULT 'ninguno' COMMENT 'peso | unidad | ninguno',
  `pos_cantidad` int(11) DEFAULT NULL COMMENT 'Posición inicial (0-based) del campo cantidad/peso',
  `longitud_cantidad` int(11) DEFAULT NULL COMMENT 'Cantidad de caracteres del campo cantidad/peso',
  `factor_divisor` decimal(10,4) NOT NULL DEFAULT 1.0000 COMMENT 'Divisor para pasar gramos a kg, etc.',
  `cantidad_fija` decimal(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Cantidad fija cuando modo_cantidad = unidad',
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_balanzas_empresa_activo` (`id_empresa`,`activo`,`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- Ejemplos iniciales (empresa 1) basados en lógica actual:
--
-- 1) Códigos que empiezan con 20000:
--    - prefijo: 20000
--    - idProducto: posiciones 5-6 (2 dígitos)
--    - peso: posiciones 7-11 (5 dígitos), dividido por 1000 -> kg
--
-- 2) Códigos que empiezan con 20 (pero no 20000):
--    - prefijo: 20
--    - idProducto: posiciones 4-5 (2 dígitos)
--    - peso: posiciones 7-11 (5 dígitos), dividido por 1000 -> kg
--
-- 3) Códigos que empiezan con 21:
--    - prefijo: 21
--    - idProducto: posiciones 4-5 (2 dígitos)
--    - cantidad fija = 1 unidad
-- ================================================================

INSERT IGNORE INTO `balanzas_formatos`
(`id_empresa`, `nombre`,                         `prefijo`, `longitud_min`, `longitud_max`,
 `pos_producto`, `longitud_producto`,
 `modo_cantidad`, `pos_cantidad`, `longitud_cantidad`, `factor_divisor`, `cantidad_fija`,
 `orden`, `activo`)
VALUES
(1, 'Balanza 20000 (peso en kg)',                '20000', 12, 20,
  5, 2,
  'peso', 7, 5, 1000.0000, 1.000,
  10, 1),
(1, 'Balanza 20 (peso en kg, genérica)',        '20',    12, 20,
  4, 2,
  'peso', 7, 5, 1000.0000, 1.000,
  20, 1),
(1, 'Balanza 21 (unidad, cantidad fija = 1)',   '21',    8,  20,
  4, 2,
  'unidad', NULL, NULL, 1.0000, 1.000,
  30, 1);

