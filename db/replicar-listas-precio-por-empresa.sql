-- ================================================================
-- Replicar listas de precio de empresa 1 al resto de empresas
-- ================================================================
-- Problema: Si en listas_precio solo hay filas con id_empresa = 1,
-- los usuarios con empresa 2, 3, 4... no encuentran listas y
-- multi-sucursal / multi-empresa no funciona bien en ventas.
--
-- Este script copia las listas de id_empresa = 1 a cada otra
-- empresa que tenga al menos un usuario (usuarios.empresa),
-- sin duplicar (id_empresa, codigo) que ya existan.
--
-- Ejecutar: mysql -u USUARIO -p NOMBRE_BD < db/replicar-listas-precio-por-empresa.sql
-- O pegar en phpMyAdmin.
-- ================================================================

-- Copiar listas de empresa 1 a cada empresa que existe en usuarios
-- y que aún no tenga esa lista (evita duplicados por uq_listas_precio_empresa_codigo)
INSERT INTO listas_precio (id_empresa, codigo, nombre, base_precio, tipo_descuento, valor_descuento, orden, activo)
SELECT u.empresa, l.codigo, l.nombre, l.base_precio, l.tipo_descuento, l.valor_descuento, l.orden, l.activo
FROM (SELECT DISTINCT empresa FROM usuarios WHERE empresa IS NOT NULL AND empresa != 1) u
CROSS JOIN listas_precio l
WHERE l.id_empresa = 1
  AND NOT EXISTS (
    SELECT 1 FROM listas_precio lp
    WHERE lp.id_empresa = u.empresa AND lp.codigo = l.codigo
  );

-- Opcional: ver cuántas filas se insertaron (ejecutar aparte si quieres)
-- SELECT ROW_COUNT() AS filas_insertadas;
