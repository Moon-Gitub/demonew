# Ventas

En esta sección se describe todo lo relacionado con **ventas**, con **detalle de cada pantalla, cada clic y cada elemento** que el usuario ve y usa.

---

## Cómo llegar a las pantallas de Ventas

| Clic | Qué hace |
|------|----------|
| **Clic en "Ventas"** (en el menú lateral izquierdo) | Se despliega el submenú de Ventas (si es menú tipo árbol). Si ya estaba desplegado, puede contraerse. |
| **Clic en "Adm. ventas"** (dentro del submenú Ventas) | Abre la pantalla de administración de ventas. |
| **Clic en "Adm. presupuestos"** | Abre la pantalla de presupuestos. |
| **Clic en "Crear venta"** | Abre la pantalla del punto de venta (POS). |
| **Clic en "Productos Vendidos"** | Abre el reporte de productos vendidos. |
| **Clic en "Informe rentabilidad"** | Abre el informe de rentabilidad. |
| **Clic en "Informe de ventas"** | Abre el informe de ventas por categoría/proveedor. |

---

## Administración de ventas (Adm. ventas)

**Ruta:** Menú lateral → Ventas → Adm. ventas  
**Perfil:** Administrador y Vendedor

### Elementos de la pantalla y qué hace cada clic

| Elemento | Qué es | Qué hace al usarlo |
|----------|--------|---------------------|
| **Tabla o grilla de ventas** | Listado de ventas (filas = ventas, columnas = fecha, número, cliente, total, estado, etc.) | **Clic en una fila:** suele seleccionar esa venta para poder editar, anular o ver detalle. **Clic en el encabezado de una columna:** en algunas tablas ordena por esa columna (ascendente/descendente). |
| **Campo de búsqueda o filtro** | Caja de texto o filtros por fecha/cliente | Al **escribir** o elegir filtros, la tabla se actualiza y muestra solo las ventas que coinciden. |
| **Botón "Nueva venta" / "Crear venta"** (si existe) | Botón para ir al POS | **Clic:** lleva a la pantalla Crear venta. |
| **Botón "Editar" / ícono de lápiz** (por fila o arriba) | Acción sobre la venta seleccionada | **Clic:** abre la pantalla o modal de edición de esa venta (solo si el sistema y el perfil lo permiten). |
| **Botón "Ver" / ícono de ojo** (si existe) | Ver detalle sin editar | **Clic:** abre un modal o pantalla con el detalle de la venta (ítems, precios, medio de pago). |
| **Botón "Anular" / ícono de eliminar** (si existe) | Anular la venta seleccionada | **Clic:** suele pedir confirmación; al confirmar, la venta queda anulada y el stock puede revertirse. |
| **Botón "Excel" / "Exportar"** (si existe) | Exportar el listado | **Clic:** descarga el listado de ventas en Excel o PDF según la opción. |
| **Paginación** (si existe) | Números o "Siguiente / Anterior" | **Clic:** cambia la página de resultados cuando hay muchas ventas. |

Es la pantalla de consulta y control de todo lo vendido.

---

## Presupuestos (Adm. presupuestos)

**Ruta:** Menú lateral → Ventas → Adm. presupuestos  
**Perfil:** solo Administrador

### Elementos y qué hace cada clic

| Elemento | Qué hace al usarlo |
|----------|---------------------|
| **Tabla de presupuestos** | Lista presupuestos (fecha, cliente, total, estado). **Clic en una fila:** selecciona ese presupuesto. |
| **Botón "Nuevo presupuesto"** (si existe) | **Clic:** abre la pantalla para crear un presupuesto (o Crear venta en modo presupuesto). |
| **Botón "Ver" / "Editar"** | **Clic:** abre el detalle del presupuesto seleccionado para ver o editar ítems y total. |
| **Botón "Convertir en venta"** (si existe) | **Clic:** convierte el presupuesto seleccionado en una venta definitiva (puede pedir confirmación). |
| **Botón "Eliminar" / "Anular"** (si existe) | **Clic:** elimina o anula el presupuesto seleccionado (suele pedir confirmación). |

Para **crear** un presupuesto: desde Ventas → Crear presupuesto (si existe) o desde Crear venta eligiendo tipo "Presupuesto" en lugar de "Venta".

---

## Crear venta (punto de venta / caja)

**Ruta:** Menú lateral → Ventas → Crear venta  
**Perfil:** Administrador y Vendedor

Es la pantalla principal para **cargar una venta** en el momento. Cada campo, cada botón y cada clic están detallados en la guía [Crear venta – Paso a paso](Crear-venta-paso-a-paso).

**Resumen rápido:** se elige cliente (opcional) y lista de precio, se cargan productos por código/nombre o código de balanza, se ajustan cantidades, se ve el total y se cobra con el medio de pago elegido. Al confirmar el cobro, la venta se registra y el stock se descuenta.

---

## Productos vendidos

**Ruta:** Menú lateral → Ventas → Productos Vendidos  
**Perfil:** solo Administrador

| Elemento | Qué hace |
|----------|----------|
| **Filtros** (fecha desde/hasta, producto, etc.) | Al cambiar fechas o criterios y pulsar "Buscar" o "Filtrar", la tabla se actualiza. |
| **Tabla de resultados** | Muestra qué productos se vendieron, cantidades y/o montos en el período. Suele ser solo lectura. |
| **Botón "Exportar"** (si existe) | **Clic:** descarga el reporte en Excel o PDF. |

---

## Informe de rentabilidad

**Ruta:** Menú lateral → Ventas → Informe rentabilidad  
**Perfil:** solo Administrador

| Elemento | Qué hace |
|----------|----------|
| **Filtros** (fecha, producto, etc.) | Al filtrar y buscar, se actualiza el informe. |
| **Tabla o gráfico** | Muestra margen/ganancia (precio venta vs. precio compra) por producto o por venta. |
| **Exportar** (si existe) | **Clic:** descarga el informe. |

---

## Informe de ventas

**Ruta:** Menú lateral → Ventas → Informe de ventas  
**Perfil:** solo Administrador

| Elemento | Qué hace |
|----------|----------|
| **Filtros** (categoría, proveedor, fecha) | Al filtrar y buscar, se actualiza el informe. |
| **Tabla o gráfico** | Muestra ventas por categoría, proveedor u otro criterio. |
| **Exportar** (si existe) | **Clic:** descarga el informe. |

---

## Resumen por pantalla

| Pantalla | Uso principal | Acceso |
|----------|----------------|--------|
| Adm. ventas | Ver tabla, filtrar, clic en Editar/Ver/Anular por venta | Ventas → Adm. ventas |
| Adm. presupuestos | Listar presupuestos, Ver/Editar, Convertir en venta, Eliminar | Ventas → Adm. presupuestos |
| Crear venta | POS: cliente, lista, productos, total, cobro (ver guía paso a paso) | Ventas → Crear venta |
| Productos Vendidos | Reporte de productos vendidos en un período | Ventas → Productos Vendidos |
| Informe rentabilidad | Margen/ganancia por producto o venta | Ventas → Informe rentabilidad |
| Informe de ventas | Ventas por categoría/proveedor | Ventas → Informe de ventas |

Para **cada campo y cada clic** en Crear venta (incluido el modal de cobro), ver [Crear venta – Paso a paso](Crear-venta-paso-a-paso). Para **listas de precio** y **formatos de balanza**, ver [Empresa](Empresa). Para **cobro con Mercado Pago**, ver [Integraciones y cobro](Integraciones-y-cobro). Para **caja y cierres**, ver [Cajas](Cajas). Para **vender sin internet**, ver [Sistema offline](Sistema-offline).
