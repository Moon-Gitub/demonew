# Implementación: Tabla Relacional productos_venta

## ✅ Cambios Realizados

### 1. Estructura de Base de Datos
- ✅ **Creado**: `db/crear-tabla-productos-venta.sql`
  - Tabla `productos_venta` con FOREIGN KEYs
  - Índices para búsquedas rápidas
  
- ✅ **Creado**: `db/migrar-productos-venta.sql`
  - Script de migración de datos JSON existentes
  - Procedimiento almacenado para migración segura
  - Consultas de verificación

### 2. Modelos (Backend)
- ✅ **`modelos/ventas.modelo.php`**
  - `mdlObtenerProductosVenta()` - Obtiene productos con JOIN a `productos` y `categorias`
  - `mdlIngresarProductosVenta()` - Inserta productos en tabla relacional
  - `mdlEliminarProductosVenta()` - Elimina productos de una venta
  - `mdlIngresarVenta()` - Modificado para insertar en `productos_venta` automáticamente
  - `mdlEditarVenta()` - Modificado para actualizar `productos_venta`

### 3. Controladores
- ✅ **`controladores/ventas.controlador.php`**
  - `ctrObtenerProductosVenta()` - Helper para obtener productos
  - `ctrObtenerProductosVentaLegacy()` - Retorna formato compatible con JSON antiguo
  - `ctrCrearVentaCaja()` - Ya inserta en `productos_venta` (automático desde modelo)
  - `ctrEditarVenta()` - Actualizado para usar tabla relacional
  - `ctrAnularVenta()` - Actualizado para usar tabla relacional

### 4. Vistas (Frontend)
- ✅ **`vistas/modulos/editar-venta.php`** - Usa `ctrObtenerProductosVentaLegacy()`
- ✅ **`vistas/modulos/ventas-productos.php`** - 3 lugares actualizados
- ✅ **`vistas/modulos/ventas-rentabilidad.php`** - Actualizado
- ✅ **`vistas/modulos/ventas-categoria-proveedor-informe.php`** - 3 lugares actualizados
- ✅ **`vistas/modulos/presupuesto-venta.php`** - Actualizado
- ✅ **`vistas/modulos/pedidos-validados.php`** - Actualizado
- ✅ **`vistas/modulos/pedidos-nuevos.php`** - Actualizado

### 5. PDFs (6 archivos)
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/comprobante.php`** - Actualizado
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/ticket.php`** - Actualizado
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/remito.php`** - Actualizado
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteP.php`** - Actualizado
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/comprobanteMail.php`** - Actualizado
- ✅ **`extensiones/vendor/tecnickcom/tcpdf/pdf/presupuesto.php`** - Actualizado

### 6. Reportes y Análisis
- ✅ **`controladores/caja-cierres.controlador.php`** - Actualizado
- ✅ **`modelos/productos.modelo.php`** - `mdlMostrarProductosMasVendidos()` actualizado para usar tabla relacional

### 7. AJAX y APIs
- ✅ **`ajax/ventas.ajax.php`** - Retorna productos en formato compatible

## 🔄 Compatibilidad

**Todos los cambios mantienen compatibilidad con datos existentes:**
- Si no hay datos en `productos_venta`, intenta leer desde JSON (campo `productos`)
- Las nuevas ventas se guardan en ambas ubicaciones (JSON + tabla relacional)
- Migración gradual sin romper funcionalidad existente

## 📋 Pasos para Completar la Migración

### Paso 1: Crear la tabla
```sql
SOURCE db/crear-tabla-productos-venta.sql;
```

### Paso 2: Migrar datos existentes
```sql
SOURCE db/migrar-productos-venta.sql;
```

### Paso 3: Verificar migración
Revisar las consultas de verificación en `migrar-productos-venta.sql`

### Paso 4: Probar funcionalidad
- Crear una nueva venta
- Editar una venta existente
- Generar PDFs
- Ver reportes

## 🎯 Beneficios Obtenidos

1. **Rendimiento**: Consultas SQL directas en lugar de `json_decode()`
2. **Escalabilidad**: Índices para búsquedas rápidas
3. **Integridad**: FOREIGN KEY garantiza consistencia
4. **Reportes**: SQL puro para análisis complejos
5. **Mantenibilidad**: Estructura estándar y fácil de entender

## ⚠️ Notas Importantes

- El campo `productos` en la tabla `ventas` **se mantiene** por compatibilidad
- Las nuevas ventas se guardan en **ambas ubicaciones** (JSON + tabla relacional)
- El código intenta primero la tabla relacional, luego JSON (fallback)
- La migración es **reversible** (los datos JSON originales se mantienen)
