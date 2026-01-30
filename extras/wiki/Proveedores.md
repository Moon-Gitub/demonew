# Proveedores

En esta sección se describe la **gestión de proveedores** y la **cuenta corriente** con proveedores. **Solo los usuarios con perfil Administrador** tienen acceso a estas pantallas.

---

## Administración de proveedores

**Ruta:** menú principal → Proveedores  
**Perfil:** solo Administrador

Aquí se hace el **ABM (Alta, Baja, Modificación)** de proveedores.

### Datos habituales de un proveedor

- **Nombre / Razón social**
- **CUIT / Número de identificación fiscal**
- **Domicilio**
- **Teléfono**
- **Correo electrónico**
- **Contacto** (nombre de la persona de contacto)
- **Estado:** activo/inactivo

### Acciones típicas

- **Alta:** crear nuevo proveedor con todos los datos.
- **Editar:** modificar datos de un proveedor existente.
- **Eliminar / Desactivar:** dar de baja un proveedor (suele ser baja lógica para no perder historial de compras).

Al **Crear compra** o **Ingreso de mercadería** se elige el proveedor de la lista; por eso es importante tenerlos cargados antes.

---

## Cuenta corriente de proveedores

**Ruta:** Proveedores (o submenú “Proveedores cuenta” / “Proveedores cuenta saldos” / “Proveedores pagos” / “Proveedores saldo”)  
**Perfil:** solo Administrador

La **cuenta corriente con proveedores** registra:

- **Débitos:** compras a crédito, notas de débito del proveedor, gastos asociados.
- **Créditos:** pagos realizados al proveedor, notas de crédito, devoluciones.

El **saldo** es lo que la empresa le debe al proveedor (saldo positivo) o lo que el proveedor nos debe (saldo negativo, menos habitual).

### Pantallas relacionadas

Según el menú pueden existir:

- **Proveedores cuenta:** listado de proveedores con saldo y/o movimientos.
- **Proveedores cuenta saldos:** ver saldos por proveedor.
- **Proveedores pagos:** registrar pagos a proveedores (efectivo, transferencia, cheque, etc.) para bajar la deuda.
- **Proveedores saldo:** ver detalle de saldo y movimientos por proveedor.

Al **Crear compra** se genera un débito en la cuenta del proveedor; al registrar un **pago** en Proveedores pagos (o similar) se genera un crédito y baja la deuda.

---

## Resumen

| Función | Descripción |
|--------|-------------|
| ABM Proveedores | Alta, edición y baja de proveedores. |
| Cuenta corriente | Débitos (compras) y créditos (pagos); saldo por proveedor. |
| Pagos | Registrar pagos a proveedores para bajar la deuda. |
| En Compras | Elegir proveedor al crear compra o ingreso. |

Para **crear una compra** e **ingreso de mercadería**, ver [Compras](Compras).
