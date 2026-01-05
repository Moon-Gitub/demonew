# Instrucciones para Migrar Ventas a productos_venta

Este documento explica cómo migrar las ventas que aún tienen productos en formato JSON a la nueva tabla relacional `productos_venta`.

## ¿Qué hace la migración?

La migración toma las ventas que:
- ✅ Tienen productos almacenados en el campo JSON `ventas.productos`
- ❌ NO tienen productos en la tabla `productos_venta`

Y las migra a la nueva estructura relacional.

## Opción 1: Migración con SQL (Recomendado)

### Paso 1: Verificar ventas pendientes

```sql
SELECT COUNT(*) as ventas_pendientes
FROM ventas v
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);
```

### Paso 2: Ejecutar el script de migración

```bash
mysql -u usuario -p nombre_base_datos < migrar-ventas-pendientes.sql
```

O desde MySQL:

```sql
source /ruta/completa/migrar-ventas-pendientes.sql;
```

### Paso 3: Verificar resultados

El script mostrará automáticamente:
- Resumen de migración
- Reporte detallado
- Ventas con problemas (si las hay)

## Opción 2: Migración con PHP

### Desde línea de comandos:

```bash
cd /ruta/del/proyecto/migracion/sinjson
php migrar-ventas-pendientes.php
```

### Desde navegador:

1. Acceder a: `http://tu-dominio.com/migracion/sinjson/migrar-ventas-pendientes.php`
2. El script mostrará el progreso y resultados

## Verificación Manual

### 1. Contar ventas migradas

```sql
SELECT COUNT(DISTINCT id_venta) as ventas_migradas 
FROM productos_venta;
```

### 2. Verificar que no queden pendientes

```sql
SELECT COUNT(*) as ventas_pendientes
FROM ventas v
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
AND JSON_VALID(v.productos) = 1
AND NOT EXISTS (
    SELECT 1 FROM productos_venta pv WHERE pv.id_venta = v.id
);
```

### 3. Verificar integridad de datos

```sql
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

## Migrar una venta específica

Si necesitas migrar solo una venta específica:

```sql
-- Cambiar el ID de la venta
SET @id_venta = 245690;

-- Ejecutar el script
source db/migrar-venta-especifica.sql;
```

## Solución de Problemas

### Error: "Producto no existe"

Si algunos productos no se migran porque no existen en la tabla `productos`:
- Verificar que el `id_producto` en el JSON sea correcto
- Los productos inexistentes se omiten automáticamente

### Error: "JSON inválido"

Si una venta tiene JSON inválido:
- Revisar manualmente el campo `ventas.productos`
- Corregir el JSON o migrar manualmente

### Diferencia en totales

Si hay diferencias entre el total calculado y el total de la venta:
- Puede ser por redondeos
- Verificar que los precios en JSON sean correctos

## Notas Importantes

1. **Backup**: Siempre hacer backup antes de migrar
2. **Transacciones**: La migración usa transacciones, si falla se revierte todo
3. **Idempotente**: Puedes ejecutar el script múltiples veces sin duplicar datos
4. **Solo pendientes**: Solo migra ventas que NO tienen productos_venta

## Archivos relacionados

- `migrar-ventas-pendientes.sql` - Script SQL principal
- `migrar-ventas-pendientes.php` - Script PHP alternativo
- `migrar-venta-especifica.sql` - Para migrar una venta específica
- `crear-tabla-productos-venta.sql` - Crear la tabla si no existe
