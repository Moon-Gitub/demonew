-- =============================================
-- SCRIPT DE VERIFICACIÓN DE INSTALACIÓN
-- Sistema de Cobro Moon POS
-- =============================================

-- Verificar que existan las tablas
SELECT
    'VERIFICACIÓN DE TABLAS' AS Verificacion,
    '' AS Tabla,
    '' AS Estado,
    '' AS Comentario
UNION ALL
SELECT
    '',
    'mercadopago_intentos',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado,
    'Tabla para registrar intentos de pago'
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'mercadopago_intentos'
UNION ALL
SELECT
    '',
    'mercadopago_pagos',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado,
    'Tabla para registrar pagos confirmados'
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'mercadopago_pagos'
UNION ALL
SELECT
    '',
    'mercadopago_webhooks',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado,
    'Tabla para registrar webhooks recibidos'
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'mercadopago_webhooks'
UNION ALL
SELECT
    '',
    'clientes',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado,
    'Tabla principal de clientes'
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'clientes'
UNION ALL
SELECT
    '',
    'clientes_cuenta_corriente',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado,
    'Tabla de cuenta corriente de clientes'
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'clientes_cuenta_corriente';

-- Verificar columnas importantes
SELECT
    'VERIFICACIÓN DE COLUMNAS' AS Verificacion,
    '' AS Tabla,
    '' AS Columna,
    '' AS Estado
UNION ALL
SELECT
    '',
    'clientes',
    'estado_bloqueo',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'clientes'
  AND column_name = 'estado_bloqueo'
UNION ALL
SELECT
    '',
    'clientes_cuenta_corriente',
    'tipo',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'clientes_cuenta_corriente'
  AND column_name = 'tipo'
UNION ALL
SELECT
    '',
    'clientes',
    'aplicar_recargos',
    IF(COUNT(*) > 0, '✓ OK', '✗ FALTA') AS Estado
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'clientes'
  AND column_name = 'aplicar_recargos';

-- Contar registros en las tablas nuevas
SELECT
    'ESTADÍSTICAS DE TABLAS' AS Estadistica,
    '' AS Tabla,
    '' AS Total_Registros,
    '' AS Comentario
UNION ALL
SELECT
    '',
    'mercadopago_intentos',
    CAST(COUNT(*) AS CHAR) AS Total,
    'Intentos de pago registrados'
FROM mercadopago_intentos
UNION ALL
SELECT
    '',
    'mercadopago_pagos',
    CAST(COUNT(*) AS CHAR) AS Total,
    'Pagos confirmados registrados'
FROM mercadopago_pagos
UNION ALL
SELECT
    '',
    'mercadopago_webhooks',
    CAST(COUNT(*) AS CHAR) AS Total,
    'Webhooks recibidos'
FROM mercadopago_webhooks;

-- =============================================
-- Si todas las verificaciones muestran ✓ OK,
-- la instalación fue exitosa
-- =============================================
