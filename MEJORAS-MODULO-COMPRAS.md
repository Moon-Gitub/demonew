# Mejoras Implementadas en el Módulo de Compras

## Resumen de Cambios

Se han implementado mejoras en el módulo de compras manteniendo el flujo actual sin romper el comportamiento existente.

## Funcionalidades Agregadas

### 1. Factura Directa (Sin Orden Previa)

**Objetivo**: Permitir cargar facturas directamente sin crear una orden de compra previa.

**Implementación**:
- Se agregó un checkbox "Cargar factura directa (sin orden previa)" en `crear-compra.php`
- Cuando está activo, se muestran campos adicionales para datos impositivos
- Se creó el método `ctrCrearFacturaDirecta()` en `ControladorCompras`
- Se agregó el método `mdlIngresarCompraDirecta()` en `ModeloCompras`
- Las facturas directas se guardan con `estado=1` (ingresada directamente)

**Archivos modificados**:
- `vistas/modulos/crear-compra.php`: Agregados campos para factura directa
- `controladores/compras.controlador.php`: Método `ctrCrearFacturaDirecta()`
- `modelos/compras.modelo.php`: Método `mdlIngresarCompraDirecta()` y `mdlObtenerUltimaCompra()`
- `vistas/js/compras.js`: Funciones JavaScript para calcular totales en factura directa

### 2. Soporte para Facturas de Servicios

**Objetivo**: Permitir registrar compras de servicios (ej. EDEMSA) sin productos físicos.

**Implementación**:
- Se mejoró la detección de servicios en el procesamiento de productos
- Si no hay productos pero hay un monto total, se crea un "producto servicio virtual" con ID=0
- Los productos con ID=0 no se procesan (no actualizan precios ni stock)
- Se detectan servicios por:
  - Descripción que contiene "SERVICIO"
  - Stock = 0 y descripción que contiene "EDEMSA", "LUZ", "AGUA"
  - ID = 0 (producto servicio virtual)

**Archivos modificados**:
- `controladores/compras.controlador.php`: Lógica de detección de servicios en `ctrCrearFacturaDirecta()` y `ctrEditarCompra()`

### 3. Mejoras en JavaScript

**Funcionalidades agregadas**:
- Función `calcularTotalFacturaDirecta()`: Calcula el total de factura con impuestos
- Sincronización automática de totales cuando cambian productos en modo factura directa
- Listeners para campos impositivos (IVA, percepciones, etc.) en factura directa
- Función `cambioDatosFacturaCompra()` mejorada para funcionar tanto en `editar-ingreso.php` como en `crear-compra.php`
- Datepicker configurado para `fechaEmisionDirecta`

**Archivos modificados**:
- `vistas/js/compras.js`: Funciones para factura directa

### 4. Prevención de Duplicados en Cuenta Corriente

**Objetivo**: Evitar que se registren duplicados en cuenta corriente del proveedor.

**Implementación**:
- Se verifica si ya existe un registro en cuenta corriente antes de insertar
- Aplica tanto para factura directa como para validación de ingreso

**Archivos modificados**:
- `controladores/compras.controlador.php`: Validación en `ctrCrearFacturaDirecta()` y `ctrEditarCompra()`

## Flujos de Trabajo

### Flujo 1: Orden de Compra + Validación (Existente - Sin Cambios)
1. Crear orden de compra (`ctrCrearCompra()`) → `estado=0`
2. Validar ingreso (`ctrEditarCompra()`) → `estado=1`, se agregan datos impositivos, se actualiza stock, se registra en cuenta corriente

### Flujo 2: Factura Directa (Nuevo)
1. Marcar checkbox "Cargar factura directa"
2. Agregar productos (o dejar vacío para servicios)
3. Seleccionar tipo de comprobante
4. Completar datos impositivos
5. Guardar → `ctrCrearFacturaDirecta()` → `estado=1`, se actualiza stock (si no es servicio), se registra en cuenta corriente

## Estados de Compra

- `estado=0`: Orden de compra (pendiente de validación)
- `estado=1`: Compra ingresada/validada (con datos impositivos)
- `estado=2`: Compra validada (según consultas existentes)

## Campos Agregados a la Tabla `compras`

Los siguientes campos ya existían y se utilizan en factura directa:
- `tipo`: Tipo de comprobante (0=X, 1=Factura A, 6=Factura B, 11=Factura C)
- `remitoNumero`: Número de remito
- `numeroFactura`: Número de factura
- `fechaEmision`: Fecha de emisión
- `descuento`: Descuento aplicado
- `totalNeto`: Total neto
- `iva`: IVA
- `precepcionesIngresosBrutos`: Percepciones de ingresos brutos
- `precepcionesIva`: Percepciones de IVA
- `precepcionesGanancias`: Percepciones de ganancias
- `impuestoInterno`: Impuesto interno
- `observacionFactura`: Observaciones
- `fechaIngreso`: Fecha de ingreso

## Validaciones de Seguridad

- Validación de token CSRF en ambos métodos
- Validación de existencia de productos o monto (para servicios)
- Prevención de duplicados en cuenta corriente

## Compatibilidad

✅ **No se rompe el flujo existente**: El flujo de orden + validación funciona exactamente igual que antes.

✅ **Compatibilidad con estructura existente**: Se utilizan los mismos campos y tablas.

✅ **Consistencia de datos**: La cuenta corriente se actualiza correctamente en ambos flujos.

## Pruebas Recomendadas

1. **Flujo estándar**: Crear orden → Validar ingreso → Verificar cuenta corriente
2. **Factura directa con productos**: Marcar checkbox → Agregar productos → Completar datos impositivos → Guardar
3. **Factura directa de servicio**: Marcar checkbox → No agregar productos → Ingresar monto → Completar datos impositivos → Guardar
4. **Verificar cuenta corriente**: Confirmar que no se duplican registros
5. **Verificar stock**: Confirmar que servicios no actualizan stock

## Notas Técnicas

- Los productos servicio virtuales (ID=0) no se procesan en la base de datos
- La detección de servicios es heurística (basada en descripción y stock)
- Los totales se calculan automáticamente en JavaScript antes del envío
- La fecha de emisión usa el mismo datepicker que las demás fechas
