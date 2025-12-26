# Checklist de Migración Completa

## ✅ Pre-Migración

- [ ] **Backup de Base de Datos**
  ```bash
  mysqldump -u usuario -p nombre_bd > backup_antes_migracion_$(date +%Y%m%d_%H%M%S).sql
  ```

- [ ] **Backup del Código**
  ```bash
  git tag backup-antes-migracion-$(date +%Y%m%d)
  git push origin backup-antes-migracion-$(date +%Y%m%d)
  ```

- [ ] **Verificar Versión de MySQL/MariaDB**
  ```sql
  SELECT VERSION();
  ```
  Debe ser 5.7+ o MariaDB 10.2+

- [ ] **Verificar Espacio en Disco**
  - Espacio necesario: ~10-20% del tamaño actual de la tabla `ventas`

---

## ✅ Paso 1: Crear Tabla

- [ ] Ejecutar `crear-tabla-productos-venta.sql`
- [ ] Verificar que la tabla existe: `SHOW TABLES LIKE 'productos_venta';`
- [ ] Verificar estructura: `DESCRIBE productos_venta;`
- [ ] Verificar índices: `SHOW INDEX FROM productos_venta;`
- [ ] Verificar FOREIGN KEYs: 
  ```sql
  SELECT 
    CONSTRAINT_NAME, 
    TABLE_NAME, 
    REFERENCED_TABLE_NAME 
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
  WHERE TABLE_NAME = 'productos_venta' 
  AND REFERENCED_TABLE_NAME IS NOT NULL;
  ```

---

## ✅ Paso 2: Migrar Datos

- [ ] (Opcional) Ejecutar diagnóstico: `diagnosticar-productos-inexistentes.sql`
- [ ] Revisar resultados del diagnóstico
- [ ] Decidir qué script usar:
  - [ ] `migrar-productos-venta.sql` (con validación)
  - [ ] `migrar-productos-venta-sin-fk.sql` (sin validación)
- [ ] Ejecutar script de migración elegido
- [ ] Verificar conteo de productos migrados
- [ ] Verificar integridad de datos (consultas de verificación)
- [ ] Comparar totales (ventas vs productos_venta)

---

## ✅ Paso 3: Optimizar Índices

- [ ] Ejecutar `db/optimizar-indices-dashboard.sql`
- [ ] Verificar índice `idx_fecha_cbte_tipo` en `ventas`
- [ ] Verificar índice `idx_producto_cantidad` en `productos_venta`

---

## ✅ Paso 4: Actualizar Código

- [ ] Hacer `git pull origin main`
- [ ] Verificar que no hay conflictos
- [ ] Verificar archivos modificados:
  - [ ] `modelos/ventas.modelo.php`
  - [ ] `modelos/productos.modelo.php`
  - [ ] `controladores/ventas.controlador.php`
  - [ ] `controladores/productos.controlador.php`
  - [ ] `vistas/modulos/inicio/cajas-superiores.php`
  - [ ] `vistas/modulos/reportes/productos-mas-vendidos.php`

---

## ✅ Paso 5: Pruebas Funcionales

### Dashboard
- [ ] Dashboard carga sin errores
- [ ] Cajas de estadísticas muestran valores correctos
- [ ] Gráfico de ventas se muestra
- [ ] Productos más vendidos se muestra
- [ ] Tiempo de carga mejorado (comparar con antes)

### Ventas
- [ ] Crear nueva venta funciona
- [ ] Verificar en BD que se guardó en `productos_venta`
- [ ] Editar venta funciona
- [ ] Verificar que se actualizó en `productos_venta`
- [ ] Anular venta funciona
- [ ] Verificar que se eliminó de `productos_venta` (CASCADE)

### PDFs
- [ ] Comprobante A se genera correctamente
- [ ] Comprobante B se genera correctamente
- [ ] Ticket se genera correctamente
- [ ] Remito se genera correctamente
- [ ] Presupuesto se genera correctamente
- [ ] Todos muestran productos correctamente

### Reportes
- [ ] Ventas por Productos funciona
- [ ] Rentabilidad funciona
- [ ] Categorías/Proveedores funciona
- [ ] Todos muestran datos correctos

### Vistas
- [ ] Editar venta muestra productos
- [ ] Presupuesto venta muestra productos
- [ ] Pedidos muestran productos
- [ ] Todas las vistas funcionan correctamente

---

## ✅ Paso 6: Verificación de Rendimiento

- [ ] Medir tiempo de carga del dashboard (antes vs después)
- [ ] Verificar número de consultas SQL (debe ser menor)
- [ ] Verificar uso de memoria (debe ser menor)
- [ ] Verificar que se usan los índices:
  ```sql
  EXPLAIN SELECT ... FROM ventas WHERE fecha = ... AND cbte_tipo NOT IN (...);
  ```
  Debe mostrar `Using index` o usar `idx_fecha_cbte_tipo`

---

## ✅ Paso 7: Verificación Final

- [ ] No hay errores en `error_log`
- [ ] No hay warnings en consola del navegador
- [ ] Todas las funcionalidades trabajan correctamente
- [ ] Rendimiento mejorado según métricas
- [ ] Usuarios pueden trabajar normalmente

---

## 📊 Métricas de Éxito

### Antes
- [ ] Tiempo de carga dashboard: _____ segundos
- [ ] Número de consultas SQL: _____
- [ ] Uso de memoria: _____ MB

### Después
- [ ] Tiempo de carga dashboard: _____ segundos (debe ser 70-80% menor)
- [ ] Número de consultas SQL: _____ (debe ser 40-60% menor)
- [ ] Uso de memoria: _____ MB (debe ser 30-50% menor)

---

## 🎯 Confirmación Final

- [ ] **Migración completada exitosamente**
- [ ] **Todos los tests pasaron**
- [ ] **Rendimiento mejorado**
- [ ] **Sistema funcionando correctamente**
- [ ] **Documentación actualizada**

---

## 📝 Notas Adicionales

**Fecha de migración**: _______________

**Realizado por**: _______________

**Observaciones**:
_________________________________
_________________________________
_________________________________

---

## 🆘 Si Algo Sale Mal

1. **Revisar logs**: `error_log`, logs de MySQL
2. **Verificar estado**: Consultas de verificación en `PASOS-APLICACION-COMPLETA.md`
3. **Rollback si es necesario**: Ver sección de rollback en `PASOS-APLICACION-COMPLETA.md`
4. **Contactar soporte**: Si no puedes resolver el problema
