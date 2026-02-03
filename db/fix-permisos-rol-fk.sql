-- ================================================================
-- Arreglar FK permisos_rol -> pantallas
-- ================================================================
--
-- Si te da error #1062 "duplicate entry '61' for key 'PRIMARY'" al hacer
-- MODIFY id AUTO_INCREMENT, es porque en pantallas hay ids duplicados o
-- inconsistentes. Usar PLAN B.
--
-- ========== PLAN B (cuando hay error 1062: duplicate 61) ==========
-- 1) Quitar la FK (si existe), para poder vaciar las tablas:
--    ALTER TABLE permisos_rol DROP FOREIGN KEY fk_permisos_rol_pantalla;
--    (Si da error "check that column/key exists", ignorar.)
--
-- 2) Vaciar y volver a cargar con datos correctos (ids 1, 2, 3... 61):
--    DELETE FROM permisos_rol;
--    DELETE FROM pantallas;
--    Luego ejecutar SOLO la parte de pantallas y permisos_rol del archivo
--    tablas-con-datos-listas-medios-pantallas-permisos.sql (INSERT pantallas
--    con id explícitos 1-61, luego INSERT permisos_rol).
--
-- 3) Dejar el contador AUTO_INCREMENT listo para futuras filas:
--    ALTER TABLE pantallas AUTO_INCREMENT = 62;
--
-- 4) Si la FK no existía, crearla:
--    ALTER TABLE permisos_rol
--    ADD CONSTRAINT fk_permisos_rol_pantalla
--    FOREIGN KEY (id_pantalla) REFERENCES pantallas (id) ON DELETE CASCADE;
--
-- ========== PLAN A (datos ya correctos, solo tipos o FK) ==========

-- 1) Ver si hay id_pantalla en permisos_rol que NO existan en pantallas
--    (si devuelve filas, esos son los huérfanos)
-- SELECT pr.rol, pr.id_pantalla
-- FROM permisos_rol pr
-- LEFT JOIN pantallas p ON p.id = pr.id_pantalla
-- WHERE p.id IS NULL;

-- 2) Borrar permisos de rol que apunten a pantallas inexistentes
DELETE pr FROM permisos_rol pr
LEFT JOIN pantallas p ON p.id = pr.id_pantalla
WHERE p.id IS NULL;

-- 3) Asegurar que tipos coincidan (mismo tipo que pantallas.id)
ALTER TABLE `permisos_rol`
  MODIFY `id_pantalla` INT(11) NOT NULL;

-- En MySQL la columna AUTO_INCREMENT debe ser clave (PRIMARY KEY).
-- Ejecutar UNA de estas dos líneas:
--   A) Si id aún NO es clave: (define id como PK y auto_increment)
ALTER TABLE `pantallas`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY;
--   B) Si ya da error "Duplicate primary key", entonces id ya es PK; solo hacer:
-- ALTER TABLE `pantallas` MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT;

-- 4) Si la FK ya existe, quitarla; luego la volvemos a crear.
--    (Si da error "check that column/key exists", la FK no estaba: ignorar este paso.)
ALTER TABLE `permisos_rol`
  DROP FOREIGN KEY `fk_permisos_rol_pantalla`;

-- 5) Crear la FK de nuevo
ALTER TABLE `permisos_rol`
  ADD CONSTRAINT `fk_permisos_rol_pantalla`
  FOREIGN KEY (`id_pantalla`) REFERENCES `pantallas` (`id`) ON DELETE CASCADE;
