# Migración: JSON a Tabla Relacional `productos_venta`

## 📋 Descripción

Este directorio contiene todos los scripts y documentación necesaria para migrar el sistema de almacenamiento de productos en ventas desde **JSON** (campo `productos` en tabla `ventas`) a una **tabla relacional** (`productos_venta`).

## 🎯 Objetivo

Mejorar el rendimiento, escalabilidad e integridad de datos cambiando de:
- ❌ **Antes**: Productos almacenados como JSON en `ventas.productos`
- ✅ **Después**: Productos almacenados en tabla relacional `productos_venta`

## 📁 Archivos Incluidos

### 1. `crear-tabla-productos-venta.sql`
**Propósito**: Crea la tabla `productos_venta` con todas sus restricciones, índices y FOREIGN KEYs.

**Uso**:
```sql
SOURCE migracion/sinjson/crear-tabla-productos-venta.sql;
```

**Contenido**:
- Estructura de la tabla `productos_venta`
- Índices para búsquedas rápidas
- FOREIGN KEYs para integridad referencial
- Comentarios explicativos

### 2. `migrar-productos-venta.sql`
**Propósito**: Script principal de migración que valida la existencia de productos antes de insertar.

**Uso**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta.sql;
```

**Características**:
- ✅ Valida que los productos existan en la tabla `productos`
- ✅ Omite productos inexistentes (no rompe la migración)
- ✅ Muestra resumen de productos migrados y omitidos
- ✅ Incluye consultas de verificación

**Recomendado para**: Migración normal con validación de integridad.

### 3. `migrar-productos-venta-sin-fk.sql`
**Propósito**: Script alternativo que deshabilita temporalmente las FOREIGN KEYs para migrar todos los datos, incluso productos inexistentes.

**Uso**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta-sin-fk.sql;
```

**Características**:
- ⚠️ Deshabilita FOREIGN KEYs temporalmente
- ✅ Migra todos los productos (existentes e inexistentes)
- ✅ Re-habilita FOREIGN KEYs al finalizar
- ✅ Muestra resumen detallado

**Recomendado para**: Cuando necesitas migrar datos históricos con productos que ya no existen.

### 4. `diagnosticar-productos-inexistentes.sql`
**Propósito**: Script de diagnóstico para identificar productos problemáticos antes de la migración.

**Uso**:
```sql
SOURCE migracion/sinjson/diagnosticar-productos-inexistentes.sql;
```

**Información que muestra**:
1. Lista detallada de productos inexistentes por venta
2. Resumen de productos inexistentes (cuántas veces se usan)
3. Ventas afectadas con productos inexistentes

**Recomendado para**: Ejecutar ANTES de la migración para entender qué productos son problemáticos.

## 🚀 Proceso de Migración Recomendado

### Paso 1: Diagnóstico (Opcional pero Recomendado)
```sql
SOURCE migracion/sinjson/diagnosticar-productos-inexistentes.sql;
```
Revisa los resultados para entender qué productos no existen.

### Paso 2: Crear la Tabla
```sql
SOURCE migracion/sinjson/crear-tabla-productos-venta.sql;
```
Esto crea la estructura de la tabla `productos_venta`.

### Paso 3: Ejecutar Migración

**Opción A - Con Validación (Recomendado)**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta.sql;
```

**Opción B - Sin Validación FK (Solo si es necesario)**:
```sql
SOURCE migracion/sinjson/migrar-productos-venta-sin-fk.sql;
```

### Paso 4: Verificar Migración
Los scripts de migración incluyen consultas de verificación automáticas. Revisa:
- Total de productos migrados
- Ventas migradas
- Diferencias de totales (si las hay)
- Productos inexistentes (si usaste Opción B)

## 📊 Estructura de la Tabla `productos_venta`

```sql
CREATE TABLE productos_venta (
  id INT(11) NOT NULL AUTO_INCREMENT,
  id_venta INT(11) NOT NULL,
  id_producto INT(11) NOT NULL,
  cantidad DECIMAL(10,2) NOT NULL DEFAULT 0,
  precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_venta (id_venta),
  INDEX idx_producto (id_producto),
  INDEX idx_venta_producto (id_venta, id_producto),
  FOREIGN KEY (id_venta) REFERENCES ventas(id) ON DELETE CASCADE,
  FOREIGN KEY (id_producto) REFERENCES productos(id) ON DELETE RESTRICT
);
```

## ⚠️ Consideraciones Importantes

### Compatibilidad
- ✅ El campo `productos` en la tabla `ventas` **se mantiene** por compatibilidad
- ✅ El código intenta primero la tabla relacional, luego JSON (fallback)
- ✅ Las nuevas ventas se guardan en **ambas ubicaciones** (JSON + tabla relacional)

### Productos Inexistentes
Si encuentras productos inexistentes:
1. **Opción 1**: Corregir el JSON de las ventas afectadas
2. **Opción 2**: Crear los productos faltantes en la tabla `productos`
3. **Opción 3**: Omitirlos durante la migración (se mantienen en JSON)

### Reversibilidad
- ✅ La migración es **reversible** (los datos JSON originales se mantienen)
- ✅ Puedes eliminar la tabla `productos_venta` sin perder datos
- ⚠️ Si eliminas la tabla, el sistema volverá a usar solo JSON

## 🔍 Verificación Post-Migración

### 1. Contar Registros
```sql
SELECT COUNT(*) as total_productos_venta FROM productos_venta;
SELECT COUNT(DISTINCT id_venta) as ventas_migradas FROM productos_venta;
```

### 2. Comparar Totales
```sql
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

### 3. Verificar Ventas sin Productos Migrados
```sql
SELECT 
    v.id,
    v.codigo,
    v.fecha,
    JSON_LENGTH(v.productos) as productos_en_json,
    COUNT(pv.id) as productos_migrados
FROM ventas v
LEFT JOIN productos_venta pv ON v.id = pv.id_venta
WHERE v.productos IS NOT NULL 
AND v.productos != '' 
AND v.productos != '[]'
GROUP BY v.id, v.codigo, v.fecha
HAVING productos_migrados = 0;
```

## 📝 Cambios en el Código

Los siguientes archivos fueron modificados para usar la tabla relacional:

### Modelos
- `modelos/ventas.modelo.php`
  - `mdlObtenerProductosVenta()` - Nueva función
  - `mdlIngresarProductosVenta()` - Nueva función
  - `mdlEliminarProductosVenta()` - Nueva función
  - `mdlIngresarVenta()` - Modificado
  - `mdlEditarVenta()` - Modificado

### Controladores
- `controladores/ventas.controlador.php`
  - `ctrObtenerProductosVenta()` - Nueva función
  - `ctrObtenerProductosVentaLegacy()` - Nueva función (formato compatible)

### Vistas
- `vistas/modulos/editar-venta.php`
- `vistas/modulos/ventas-productos.php`
- `vistas/modulos/ventas-rentabilidad.php`
- `vistas/modulos/ventas-categoria-proveedor-informe.php`
- `vistas/modulos/presupuesto-venta.php`
- `vistas/modulos/pedidos-validados.php`
- `vistas/modulos/pedidos-nuevos.php`

### PDFs
- `extensiones/vendor/tecnickcom/tcpdf/pdf/comprobante.php`
- `extensiones/vendor/tecnickcom/tcpdf/pdf/ticket.php`
- `extensiones/vendor/tecnickcom/tcpdf/pdf/remito.php`
- `extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteP.php`
- `extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteMail.php`
- `extensiones/vendor/tecnickcom/tcpdf/pdf/presupuesto.php`

### Reportes
- `controladores/caja-cierres.controlador.php`
- `modelos/productos.modelo.php` - `mdlMostrarProductosMasVendidos()`

### AJAX
- `ajax/ventas.ajax.php`

## 🎯 Beneficios Obtenidos

1. **Rendimiento**: Consultas SQL directas en lugar de `json_decode()`
2. **Escalabilidad**: Índices para búsquedas rápidas
3. **Integridad**: FOREIGN KEY garantiza consistencia
4. **Reportes**: SQL puro para análisis complejos
5. **Mantenibilidad**: Estructura estándar y fácil de entender

## 📚 Referencias

- Documentación principal: `IMPLEMENTACION-PRODUCTOS-VENTA.md` (en raíz del proyecto)
- Análisis inicial: `ANALISIS-OPCIONES-PRODUCTOS-VENTA.md` (si existe)

## 🆘 Solución de Problemas

### Error: "No puedo añadir o actualizar una fila hija: falla una restricción de clave foránea"
**Causa**: Hay productos en el JSON que no existen en la tabla `productos`.

**Solución**:
1. Ejecuta `diagnosticar-productos-inexistentes.sql` para identificar productos problemáticos
2. Usa `migrar-productos-venta.sql` (omite productos inexistentes) o
3. Usa `migrar-productos-venta-sin-fk.sql` (migra todo sin validación)

### Error: "Table 'productos_venta' doesn't exist"
**Causa**: No se ha ejecutado el script de creación de tabla.

**Solución**: Ejecuta `crear-tabla-productos-venta.sql` primero.

### Productos no se muestran después de la migración
**Causa**: Puede haber un problema con el formato del JSON o productos inexistentes.

**Solución**:
1. Verifica que la migración se completó: `SELECT COUNT(*) FROM productos_venta;`
2. Revisa productos inexistentes con el script de diagnóstico
3. El sistema tiene fallback a JSON, así que debería seguir funcionando

## 📅 Fecha de Implementación

- **Fecha**: Diciembre 2025
- **Versión**: 1.0
- **Estado**: ✅ Implementado y probado
