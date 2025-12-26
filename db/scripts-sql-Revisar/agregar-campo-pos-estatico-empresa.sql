-- ============================================
-- AGREGAR CAMPO POS ESTÁTICO A TABLA EMPRESA
-- ============================================

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = "empresa";
SET @columnname = "mp_pos_id";

-- Verificar y agregar mp_pos_id
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'La columna mp_pos_id ya existe' AS mensaje;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(255) DEFAULT NULL COMMENT 'POS ID de Mercado Pago para QR estático';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SELECT 'Campo mp_pos_id agregado correctamente a la tabla empresa' AS resultado;
