# Guía: Cómo Registrar Factura de Servicios (sin productos físicos)

## Ejemplo: Factura de EDEMSA (Luz)

Esta guía explica cómo registrar una factura de servicios como EDEMSA, donde no hay productos físicos que ingresar al stock.

## Pasos para Registrar Factura de Servicios

### Paso 1: Acceder a Crear Compra
1. Ve a **Compras** → **Crear Compra**
2. Selecciona el proveedor (ej: "EDEMSA")

### Paso 2: Activar Modo Factura Directa
1. **Marca el checkbox**: "Cargar factura directa (sin orden previa)"
   - Esto mostrará los campos de factura con datos impositivos

### Paso 3: Completar Datos de la Factura

#### 3.1 Tipo de Comprobante
- Selecciona el tipo:
  - **Factura A** (si el proveedor es Responsable Inscripto)
  - **Factura B** (si es Monotributista)
  - **Factura C** (si no tiene CUIT)
  - **X** (si es Remito, aunque no aplica para servicios)

#### 3.2 Datos de la Factura
- **Fecha Emisión**: Fecha de la factura del proveedor
- **Punto de Venta**: Punto de venta de la factura
- **Número de Factura**: Número de la factura

#### 3.3 Totales e Impuestos
**IMPORTANTE**: Como NO vas a agregar productos, debes completar manualmente:

1. **SubTotal**: Ingresa el monto neto de la factura
   - Este campo se actualiza automáticamente si agregas productos
   - Si NO agregas productos, ingrésalo manualmente

2. **Descuento** (opcional): Si hay descuento

3. **Total Neto**: Se calcula automáticamente (SubTotal - Descuento)

4. **Campos Impositivos** (se muestran al seleccionar Factura A, B o C):
   - **I.V.A.**: Monto de IVA
   - **Percep. Ingresos Brutos**: Si aplica
   - **Percep. I.V.A.**: Si aplica
   - **Percep. Ganancias**: Si aplica
   - **Imp. Interno**: Si aplica

5. **TOTAL**: Se calcula automáticamente (Neto + todos los impuestos)

#### 3.4 Observaciones
- Puedes agregar una descripción como: "Factura de luz - Período Enero 2025"

### Paso 4: Guardar la Factura

1. **NO es necesario agregar productos** a la tabla de productos
2. Haz clic en **"Cargar Factura Directa"**
3. El sistema automáticamente:
   - Creará un "producto virtual" (ID 0) con la descripción "SERVICIO - [observación]"
   - Registrará la compra con estado = 1 (ingresada directamente)
   - Actualizará la cuenta corriente del proveedor
   - **NO actualizará stock** (porque es un servicio)

## Ejemplo Práctico: Factura EDEMSA

### Datos de Ejemplo:
- **Proveedor**: EDEMSA
- **Tipo**: Factura B
- **Fecha Emisión**: 15/01/2025
- **Punto de Venta**: 0001
- **Número Factura**: 00012345
- **SubTotal**: $15,000.00
- **IVA (21%)**: $3,150.00
- **Total**: $18,150.00
- **Observaciones**: "Factura de luz - Período Diciembre 2024"

### Pasos:
1. ✅ Seleccionar proveedor "EDEMSA"
2. ✅ Marcar "Cargar factura directa"
3. ✅ Seleccionar "Factura B"
4. ✅ Completar fecha, punto de venta y número
5. ✅ Ingresar SubTotal: 15000
6. ✅ Ingresar IVA: 3150
7. ✅ Verificar que Total = 18150
8. ✅ Agregar observación
9. ✅ Clic en "Cargar Factura Directa"

**Resultado**: 
- ✅ Compra registrada con ID interno
- ✅ Cuenta corriente de EDEMSA actualizada (+$18,150.00)
- ✅ Stock NO se modifica (es servicio)
- ✅ Producto virtual creado: "SERVICIO - Factura de luz - Período Diciembre 2024"

## Preguntas Frecuentes

### ¿Puedo agregar productos Y servicios en la misma factura?
Sí, puedes:
1. Agregar los productos físicos normalmente
2. Marcar "Cargar factura directa"
3. Los productos físicos actualizarán stock
4. El total se calculará con productos + impuestos

### ¿Qué pasa si no marco el checkbox de factura directa?
- Se creará una **orden de compra** (estado = 0)
- Tendrás que validar el ingreso después
- No podrás completar datos impositivos hasta validar

### ¿Cómo veo las facturas de servicios registradas?
- Ve a **Compras** → **Compras** (lista de compras ingresadas)
- Las facturas de servicios aparecen con estado = 1
- Puedes ver el detalle y el "producto virtual" creado

### ¿El producto virtual se guarda en el catálogo?
No, el producto virtual (ID 0) **NO se guarda** en la tabla `productos`. Solo se guarda en el JSON de la compra para mantener consistencia en los datos.

## Detección Automática de Servicios

El sistema detecta servicios automáticamente si:
1. El ID del producto es 0 (producto virtual)
2. La descripción contiene: "SERVICIO", "EDEMSA", "LUZ", "AGUA", "GAS", "INTERNET", "TELEFONIA"
3. El producto tiene stock = 0 Y descripción que sugiere servicio

Cuando detecta un servicio, **NO actualiza el stock**.

## Notas Importantes

- ✅ Las facturas de servicios se registran correctamente en cuenta corriente
- ✅ No afectan el stock de productos
- ✅ Se pueden imprimir normalmente
- ✅ Aparecen en reportes de compras
- ✅ Mantienen todos los datos impositivos
