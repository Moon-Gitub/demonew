# Mejoras Implementadas en el Módulo de Compras

## Resumen

Se implementaron mejoras en el módulo de compras manteniendo el flujo actual sin romper el comportamiento existente. Las mejoras permiten:

1. **Crear factura directamente sin orden previa** (unificar paso 1+2 opcionalmente)
2. **Registrar compras de servicios** (ej. EDEMSA) sin productos físicos
3. **Prevenir duplicados en cuenta corriente** del proveedor

## Cambios Realizados

### 1. Vista: `vistas/modulos/crear-compra.php`

**Cambios:**
- Se agregó un checkbox "Cargar factura directa (sin orden previa)" que permite alternar entre el flujo tradicional y el flujo directo
- Se agregaron campos para datos impositivos cuando se selecciona factura directa:
  - Tipo de comprobante (X, Factura A, B, C)
  - Fecha de emisión
  - Punto de venta y número de factura
  - Remito (para tipo X)
  - Campos impositivos: IVA, Percepciones (Ingresos Brutos, IVA, Ganancias), Impuesto Interno
  - Descuento y totales
  - Observaciones

**Funcionalidad:**
- Los campos de factura directa están ocultos por defecto
- Al marcar el checkbox, se muestran los campos y el botón cambia a "Cargar Factura Directa"
- Al desmarcar, vuelve al comportamiento normal (crear orden)

### 2. Controlador: `controladores/compras.controlador.php`

#### Nuevo método: `ctrCrearFacturaDirecta()`

**Funcionalidad:**
- Crea una compra con `estado = 1` (ingresada directamente) en lugar de `estado = 0` (orden)
- Permite facturas de servicios sin productos físicos:
  - Si `listaProductosCompras` está vacío pero hay un monto (`nuevoTotalFactura`), crea un producto virtual con ID 0
  - El producto virtual tiene descripción "SERVICIO - [observación]" y no se actualiza en BD
- Procesa productos reales:
  - Actualiza precios de compra/venta
  - Actualiza stock SOLO si NO es servicio (detecta servicios por palabras clave: SERVICIO, EDEMSA, LUZ, AGUA, GAS, INTERNET, TELEFONIA)
- Registra en cuenta corriente del proveedor con prevención de duplicados

#### Mejoras en `ctrEditarCompra()`

**Cambios:**
- Mejorada la detección de servicios (mismas palabras clave)
- Prevención de duplicados en cuenta corriente: verifica si ya existe un registro antes de insertar

### 3. Modelo: `modelos/compras.modelo.php`

**Métodos existentes utilizados:**
- `mdlIngresarCompraDirecta()`: Inserta compra con todos los campos impositivos
- `mdlObtenerUltimaCompra()`: Obtiene el ID de la última compra creada para registrar en cuenta corriente

**Nota:** No se requirieron cambios en el modelo, se utilizaron métodos existentes.

### 4. JavaScript: `vistas/js/compras.js`

**Funciones agregadas:**
- `calcularTotalFacturaDirecta()`: Calcula el total final sumando neto + impuestos
- Listeners para campos de factura directa:
  - Descuento
  - IVA
  - Percepciones (Ingresos Brutos, IVA, Ganancias)
  - Impuesto Interno
- Mejora en `sumarTotalCompras()`: Actualiza también los campos de factura directa si está activa
- Mejora en `cambioDatosFacturaCompra()`: Funciona tanto para `editar-ingreso.php` como para `crear-compra.php` (factura directa)

**Correcciones:**
- Eliminado código duplicado de funciones de factura directa
- Uso de delegación de eventos (`$(document).on()`) para elementos dinámicos

### 5. Prevención de Duplicados en Cuenta Corriente

**Implementación:**
- En `ctrCrearFacturaDirecta()`: Verifica si ya existe un registro con `id_compra` antes de insertar
- En `ctrEditarCompra()`: Misma verificación para evitar duplicados al validar ingreso dos veces

**Método utilizado:**
- `ModeloProveedoresCtaCte::mdlMostrarCtaCteProveedor($tabla, "id_compra", $idCompra)`

## Flujos de Trabajo

### Flujo Tradicional (Sin Cambios)
1. Usuario crea orden de compra (`estado = 0`)
2. Usuario valida ingreso agregando datos impositivos (`estado = 1`)
3. Se actualiza stock y se registra en cuenta corriente

### Flujo Factura Directa (Nuevo)
1. Usuario marca checkbox "Cargar factura directa"
2. Usuario completa datos impositivos directamente
3. Al guardar, se crea compra con `estado = 1` directamente
4. Se actualiza stock (si aplica) y se registra en cuenta corriente

### Flujo Servicios (Nuevo)
1. Usuario marca checkbox "Cargar factura directa"
2. Usuario NO agrega productos (o agrega productos servicio)
3. Usuario completa monto total y datos impositivos
4. Sistema crea producto virtual (ID 0) si no hay productos
5. Se registra compra y cuenta corriente sin actualizar stock

## Estados de Compra

- `estado = 0`: Orden de compra / Nota de pedido (pendiente de validar)
- `estado = 1`: Compra ingresada / Validada (con datos impositivos)
- `estado = 2`: Compra validada (según consultas existentes)

## Detección de Servicios

Un producto se considera servicio si:
1. Su ID es 0 (producto virtual)
2. Su descripción contiene: "SERVICIO", "EDEMSA", "LUZ", "AGUA", "GAS", "INTERNET", "TELEFONIA"
3. Tiene stock = 0 Y descripción que sugiere servicio

Los servicios NO actualizan stock al procesarse.

## Seguridad

- Validación CSRF en ambos métodos (`ctrCrearCompra` y `ctrCrearFacturaDirecta`)
- Validación de datos antes de procesar
- Prevención de duplicados en cuenta corriente

## Compatibilidad

- ✅ El flujo tradicional sigue funcionando igual
- ✅ No se rompe ninguna funcionalidad existente
- ✅ Los reportes y consultas existentes siguen funcionando
- ✅ La estructura de la tabla `compras` no cambió (se usan campos existentes)

## Notas Técnicas

### Producto Virtual (ID 0)
- Se crea solo cuando `listaProductosCompras` está vacío pero hay monto
- No se guarda en la tabla `productos`
- Se guarda en el JSON de `productos` de la compra para mantener consistencia
- Permite registrar facturas de servicios sin necesidad de crear productos en el catálogo

### Cuenta Corriente
- Tipo 1 = Compra (aumenta saldo a pagar)
- Tipo 0 = Pago (disminuye saldo a pagar)
- Se previene duplicados verificando `id_compra` antes de insertar

## Pruebas Recomendadas

1. ✅ Crear orden tradicional y validar ingreso (flujo existente)
2. ✅ Crear factura directa con productos físicos
3. ✅ Crear factura directa de servicio (EDEMSA) sin productos
4. ✅ Verificar que no se dupliquen registros en cuenta corriente
5. ✅ Verificar que servicios no actualicen stock
6. ✅ Verificar cálculos de totales con impuestos
