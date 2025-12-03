-- =============================================
-- ACTUALIZACIÓN: Agregar control de recargos por cliente
-- =============================================

-- Agregar columna aplicar_recargos a la tabla clientes
-- 1 = SÍ aplicar recargos por mora
-- 0 = NO aplicar recargos (cliente exento)
ALTER TABLE `clientes`
ADD COLUMN `aplicar_recargos` TINYINT(1) NOT NULL DEFAULT 1
COMMENT '1=Aplica recargos por mora, 0=Exento de recargos'
AFTER `estado_bloqueo`;

-- Mensaje de confirmación
SELECT 'Columna aplicar_recargos agregada exitosamente' AS Resultado,
       'Ahora puedes controlar por cliente si se aplican o no los recargos por mora' AS Detalle;

-- =============================================
-- NOTAS:
-- =============================================
-- Por defecto todos los clientes tienen aplicar_recargos = 1 (SÍ aplica)
-- Para eximir a un cliente de recargos:
--   UPDATE clientes SET aplicar_recargos = 0 WHERE id = [id_del_cliente];
-- Para volver a aplicar recargos:
--   UPDATE clientes SET aplicar_recargos = 1 WHERE id = [id_del_cliente];
-- =============================================
