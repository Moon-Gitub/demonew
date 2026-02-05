# Errores más comunes al migrar y sus soluciones

Referencia rápida para migraciones de base de datos (estructura y datos) del sistema NewMoon.

---

## 1. #1062 - Entrada duplicada '0' para la clave 'PRIMARY'

**Qué significa:** Se intenta insertar (o MySQL asigna) un valor `0` en la clave primaria y ya existe una fila con ese valor, o la clave no acepta duplicados.

**Causas habituales:**
- La tabla no tiene `AUTO_INCREMENT` en la columna `id` y se inserta sin valor (queda 0).
- Ya existe una fila con `id = 0` y el siguiente insert vuelve a usar 0 (p. ej. por `NO_AUTO_VALUE_ON_ZERO`).
- Hay una venta o registro con `id = 0` y se usa como referencia en otra tabla (ej. `productos_venta.id_venta = 0`).

**Soluciones:**
- Asegurar que la columna de clave primaria sea `AUTO_INCREMENT`:
  ```sql
  ALTER TABLE nombre_tabla MODIFY COLUMN id INT NOT NULL AUTO_INCREMENT;
  ```
- Si ya hay fila con `id = 0`, corregir o eliminar ese registro y luego ajustar el auto_increment:
  ```sql
  -- Ver si existe id = 0
  SELECT * FROM nombre_tabla WHERE id = 0;
  -- Si corresponde, actualizar o borrar; luego:
  ALTER TABLE nombre_tabla AUTO_INCREMENT = 1;
  ```
- Revisar `@@sql_mode`: si incluye `NO_AUTO_VALUE_ON_ZERO`, en inserts sin `id` se usa 0. Puedes quitarlo para la sesión:
  ```sql
  SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, 'NO_AUTO_VALUE_ON_ZERO', ''));
  ```

---

## 2. #1062 - Entrada duplicada '' para la clave 'uuid' (o otro índice UNIQUE)

**Qué significa:** Se intenta poner `NOT NULL` o crear un índice UNIQUE sobre una columna que tiene valores vacíos o NULL repetidos (varios registros con `''` o `NULL`).

**Causas habituales:**
- Columna `uuid` (o similar) con NULL o `''` en varias filas; al hacer `ALTER TABLE ... MODIFY uuid VARCHAR(34) NOT NULL` o `ADD UNIQUE KEY uuid (uuid)` falla.

**Soluciones:**
- Rellenar valores vacíos/NULL con un valor único antes del ALTER:
  ```sql
  UPDATE ventas SET uuid = REPLACE(UUID(), '-', '') WHERE uuid IS NULL OR TRIM(IFNULL(uuid, '')) = '';
  -- Luego el MODIFY y ADD UNIQUE KEY
  ```
- Si la columna tiene longitud limitada (ej. VARCHAR(34)), usar por ejemplo:
  ```sql
  UPDATE ventas SET uuid = LEFT(REPLACE(UUID(), '-', ''), 34) WHERE uuid IS NULL OR TRIM(IFNULL(uuid, '')) = '';
  ```

---

## 3. #1005 - Can't create table (errno: 150) / Foreign key constraint is incorrectly formed

**Qué significa:** No se puede crear una tabla o índice por una restricción de clave foránea mal definida.

**Causas habituales:**
- Tipos o collation distintos entre la columna referenciada y la que referencia.
- La tabla o columna referenciada no existe aún.
- La columna referenciada no es PRIMARY KEY o UNIQUE.
- Orden de creación: se crea la tabla con FK antes que la tabla referenciada.

**Soluciones:**
- Crear primero las tablas “padre” (sin FK) y luego las que tienen FK.
- Asegurar que tipos coincidan (ej. `INT` con `INT`, no `INT` con `BIGINT` salvo que sea intencional).
- Desactivar FKs durante la migración si es necesario, y reactivarlas al final:
  ```sql
  SET FOREIGN_KEY_CHECKS = 0;
  -- ... tus INSERT / CREATE ...
  SET FOREIGN_KEY_CHECKS = 1;
  ```

---

## 4. #1146 - Table 'xxx' doesn't exist

**Qué significa:** Un script o procedimiento referencia una tabla que aún no existe en esa base de datos.

**Causas habituales:**
- Orden de ejecución: se ejecuta un script que depende de tablas creadas en otro script que no se corrió (o se corrió después).
- Nombre de base de datos o prefijo distinto.
- Tabla con nombre distinto en esta instalación (ej. mayúsculas/minúsculas en Linux).

**Soluciones:**
- Ejecutar los scripts en el orden indicado (ej. “00-SCRIPT-MAESTRO” antes que “migración de ventas”).
- Comprobar que la tabla exista: `SHOW TABLES LIKE 'nombre_tabla';`
- Revisar que el nombre de la tabla en el script coincida con el de la BD (incluyendo mayúsculas/minúsculas).

---

## 5. #1215 - Cannot add foreign key constraint

**Qué significa:** No se puede añadir la clave foránea (mismo tipo de problema que el #1005 pero en un `ALTER TABLE`).

**Causas habituales:**
- Datos en la columna que no existen en la tabla referenciada (ej. `id_venta` con valores que no están en `ventas.id`).
- Tipos/collation distintos entre columnas.
- Motor de tabla distinto (ej. MyISAM vs InnoDB).

**Soluciones:**
- Limpiar o corregir datos huérfanos antes de crear la FK:
  ```sql
  -- Ejemplo: eliminar filas de productos_venta con id_venta inexistente
  DELETE FROM productos_venta WHERE id_venta NOT IN (SELECT id FROM ventas);
  ```
- Unificar tipos (INT, UNSIGNED, etc.) y motor (InnoDB) en ambas tablas.
- Crear la FK después de que los datos sean consistentes.

---

## 6. #1366 - Incorrect string value / problemas de caracteres (ñ, tildes, emojis)

**Qué significa:** El valor que se intenta guardar no es válido para la codificación de la columna o de la conexión.

**Causas habituales:**
- Columna o tabla con `latin1` y datos en UTF-8 (o al revés).
- Conexión sin `utf8mb4` y datos con emojis o caracteres de 4 bytes.

**Soluciones:**
- Usar `utf8mb4` en tablas y conexión:
  ```sql
  ALTER TABLE nombre_tabla CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```
- En la conexión (PHP/config): `charset=utf8mb4`.
- Al importar dump: `mysqldump ... --default-character-set=utf8mb4` y que el servidor/cliente usen utf8mb4.

---

## 7. Timeout / script que se corta o tarda mucho

**Qué significa:** La migración hace muchas filas o consultas pesadas y la conexión o el servidor cortan por tiempo o memoria.

**Soluciones:**
- Ejecutar en lotes (por rangos de `id` o por fecha).
- Aumentar timeouts en el cliente (phpMyAdmin, MySQL client) o en `my.cnf` (`max_execution_time`, `wait_timeout`, etc.).
- Desactivar índices/FK durante los INSERT masivos y recrearlos al final (si el script lo permite o lo adaptas).
- Usar la línea de comandos de MySQL para scripts muy grandes en lugar de phpMyAdmin.

---

## 8. Procedimiento almacenado no existe al hacer CALL

**Qué significa:** Se ejecuta `CALL nombre_procedimiento();` pero el procedimiento no está creado en esa base de datos.

**Causas habituales:**
- El script que crea el procedimiento (`CREATE PROCEDURE ...`) no se ejecutó o falló antes.
- Se ejecutó en otra base de datos.
- El script que hace `DROP PROCEDURE` se ejecutó después del `CALL` y luego se intenta llamar de nuevo.

**Soluciones:**
- Ejecutar primero el script completo que contiene `CREATE PROCEDURE ...` (ej. 00-SCRIPT-MAESTRO-COMPLETO.sql).
- Comprobar que el procedimiento exista: `SHOW PROCEDURE STATUS WHERE Db = 'nombre_bd';`
- No ejecutar solo el fragmento con `CALL` y `DROP PROCEDURE` sin haber creado antes el procedimiento en esa misma BD.

---

## 9. Columna no existe / Unknown column 'xxx' in 'field list'

**Qué significa:** Un INSERT o UPDATE usa una columna que no existe en la tabla (o el nombre está mal escrito).

**Causas habituales:**
- La estructura de la tabla en esta BD es más vieja y le falta la columna (o es de otra versión del sistema).
- Nombre de columna distinto (mayúsculas, guiones, otro idioma).

**Soluciones:**
- Revisar la estructura: `DESCRIBE nombre_tabla;` o `SHOW CREATE TABLE nombre_tabla;`
- Añadir la columna si debe existir: `ALTER TABLE nombre_tabla ADD COLUMN xxx ...;`
- Ajustar el script de migración para que no use esa columna en esta BD o use un nombre/alias correcto.

---

## 10. Listas de precio / multi-empresa: solo funcionan para empresa 1

**Qué significa:** Usuarios con `empresa = 2, 3, 4` no ven listas de precio o fallan ventas porque en `listas_precio` solo hay filas con `id_empresa = 1`.

**Soluciones:**
- Replicar listas de empresa 1 al resto de empresas que tengan usuarios:
  ```sql
  -- Ver script: db/replicar-listas-precio-por-empresa.sql
  ```
- En código existe fallback: si la empresa no tiene listas, se usan las de empresa 1 (modelo listas_precio). Opcionalmente ejecutar el script anterior para tener datos por empresa.

---

## Resumen rápido

| Error / síntoma                         | Revisar primero                                      |
|----------------------------------------|------------------------------------------------------|
| Duplicado '0' en PRIMARY               | AUTO_INCREMENT en `id`, filas con `id = 0`, sql_mode |
| Duplicado '' en UNIQUE (uuid, etc.)     | NULL/vacíos en columna; rellenar antes del ALTER     |
| Foreign key incorrectly formed         | Orden de tablas, tipos, collation, datos huérfanos  |
| Table doesn't exist                    | Orden de scripts, nombre de tabla y de BD           |
| Unknown column                         | DESCRIBE / SHOW CREATE TABLE; añadir columna o script|
| Caracteres raros / Incorrect string     | Charset/collation utf8mb4 en tabla y conexión       |
| Timeout / script cortado               | Lotes, timeouts, ejecutar por consola                |
| Procedure no existe                    | Ejecutar antes el script que hace CREATE PROCEDURE  |

Si querés ampliar este documento con errores concretos que te aparezcan, se pueden añadir nuevas secciones con el mismo formato (error, causa, solución).
