# Índice: Migración JSON a Tabla Relacional

## 📂 Archivos en este Directorio

### Scripts SQL

1. **`crear-tabla-productos-venta.sql`**
   - Crea la tabla `productos_venta`
   - Estructura, índices y FOREIGN KEYs
   - **Ejecutar primero**

2. **`migrar-productos-venta.sql`**
   - Script principal de migración
   - Valida existencia de productos
   - **Recomendado para uso normal**

3. **`migrar-productos-venta-sin-fk.sql`**
   - Script alternativo sin validación FK
   - Para productos inexistentes
   - **Usar solo si es necesario**

4. **`diagnosticar-productos-inexistentes.sql`**
   - Identifica productos problemáticos
   - **Ejecutar antes de migrar**

### Documentación

5. **`README.md`**
   - Documentación completa del proceso
   - Guía paso a paso
   - Solución de problemas

6. **`INDICE.md`** (este archivo)
   - Índice de archivos
   - Referencias rápidas

## 🔄 Orden de Ejecución Recomendado

```
1. diagnosticar-productos-inexistentes.sql  (Opcional - para diagnóstico)
2. crear-tabla-productos-venta.sql          (Obligatorio - crear tabla)
3. migrar-productos-venta.sql               (Obligatorio - migrar datos)
   O migrar-productos-venta-sin-fk.sql      (Alternativa si hay problemas)
```

## 📋 Checklist de Migración

- [ ] Ejecutar diagnóstico (opcional)
- [ ] Crear tabla `productos_venta`
- [ ] Ejecutar migración
- [ ] Verificar resultados
- [ ] Probar funcionalidad del sistema
- [ ] Verificar reportes y PDFs

## 🔗 Enlaces Rápidos

- **Documentación Principal**: `README.md`
- **Análisis Inicial**: `../ANALISIS-OPCIONES-PRODUCTOS-VENTA.md` (si existe)
- **Implementación**: `../../IMPLEMENTACION-PRODUCTOS-VENTA.md`
