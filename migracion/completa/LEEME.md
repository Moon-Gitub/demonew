# 🚀 Migración Completa: JSON a Tabla Relacional

Esta carpeta contiene **todos los scripts necesarios** para migrar los productos de ventas desde el formato JSON a la tabla relacional `productos_venta`.

## ⚠️ IMPORTANTE: ANTES DE EMPEZAR

**HACER BACKUP DE LA BASE DE DATOS** (OBLIGATORIO)

```bash
mysqldump -u tu_usuario -p nombre_base_datos > backup_antes_migracion.sql
```

O desde phpMyAdmin: Exportar → SQL → Ejecutar

---

## 📋 ¿Qué hace esta migración?

1. **Crea la tabla `productos_venta`** (si no existe)
2. **Crea índices** para optimizar búsquedas
3. **Crea foreign keys** para mantener integridad referencial
4. **Migra todos los productos** desde `ventas.productos` (JSON) a `productos_venta` (tabla relacional)
5. **Verifica** que todo se haya migrado correctamente

---

## 🎯 PASOS PARA EJECUTAR

### Opción 1: Desde Línea de Comandos (Recomendado)

```bash
# PASO 1: Crear estructura (tabla, índices, foreign keys, procedimiento)
mysql -u tu_usuario -p tu_base_datos < 01-CREAR-ESTRUCTURA.sql

# PASO 2: Ejecutar migración de datos
mysql -u tu_usuario -p tu_base_datos < 02-EJECUTAR-MIGRACION.sql
```

### Opción 2: Desde phpMyAdmin

1. **Abrir phpMyAdmin** y seleccionar tu base de datos
2. **Ir a la pestaña "SQL"**
3. **PASO 1:** Copiar y pegar todo el contenido de `01-CREAR-ESTRUCTURA.sql` y ejecutar
4. **PASO 2:** Copiar y pegar todo el contenido de `02-EJECUTAR-MIGRACION.sql` y ejecutar
5. **PASO 3 (SOLO si la tabla ya existía sin PRIMARY KEY):** Ejecutar `03-FIX-PRIMARY-KEY.sql`

---

## 📁 Archivos en esta carpeta

| Archivo | Descripción |
|---------|-------------|
| `01-CREAR-ESTRUCTURA.sql` | Crea la tabla, índices, foreign keys y el procedimiento de migración |
| `02-EJECUTAR-MIGRACION.sql` | Ejecuta la migración de datos y limpia el procedimiento |
| `03-FIX-PRIMARY-KEY.sql` | **SOLO si la tabla ya existe sin PRIMARY KEY**: Corrige el PRIMARY KEY y asigna ids únicos |
| `LEEME.md` | Este archivo con las instrucciones |

---

## ✅ Verificación después de la migración

Después de ejecutar ambos scripts, puedes verificar que todo funcionó:

```sql
-- Ver total de productos migrados
SELECT COUNT(*) AS total_productos_migrados FROM productos_venta;

-- Ver ventas migradas
SELECT COUNT(DISTINCT id_venta) AS ventas_migradas FROM productos_venta;

-- Ver si quedan ventas pendientes
SELECT COUNT(*) AS ventas_pendientes
FROM ventas v 
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1 
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);
```

---

## ❓ Problemas Comunes

### Problema: Tabla `productos_venta` sin PRIMARY KEY o con `id = 0`
- **Causa:** La tabla fue creada anteriormente sin PRIMARY KEY o el AUTO_INCREMENT no funcionó
- **Solución:** Ejecutar `03-FIX-PRIMARY-KEY.sql` que:
  - Detecta registros con `id = 0`
  - Asigna ids únicos incrementales
  - Agrega PRIMARY KEY si falta
  - Configura AUTO_INCREMENT correctamente

### Error: "Table 'ventas' doesn't exist"
- **Causa:** La tabla `ventas` no existe en tu base de datos
- **Solución:** El script omitirá la creación de foreign keys, pero continuará creando la estructura

### Error: "Commands out of sync"
- **Causa:** Ejecutaste ambos scripts en una sola ejecución
- **Solución:** Ejecuta `01-CREAR-ESTRUCTURA.sql` primero, espera a que termine, luego ejecuta `02-EJECUTAR-MIGRACION.sql`

### Error: "Foreign key constraint fails"
- **Causa:** Hay productos en JSON que no existen en la tabla `productos`
- **Solución:** El script omitirá esos productos automáticamente. Revisa el resumen final para ver cuántos productos se omitieron

---

## 📊 Resultado Esperado

Después de la migración exitosa, deberías ver:

- ✅ Tabla `productos_venta` creada
- ✅ Índices creados (idx_venta, idx_producto, idx_venta_producto, idx_created_at)
- ✅ Foreign keys creadas (fk_productos_venta_venta, fk_productos_venta_producto)
- ✅ Resumen mostrando: ventas_migradas, productos_migrados, productos_omitidos
- ✅ 0 ventas pendientes (o un número bajo si hay productos inexistentes)

---

## 🔄 ¿Qué pasa con los datos JSON antiguos?

- Los datos JSON en `ventas.productos` **NO se eliminan** automáticamente
- Puedes mantenerlos como respaldo o eliminarlos manualmente después de verificar que la migración fue exitosa
- La aplicación ahora usa la tabla `productos_venta` en lugar del JSON

---

## 📞 Soporte

Si encuentras algún problema:
1. Verifica que hiciste el backup
2. Revisa los mensajes de error en detalle
3. Consulta la documentación completa en `../sinjson/PASO-A-PASO-COMPLETO.md`

---

**¡Buena suerte con la migración! 🎉**
