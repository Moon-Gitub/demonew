# Plan de migración: Sistema actual → Versión inicial

Este documento detalla los cambios realizados desde la versión inicial del sistema y cómo revertir a ese estado si fuera necesario.

---

## 1. Versión de referencia

| Concepto | Valor |
|----------|-------|
| **Commit inicial** | `d00704a` (first commit) |
| **Commits totales desde inicio** | ~911 |
| **Archivos modificados** | ~537 |
| **Líneas añadidas/eliminadas** | ~110.000 insertadas, ~6.000 eliminadas |

**Para volver a la versión inicial:**
```bash
git checkout d00704a
# O crear una rama de respaldo:
git branch version-inicial d00704a
```

---

## 2. Cambios por área funcional

### 2.1 Facturación AFIP y ventas

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Helper reutilizable AFIP | `controladores/facturacion/FacturacionAfipHelper.php` | Clase para armar FeCAEReq, Monotributo/RI, filtro tipos comprobante |
| Selector empresa en Autorizar | `vistas/modulos/ventas.php`, `vistas/js/ventas.js` | Modal Autorizar: elegir empresa, tipos A/B o C según condicion_iva |
| Fix redondeo AFIP 10048 | `controladores/facturacion/FacturacionAfipHelper.php` | Redondeo correcto para evitar rechazo AFIP |
| **Facturación por lote** | `controladores/ventas.controlador.php`, `ajax/ventas.ajax.php`, `vistas/modulos/ventas.php`, `vistas/js/ventas.js` | Checkboxes, modal Autorizar, buildFeCAEReqLote, orden por fecha, tipo elegido |
| Regenerar TA si vencido | `controladores/ventas.controlador.php` | Regenerar Ticket de Acceso antes de facturar por lote |
| Duplicados productos_venta | `modelos/ventas.modelo.php` | UNIQUE + ON DUPLICATE KEY UPDATE para evitar duplicados |
| Informes excluir anuladas | `modelos/ventas.modelo.php`, `modelos/presupuestos.modelo.php` | cbte_tipo 999 y NC no suman en totales |
| Punto de venta en columna Sucursal | `vistas/modulos/ventas.php` | Mostrar pto_vta numérico para facturar por lote |

### 2.2 Empresa (configuración)

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| UI puntos de venta y almacenes | `vistas/modulos/empresa.php`, `vistas/js/empresa.js` | Agregar/quitar filas con botón +, sin escribir JSON manual |
| Mejor contraste | `vistas/modulos/empresa.php` | Inputs blancos, botón + oscuro, help-block legible |
| Descripciones help-block | `vistas/modulos/empresa.php` | Descripción en cada campo (como Mercado Pago) |

### 2.3 Usuarios

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Fix ModeloEmpresa not found | `ajax/usuarios.ajax.php` | require modelos/empresa.modelo.php |
| Sucursal y almacenes | `vistas/modulos/usuarios.php`, `controladores/usuarios.controlador.php` | Selector sucursal, almacenes por empresa |

### 2.4 Productos

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Campo activo | `modelos/productos.modelo.php`, vistas, ajax | Desactivar en lugar de borrar si tiene ventas |
| Productos desactivados | Nueva ruta, menú, vista | Pantalla para ver y reactivar inactivos |
| Ocultar inactivos en listados | DataTables, consultas | activo = 1 en filtros |

### 2.5 Crear venta caja

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Layout responsive | `vistas/modulos/crear-venta-caja.php`, JS | Columnas, cobro integrado, móvil |
| Medios de pago desde BD | `modelos/medios_pago.modelo.php` | Sin hardcodeo, tabla medios_pago |
| Atajos de teclado | Modal F1, Alt+P punto venta | Enlace en navbar/formulario |
| Descuento/interés | Alineación, actualización total | Campos alineados, total se actualiza |

### 2.6 Informes y dashboard

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Dashboard ejecutivo | Nuevas vistas, modelos | Dashboard diario, menú Informes |
| Gestión de pedidos | `modelos/reporte-gestion-pedidos.modelo.php` | Top 20, críticos, baja rotación, DataTables |
| Ventas por productos | Resumen agregado, DataTable | Cantidad, compra, venta, margen % |
| Gráficos Chart.js | Compatibilidad 1.x | Bar/Pie |
| Quitar widgets pesados | Dashboard | Productos más vendidos/recientes |

### 2.7 Compras

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Factura directa | Sin orden pendiente | Solo factura + stock, estado=1 |
| Cantidad pedidos cuando recibidos=0 | Actualizar stock | Usar cantidad pedidos |

### 2.8 Cajas

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Egresos por devoluciones | Cierre de caja | Detalle con número venta y productos |

### 2.9 Clientes y proveedores

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| Medios de pago desde BD | Cta. cte. cliente/proveedor | Pago mixto, medios desde medios_pago |
| Recibo cta cte | Diseño | Mejoras visuales |

### 2.10 Login y sesión

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| session_write_close antes redirect | Login | Un solo intento de ingreso |
| Permisos por rol | `permisos_rol` | Definir $raiz en ctrIngresoUsuario |

### 2.11 Extras

| Cambio | Archivos principales | Descripción |
|--------|---------------------|-------------|
| POS Offline Moon | `extras/pos-offline-moon/` | Python, tkinter, sync |
| Generar ventas de prueba | `extras/scripts/generar-ventas-prueba.php` | Script para datos de prueba |
| Mercado Pago, webhook | `webhook-mercadopago.php` | Sistema de cobro |
| .env, phpdotenv | `helpers.php`, `config.php` | Variables de entorno |
| Diagnóstico rendimiento | `diagnostico-rendimiento.php` | Medir PHP, MySQL, etc. |

---

## 3. Archivos más modificados (top 20)

| Archivo | Cambios típicos |
|---------|-----------------|
| `vistas/modulos/ventas.php` | Facturar por lote, columna sucursal, checkboxes |
| `vistas/modulos/crear-venta-caja.php` | Layout, cobro, medios pago |
| `controladores/ventas.controlador.php` | Facturación lote, AFIP, orden fecha |
| `vistas/js/ventas.js` | Modal lote, checkboxes, handlers |
| `vistas/modulos/empresa.php` | Puntos venta, almacenes, descripciones |
| `vistas/js/empresa.js` | Render ptos/almacenes, actualizar hidden |
| `modelos/ventas.modelo.php` | productos_venta, informes anuladas |
| `ajax/ventas.ajax.php` | facturarLoteIds, tipoCbte |
| `ajax/usuarios.ajax.php` | ModeloEmpresa, almacenes |
| `vistas/plantilla.php` | Menú, cabezote |
| `controladores/facturacion/FacturacionAfipHelper.php` | Nuevo, helper AFIP |
| `vistas/modulos/productos*.php` | activo, desactivados |
| `modelos/productos.modelo.php` | activo, verificación ventas |
| Informes, dashboard | Múltiples archivos |

---

## 4. Cómo revertir

### Opción A: Revertir a un commit específico (destructivo)

```bash
# CUIDADO: pierde todos los cambios posteriores
git reset --hard d00704a
```

### Opción B: Crear rama de la versión inicial (recomendado)

```bash
git branch version-inicial d00704a
# Para ver esa versión:
git checkout version-inicial
# Para volver al actual:
git checkout main
```

### Opción C: Revertir solo un área (manual)

1. Identificar los commits del área (ej. facturar por lote).
2. `git revert <commit>` de cada uno en orden inverso.
3. O restaurar archivos concretos: `git checkout d00704a -- ruta/archivo.php`

### Opción D: Revertir cambios recientes (últimos N commits)

```bash
# Ejemplo: deshacer últimos 20 commits pero mantener cambios en working dir
git reset --soft HEAD~20
```

---

## 5. Dependencias entre cambios

Algunos cambios dependen de otros:

- **Facturar por lote** depende de: FacturacionAfipHelper, selector empresa en modal, productos_venta sin duplicados.
- **Usuarios sucursal** depende de: ModeloEmpresa en ajax, empresa.almacenes.
- **Productos activo** depende de: columna `activo` en tabla productos (migración SQL).

Si revertís por partes, tené en cuenta estas dependencias.

---

## 6. Migraciones de base de datos

Cambios que pueden requerir ALTER TABLE:

- `productos.activo` (si se agregó)
- `productos_venta` UNIQUE (id_venta, id_producto) para ON DUPLICATE KEY
- `medios_pago` (tabla de medios de pago)
- Otras columnas según el historial de migraciones en `db/` y `migracion/`

Revisar `migracion/alter_table.sql` y scripts en `db/` antes de revertir.

---

## 7. Resumen ejecutivo

| Acción | Comando / Pasos |
|--------|-----------------|
| Ver versión inicial | `git show d00704a` |
| Crear rama de respaldo | `git branch version-inicial d00704a` |
| Volver a versión inicial | `git checkout version-inicial` |
| Comparar actual vs inicial | `git diff d00704a..HEAD` |
| Listar archivos cambiados | `git diff --name-only d00704a..HEAD` |

---

*Documento generado a partir del historial de Git. Actualizar si se agregan nuevos cambios relevantes.*
