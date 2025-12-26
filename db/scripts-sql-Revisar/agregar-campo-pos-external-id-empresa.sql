-- ============================================
-- AGREGAR CAMPO POS EXTERNAL ID A TABLA EMPRESA
-- Este campo permite guardar el external_id del POS manualmente
-- para evitar depender de la API de tiendas
-- ============================================

-- Verificar si la columna ya existe antes de agregarla
SET @dbname = DATABASE();
SET @tablename = "empresa";
SET @columnname = "mp_pos_external_id";

-- Verificar y agregar mp_pos_external_id
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'La columna mp_pos_external_id ya existe' AS mensaje;",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " VARCHAR(255) DEFAULT NULL COMMENT 'External ID del POS de Mercado Pago (se puede obtener desde la app de MP)';")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SELECT 'Campo mp_pos_external_id agregado correctamente a la tabla empresa' AS resultado;
