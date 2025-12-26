-- ============================================
-- AGREGAR CAMPOS DE MERCADO PAGO A TABLA EMPRESA
-- ============================================

-- Verificar si las columnas ya existen antes de agregarlas
SET @dbname = DATABASE();
SET @tablename = "empresa";
SET @columnname1 = "mp_public_key";
SET @columnname2 = "mp_access_token";

-- Verificar y agregar mp_public_key
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname1)
  ) > 0,
  "SELECT 'La columna mp_public_key ya existe' AS mensaje;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname1, " VARCHAR(255) DEFAULT NULL COMMENT 'Public Key de Mercado Pago';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verificar y agregar mp_access_token
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname2)
  ) > 0,
  "SELECT 'La columna mp_access_token ya existe' AS mensaje;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname2, " VARCHAR(255) DEFAULT NULL COMMENT 'Access Token de Mercado Pago';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SELECT 'Campos de Mercado Pago agregados correctamente a la tabla empresa' AS resultado;
