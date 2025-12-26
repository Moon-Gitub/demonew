# Pasos para Aplicar Cambios: JSON a Tabla Relacional + Optimización Dashboard

## 📋 Resumen de Cambios

Este documento describe los pasos necesarios para aplicar **todos los cambios** relacionados con:
1. Migración de JSON a tabla relacional `productos_venta`
2. Eliminación completa del uso del campo JSON `productos`
3. Optimización máxima del dashboard

## ⚠️ IMPORTANTE: Orden de Ejecución

**DEBES seguir estos pasos en el orden indicado** para evitar errores.

---

## 📦 PASO 1: Crear la Tabla Relacional

### 1.1 Ejecutar Script de Creación
```sql
SOURCE migracion/sinjson/crear-tabla-productos-venta.sql;
```

### 1.2 Verificar Creación
```sql
SHOW TABLES LIKE 'productos_venta';
DESCRIBE productos_venta;
```

**Resultado esperado**: Debe mostrar la tabla con todas sus columnas, índices y FOREIGN KEYs.

---

## 📦 PASO 2: Migrar Datos Existentes

### 2.1 (Opcional) Diagnosticar Productos Inexistentes
Si quieres saber qué productos pueden causar problemas:
```sql
SOURCE migracion/sinjson/diagnosticar-productos-inexistentes.sql;
```

Revisa los resultados y decide si:
- Corregir productos inexistentes en el JSON
- Omitirlos durante la migración
- Migrarlos sin validación FK

### 2.2 Ejecutar Migración

**Opción A - Con Validación (Recomendado)**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta.sql;
```

**Opción B - Sin Validación FK (Solo si hay productos inexistentes)**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta-sin-fk.sql;
```

### 2.3 Verificar Migración
```sql
-- Contar productos migrados
SELECT COUNT(*) as total_productos_venta FROM productos_venta;

-- Comparar con ventas
SELECT 
    COUNT(DISTINCT id_venta) as ventas_migradas,
    COUNT(*) as productos_migrados
FROM productos_venta;

-- Verificar integridad (debe retornar 0 o muy pocos registros)
SELECT 
    pv.id_venta,
    SUM(pv.cantidad * pv.precio_venta) as total_calculado,
    v.total as total_venta,
    ABS(SUM(pv.cantidad * pv.precio_venta) - v.total) as diferencia
FROM productos_venta pv
INNER JOIN ventas v ON pv.id_venta = v.id
GROUP BY pv.id_venta, v.total
HAVING ABS(diferencia) > 0.01
LIMIT 10;
```

---

## 📦 PASO 3: Optimizar Índices del Dashboard

### 3.1 Ejecutar Script de Índices
```sql
SOURCE db/optimizar-indices-dashboard.sql;
```

### 3.2 Verificar Índices
```sql
SHOW INDEX FROM ventas;
SHOW INDEX FROM productos_venta;
```

**Debes ver**:
- `idx_fecha_cbte_tipo` en `ventas`
- `idx_producto_cantidad` en `productos_venta`

---

## 📦 PASO 4: Actualizar Código (Git)

### 4.1 Actualizar desde GitHub
```bash
cd /ruta/a/tu/proyecto
git pull origin main
```

### 4.2 Verificar Archivos Actualizados
Los siguientes archivos deben estar actualizados:
- ✅ `modelos/ventas.modelo.php`
- ✅ `modelos/productos.modelo.php`
- ✅ `controladores/ventas.controlador.php`
- ✅ `controladores/productos.controlador.php`
- ✅ `vistas/modulos/inicio/cajas-superiores.php`
- ✅ `vistas/modulos/reportes/productos-mas-vendidos.php`
- ✅ Y todos los demás archivos mencionados en `IMPLEMENTACION-PRODUCTOS-VENTA.md`

---

## 📦 PASO 5: Probar Funcionalidad

### 5.1 Probar Dashboard
1. Acceder a la página de inicio
2. Verificar que las cajas de estadísticas cargan correctamente
3. Verificar que el gráfico de ventas se muestra
4. Verificar que "Productos más vendidos" se muestra

**Tiempo esperado**: El dashboard debe cargar **70-80% más rápido** que antes.

### 5.2 Probar Crear Venta
1. Crear una nueva venta desde `crear-venta-caja`
2. Verificar que se guarda correctamente
3. Verificar en la BD que:
   - Se insertó en `ventas` (campo `productos` = `'[]'`)
   - Se insertó en `productos_venta` con todos los productos

```sql
-- Verificar última venta creada
SELECT v.id, v.codigo, v.productos, COUNT(pv.id) as productos_en_tabla
FROM ventas v
LEFT JOIN productos_venta pv ON v.id = pv.id_venta
ORDER BY v.id DESC
LIMIT 1;
```

### 5.3 Probar Editar Venta
1. Editar una venta existente
2. Cambiar productos
3. Guardar
4. Verificar que se actualizó en `productos_venta`

### 5.4 Probar Generar PDFs
1. Generar un comprobante (cualquier tipo)
2. Verificar que los productos se muestran correctamente
3. Probar con diferentes tipos de comprobantes

### 5.5 Probar Reportes
1. Acceder a "Ventas por Productos"
2. Acceder a "Rentabilidad"
3. Acceder a "Categorías/Proveedores"
4. Verificar que todos cargan correctamente

---

## 📦 PASO 6: Verificar Rendimiento

### 6.1 Medir Tiempo de Carga del Dashboard
**Antes de los cambios**: Anotar tiempo de carga
**Después de los cambios**: Comparar tiempo de carga

**Mejora esperada**: 70-80% más rápido

### 6.2 Verificar Consultas SQL
Activar logging de consultas SQL (si es posible) y verificar:
- Menos consultas totales
- Consultas más rápidas
- Uso de índices

---

## 🔍 Verificación Final

### Checklist de Verificación

- [ ] Tabla `productos_venta` creada correctamente
- [ ] Datos migrados (verificar conteo)
- [ ] Índices creados
- [ ] Código actualizado desde GitHub
- [ ] Dashboard carga correctamente
- [ ] Crear venta funciona
- [ ] Editar venta funciona
- [ ] PDFs se generan correctamente
- [ ] Reportes funcionan
- [ ] Rendimiento mejorado

---

## 🆘 Solución de Problemas

### Error: "Table 'productos_venta' doesn't exist"
**Solución**: Ejecutar `crear-tabla-productos-venta.sql` primero (Paso 1)

### Error: "No puedo añadir o actualizar una fila hija: falla una restricción de clave foránea"
**Solución**: 
1. Ejecutar `diagnosticar-productos-inexistentes.sql`
2. Corregir productos inexistentes O usar `migrar-productos-venta-sin-fk.sql`

### Dashboard no carga o muestra errores
**Solución**:
1. Verificar que el código está actualizado: `git pull origin main`
2. Verificar que los índices están creados
3. Revisar `error_log` para ver errores específicos

### Productos no se muestran en ventas
**Solución**:
1. Verificar que la migración se completó: `SELECT COUNT(*) FROM productos_venta;`
2. Verificar que las nuevas ventas se están guardando en `productos_venta`
3. Revisar logs de PHP para errores

### Rendimiento no mejoró
**Solución**:
1. Verificar que los índices están creados: `SHOW INDEX FROM ventas;`
2. Verificar que no hay consultas N+1 en el código
3. Revisar que el código está usando los nuevos métodos optimizados

---

## 📊 Métricas de Éxito

### Antes de los Cambios
- Dashboard: ~X segundos
- Consultas SQL: ~Y consultas
- Uso de memoria: ~Z MB

### Después de los Cambios
- Dashboard: ~X * 0.2-0.3 segundos (70-80% más rápido)
- Consultas SQL: ~Y * 0.4-0.6 consultas (40-60% menos)
- Uso de memoria: ~Z * 0.5-0.7 MB (30-50% menos)

---

## 📝 Notas Importantes

1. **Backup**: Siempre haz backup de la base de datos antes de aplicar cambios
2. **Horario**: Aplica cambios en horario de bajo tráfico
3. **Testing**: Prueba en ambiente de desarrollo primero
4. **Monitoreo**: Monitorea el sistema después de aplicar cambios
5. **Rollback**: Si algo falla, puedes revertir el código con `git checkout` pero los datos migrados permanecerán

---

## 🔄 Rollback (Si es Necesario)

Si necesitas revertir los cambios:

### 1. Revertir Código
```bash
git checkout HEAD~N  # Donde N es el número de commits a revertir
```

### 2. Los Datos Migrados Permanecen
Los datos en `productos_venta` permanecerán, pero el código volverá a usar JSON como fallback.

### 3. Eliminar Tabla (Solo si es necesario)
```sql
DROP TABLE IF EXISTS productos_venta;
```

**⚠️ ADVERTENCIA**: Esto eliminará todos los datos migrados. Solo hazlo si estás seguro.

---

## ✅ Confirmación de Aplicación

Una vez completados todos los pasos, verifica:

```sql
-- 1. Tabla existe
SHOW TABLES LIKE 'productos_venta';

-- 2. Datos migrados
SELECT COUNT(*) FROM productos_venta;

-- 3. Índices creados
SHOW INDEX FROM ventas WHERE Key_name = 'idx_fecha_cbte_tipo';
SHOW INDEX FROM productos_venta WHERE Key_name = 'idx_producto_cantidad';

-- 4. Nuevas ventas se guardan en tabla relacional
SELECT v.id, v.codigo, v.productos, COUNT(pv.id) as productos_en_tabla
FROM ventas v
LEFT JOIN productos_venta pv ON v.id = pv.id_venta
WHERE v.fecha >= CURDATE()
GROUP BY v.id, v.codigo, v.productos
HAVING productos_en_tabla > 0
LIMIT 5;
```

Si todas las consultas retornan resultados correctos, **¡la migración fue exitosa!** 🎉
