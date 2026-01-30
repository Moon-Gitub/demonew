# Compras

En esta sección se describe la **gestión de compras**: administración de compras, crear compra e ingreso de mercadería. **Solo los usuarios con perfil Administrador** tienen acceso a estas pantallas.

---

## Administración de compras (Adm. Compras)

**Ruta:** Compras → Adm. Compras  
**Perfil:** solo Administrador

Aquí se **listan y gestionan las compras** ya cargadas en el sistema:

- Ver compras por fecha, proveedor, usuario.
- Ver detalle de cada compra (ítems, cantidades, precios, total).
- Anular o modificar compras (según diseño).
- Filtrar y exportar (Excel, PDF) si está disponible.

Es la pantalla de consulta y control de todo lo comprado.

---

## Crear compra

**Ruta:** Compras → Crear Compra  
**Perfil:** solo Administrador

Aquí se **registra una nueva compra** a un proveedor:

1. **Proveedor:** seleccionar el proveedor (debe estar dado de alta en Proveedores).
2. **Fecha y comprobante:** fecha de la compra, número de factura/remito del proveedor (si aplica).
3. **Productos y cantidades:** cargar ítems comprados, cantidades y precios de compra (costo).
4. **Total:** el sistema calcula el total según ítems y precios.
5. **Guardar:** al guardar, la compra queda registrada y normalmente se actualiza el **stock** (entrada de mercadería) y la **cuenta corriente del proveedor** (deuda con el proveedor).

Así se lleva el registro de qué se compró, a quién y a qué precio, y se mantiene el stock y la deuda con proveedores al día.

---

## Ingreso de mercadería

**Ruta:** Compras → Ingreso Mercadería  
**Perfil:** solo Administrador

Esta pantalla sirve para **registrar ingresos de mercadería** que pueden o no estar vinculados a una compra previa:

- **Ingreso por compra:** asociar el ingreso a una compra ya cargada (por ejemplo cuando llega la mercadería y se verifica contra la factura).
- **Ingreso directo:** cargar producto, cantidad y (opcionalmente) proveedor, sin compra previa (ajustes, donaciones, traslados, etc.).

Al confirmar el ingreso, el **stock** de los productos suele actualizarse (aumenta la cantidad en existencia). Si está vinculado a compra, puede actualizarse también el estado de la compra (recibido, pendiente, etc.) según el diseño.

---

## Relación con stock y proveedores

- **Crear compra** y **Ingreso mercadería** aumentan el stock de los productos ingresados.
- Las **ventas** y los **movimientos de productos** (egresos) disminuyen el stock.
- Las compras generan o aumentan la **deuda con el proveedor** en la cuenta corriente de proveedores; los **pagos a proveedores** la reducen.

Para **proveedores** y cuenta corriente de proveedores, ver [Proveedores](Proveedores). Para **movimientos de productos** (ajustes que no son compra), ver [Movimientos de productos](Movimientos-de-productos).
