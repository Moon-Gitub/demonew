# Clientes

En esta sección se describe la **gestión de clientes** y la **cuenta corriente** de clientes. Tanto Administradores como Vendedores pueden acceder a Clientes; los detalles de permisos (editar, dar crédito, etc.) dependen del perfil.

---

## Administración de clientes

**Ruta:** menú principal → Clientes  
**Perfil:** Administrador y Vendedor

Aquí se hace el **ABM (Alta, Baja, Modificación)** de clientes.

### Datos habituales de un cliente

- **Nombre / Razón social**
- **Documento** (DNI, CUIT, etc.)
- **Domicilio**
- **Teléfono**
- **Correo electrónico**
- **Condición frente al IVA** (si aplica: consumidor final, monotributista, responsable inscripto)
- **Límite de crédito** o habilitación para cuenta corriente (si el sistema lo soporta)
- **Estado:** activo/inactivo

### Acciones típicas

- **Alta:** crear nuevo cliente con todos los datos.
- **Editar:** modificar datos de un cliente existente.
- **Eliminar / Desactivar:** dar de baja un cliente (suele ser baja lógica para no perder historial de ventas).

En “Crear venta” se puede elegir el cliente para asociar la venta y, si tiene cuenta corriente, registrar el débito o el pago.

---

## Cuenta corriente de clientes

**Ruta:** Clientes (o submenú “Cuenta corriente” / “Clientes cuenta”)  
**Perfil:** según configuración; suele ser Administrador quien ve saldos y movimientos completos.

La **cuenta corriente** registra:

- **Débitos:** ventas a crédito, notas de débito, intereses (si se cargan).
- **Créditos:** pagos del cliente, notas de crédito, devoluciones.

El **saldo** es la diferencia entre lo que el cliente debe y lo que pagó. Si el saldo es positivo, el cliente debe dinero; si es negativo, tiene crédito a favor.

### Pantallas relacionadas

Según el menú pueden existir:

- **Clientes cuenta** o **Cuenta corriente:** listado de clientes con saldo y/o movimientos.
- **Clientes cuenta saldos** / **Clientes cuenta deuda:** ver saldos o deudas por cliente.
- **Clientes cuenta:** detalle de movimientos de un cliente (débitos, créditos, fechas, comprobantes).

Desde ahí se suelen poder **registrar pagos** del cliente (efectivo, transferencia, etc.) para bajar el saldo deudor.

---

## Uso en ventas

Al **Crear venta**:

- Si selecciona un **cliente con cuenta corriente**, la venta puede registrarse como “a cuenta” (débito) y el cliente paga después.
- Si no selecciona cliente o elige “Consumidor final”, la venta es “contado” (se cobra en el momento).

El límite de crédito (si está implementado) puede impedir superar un monto adeudado; si el cliente supera el límite, el sistema puede advertir o bloquear la venta a cuenta.

---

## Resumen

| Función | Descripción |
|--------|-------------|
| ABM Clientes | Alta, edición y baja de clientes. |
| Cuenta corriente | Débitos (ventas a crédito) y créditos (pagos); saldo por cliente. |
| En Crear venta | Elegir cliente para asociar venta y/o registrar venta a cuenta. |

Para **crear una venta** y elegir cliente, ver [Ventas](Ventas). Para **cobros y Mercado Pago**, ver [Integraciones y cobro](Integraciones-y-cobro).
