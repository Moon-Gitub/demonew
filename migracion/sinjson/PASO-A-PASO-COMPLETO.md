# Paso a Paso Completo: Migración JSON a Tabla Relacional

Esta guía te lleva paso a paso por todo el proceso de migración desde el campo JSON `ventas.productos` a la tabla relacional `productos_venta`.

## 📋 Prerequisitos

1. **Backup de la base de datos** (OBLIGATORIO)
   ```bash
   mysqldump -u usuario -p nombre_base_datos > backup_antes_migracion.sql
   ```

2. **Acceso a MySQL/MariaDB** con permisos para:
   - CREATE TABLE
   - CREATE INDEX
   - ALTER TABLE
   - INSERT, SELECT

3. **Verificar que existen las tablas**:
   - `ventas`
   - `productos`

## 🚀 Opción 1: Script Automático (Recomendado)

### IMPORTANTE: Ejecutar en DOS pasos

**PASO 1:** Ejecutar el script maestro (crea estructura y procedimiento):
```bash
mysql -u tu_usuario -p tu_base_datos < migracion/sinjson/00-SCRIPT-MAESTRO-COMPLETO.sql
```

**PASO 2:** Ejecutar la migración (ejecuta el procedimiento):
```bash
mysql -u tu_usuario -p tu_base_datos < migracion/sinjson/EJECUTAR-MIGRACION.sql
```

**O desde phpMyAdmin:**
1. Ejecutar `00-SCRIPT-MAESTRO-COMPLETO.sql` completo
2. Luego ejecutar: `CALL migrar_ventas_pendientes_completo();`
3. Finalmente: `DROP PROCEDURE IF EXISTS migrar_ventas_pendientes_completo;`

Este proceso ejecuta:
- ✅ Crea la tabla `productos_venta`
- ✅ Crea todos los índices
- ✅ Crea las foreign keys
- ✅ Crea el procedimiento de migración
- ✅ Migra todas las ventas pendientes (PASO 2)
- ✅ Verifica resultados

## 📝 Opción 2: Ejecución Manual Paso a Paso

Si prefieres ejecutar cada paso manualmente para mayor control:

### PASO 1: Verificar Prerequisitos

```sql
-- Verificar que existe la tabla ventas
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'ventas';

-- Verificar que existe la tabla productos
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = DATABASE() AND table_name = 'productos';

-- Contar ventas con productos en JSON
SELECT COUNT(*) as ventas_con_json
FROM ventas 
WHERE productos IS NOT NULL 
AND productos != '' 
AND productos != '[]'
AND JSON_VALID(productos) = 1;
```

### PASO 2: Crear Tabla productos_venta

```sql
CREATE TABLE IF NOT EXISTS `productos_venta` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_venta` INT(11) NOT NULL,
  `id_producto` INT(11) NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_compra` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `precio_venta` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
```

**O ejecutar el archivo:**
```bash
mysql -u usuario -p base_datos < migracion/sinjson/crear-tabla-productos-venta.sql
```

### PASO 3: Crear Índices

```sql
-- Índice para búsquedas por venta
CREATE INDEX IF NOT EXISTS `idx_venta` ON `productos_venta` (`id_venta`);

-- Índice para búsquedas por producto
CREATE INDEX IF NOT EXISTS `idx_producto` ON `productos_venta` (`id_producto`);

-- Índice compuesto para búsquedas combinadas
CREATE INDEX IF NOT EXISTS `idx_venta_producto` ON `productos_venta` (`id_venta`, `id_producto`);

-- Índice para ordenamiento por fecha
CREATE INDEX IF NOT EXISTS `idx_created_at` ON `productos_venta` (`created_at`);
```

**Verificar índices creados:**
```sql
SHOW INDEX FROM productos_venta;
```

### PASO 4: Crear Foreign Keys

```sql
-- Deshabilitar temporalmente FK checks
SET FOREIGN_KEY_CHECKS = 0;

-- Crear FK a ventas
ALTER TABLE `productos_venta`
ADD CONSTRAINT `fk_productos_venta_venta` 
FOREIGN KEY (`id_venta`) 
REFERENCES `ventas` (`id`) 
ON DELETE CASCADE 
ON UPDATE CASCADE;

-- Crear FK a productos
ALTER TABLE `productos_venta`
ADD CONSTRAINT `fk_productos_venta_producto` 
FOREIGN KEY (`id_producto`) 
REFERENCES `productos` (`id`) 
ON DELETE RESTRICT 
ON UPDATE CASCADE;

-- Rehabilitar FK checks
SET FOREIGN_KEY_CHECKS = 1;
```

**Verificar foreign keys:**
```sql
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'productos_venta'
AND REFERENCED_TABLE_NAME IS NOT NULL;
```

### PASO 5: Diagnosticar Productos Inexistentes (Opcional)

Antes de migrar, puedes verificar si hay productos en JSON que no existen en la tabla `productos`:

```sql
-- Ver productos inexistentes
SELECT DISTINCT
    JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', 0, '].id'))) AS id_producto,
    v.id AS id_venta,
    v.codigo
FROM ventas v
WHERE v.productos IS NOT NULL 
AND JSON_VALID(v.productos) = 1
AND CAST(JSON_UNQUOTE(JSON_EXTRACT(v.productos, CONCAT('$[', 0, '].id'))) AS UNSIGNED) 
    NOT IN (SELECT id FROM productos)
LIMIT 10;
```

**O ejecutar el script completo:**
```bash
mysql -u usuario -p base_datos < migracion/sinjson/diagnosticar-productos-inexistentes.sql
```

### PASO 6: Migrar Ventas Pendientes

Tienes dos opciones:

#### Opción A: Migración con SQL

```bash
mysql -u usuario -p base_datos < migracion/sinjson/migrar-ventas-pendientes.sql
```

#### Opción B: Migración con PHP

```bash
cd migracion/sinjson
php migrar-ventas-pendientes.php
```

O desde navegador:
```
http://tu-dominio.com/migracion/sinjson/migrar-ventas-pendientes.php
```

### PASO 7: Verificación Final

```sql
-- 1. Total de productos migrados
SELECT COUNT(*) AS total_productos_venta FROM productos_venta;

-- 2. Ventas migradas
SELECT COUNT(DISTINCT id_venta) AS ventas_migradas FROM productos_venta;

-- 3. Ventas aún pendientes (debe ser 0)
SELECT COUNT(*) AS ventas_pendientes
FROM ventas v
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);

-- 4. Verificar integridad de totales
SELECT 
    pv.id_venta,
    v.codigo,
    COUNT(pv.id) as productos_migrados,
    SUM(pv.cantidad * pv.precio_venta) as total_calculado,
    v.total as total_venta,
    ABS(SUM(pv.cantidad * pv.precio_venta) - v.total) as diferencia
FROM productos_venta pv
INNER JOIN ventas v ON pv.id_venta = v.id
GROUP BY pv.id_venta, v.codigo, v.total
HAVING ABS(diferencia) > 0.01
LIMIT 20;
```

## 📊 Estructura Final de la Tabla

Después de ejecutar todos los pasos, la tabla `productos_venta` tendrá:

### Columnas:
- `id` (PRIMARY KEY, AUTO_INCREMENT)
- `id_venta` (INT, FK a ventas.id)
- `id_producto` (INT, FK a productos.id)
- `cantidad` (DECIMAL 10,2)
- `precio_compra` (DECIMAL 10,2)
- `precio_venta` (DECIMAL 10,2)
- `created_at` (TIMESTAMP)

### Índices:
- `PRIMARY` (id)
- `idx_venta` (id_venta)
- `idx_producto` (id_producto)
- `idx_venta_producto` (id_venta, id_producto)
- `idx_created_at` (created_at)

### Foreign Keys:
- `fk_productos_venta_venta` → `ventas.id` (CASCADE)
- `fk_productos_venta_producto` → `productos.id` (RESTRICT)

## ⚠️ Solución de Problemas

### Error: "Cannot add foreign key constraint"

**Causa:** Productos inexistentes o tipos de datos incompatibles.

**Solución:**
```sql
-- Deshabilitar FK temporalmente
SET FOREIGN_KEY_CHECKS = 0;
-- Ejecutar migración
-- Rehabilitar FK
SET FOREIGN_KEY_CHECKS = 1;
```

### Error: "Duplicate entry"

**Causa:** Ya existe el índice o FK.

**Solución:**
```sql
-- Eliminar y recrear
DROP INDEX idx_venta ON productos_venta;
CREATE INDEX idx_venta ON productos_venta (id_venta);
```

### Ventas no se migran

**Verificar:**
1. ¿Tienen productos en JSON válido?
2. ¿Los productos existen en la tabla productos?
3. ¿Ya están migradas?

```sql
-- Ver ventas pendientes
SELECT id, codigo, productos
FROM ventas
WHERE productos IS NOT NULL 
AND productos != '' 
AND productos != '[]'
AND JSON_VALID(productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta WHERE id_venta = ventas.id
)
LIMIT 10;
```

## ✅ Checklist Final

- [ ] Backup realizado
- [ ] Tabla `productos_venta` creada
- [ ] Índices creados (4 índices)
- [ ] Foreign keys creadas (2 FK)
- [ ] Ventas migradas
- [ ] Verificación completada
- [ ] 0 ventas pendientes
- [ ] Totales verificados

## 📁 Archivos del Proceso

1. `00-SCRIPT-MAESTRO-COMPLETO.sql` - **Ejecuta TODO automáticamente**
2. `crear-tabla-productos-venta.sql` - Solo crear tabla
3. `migrar-ventas-pendientes.sql` - Solo migrar ventas
4. `migrar-ventas-pendientes.php` - Migración con PHP
5. `diagnosticar-productos-inexistentes.sql` - Diagnosticar problemas

## 🎯 Resultado Esperado

Después de completar todos los pasos:

- ✅ Tabla `productos_venta` creada con estructura completa
- ✅ Todos los índices creados y funcionando
- ✅ Foreign keys establecidas correctamente
- ✅ Todas las ventas migradas (0 pendientes)
- ✅ Integridad de datos verificada
- ✅ Sistema listo para usar la nueva estructura

## 📞 Soporte

Si encuentras problemas:
1. Revisar los logs de MySQL
2. Verificar que los productos existen
3. Ejecutar el diagnóstico de productos inexistentes
4. Revisar el backup antes de continuar
