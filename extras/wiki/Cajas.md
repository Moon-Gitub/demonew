# Cajas

En esta sección se describe la gestión de **cajas** y **cierres de caja**. Hay dos perspectivas: la del **Administrador** (administrar cajas y ver cierres) y la del **Vendedor/Cajero** (operar con su caja).

---

## Diferencia entre perfiles

- **Administrador** tiene:
  - **Administrar Caja:** crear y configurar cajas (nombre, estado, asignación).
  - **Cierres de caja:** ver y gestionar cierres (fechas, montos, diferencias).
- **Vendedor** tiene:
  - **Caja:** acceso a la caja asignada para abrir, operar y cerrar en su turno (pantalla “Caja” o “Cajas cajero”).

---

## Administrar Caja

**Ruta:** Cajas → Administrar Caja  
**Perfil:** solo Administrador

Aquí se **definen las cajas** del local (por ejemplo: Caja 1, Caja 2). Por cada caja suele configurarse:

- **Nombre o número** de caja.
- **Estado:** activa/inactiva.
- **Asignación:** a qué usuario(s) o sucursal corresponde (según implementación).

Las ventas y los cobros se asocian a una caja para poder hacer luego el cierre por caja y el arqueo.

---

## Cierres de caja

**Ruta:** Cajas → Cierres de caja  
**Perfil:** solo Administrador

En esta pantalla se **consultan y gestionan los cierres de caja**:

- Listado de cierres por fecha y caja.
- Montos: ventas del período, efectivo declarado, otros medios, diferencias.
- Quién abrió y quién cerró la caja.
- Posibilidad de reabrir un cierre o generar reportes (según diseño).

El cierre de caja es el proceso por el cual se “cierra” un turno: se registra cuánto dinero hay en caja (o cuánto se movió) y se cuadran ventas vs. efectivo/medios de pago. Así se detectan faltantes o sobrantes y se deja listo el siguiente turno.

---

## Caja (pantalla del Cajero / Vendedor)

**Ruta:** menú principal → Caja  
**Perfil:** Vendedor (o Cajero)

Esta es la pantalla donde el **cajero opera en su turno**:

- **Apertura de caja:** al iniciar el turno, se suele registrar un monto inicial (efectivo en caja) o confirmar apertura.
- **Durante el turno:** las ventas realizadas desde “Crear venta” se registran en esta caja (si el usuario tiene una caja asignada).
- **Cierre de caja:** al terminar el turno, el cajero declara el dinero que hay en caja (o los montos por medio de pago); el sistema calcula la diferencia con lo vendido y se registra el cierre.

Detalles exactos (botones “Abrir caja”, “Cerrar caja”, arqueo por medio de pago) pueden variar según la versión; la idea es que el Vendedor solo vea y use su propia caja, sin acceder a Administrar Caja ni a Cierres de otras cajas.

---

## Flujo típico

1. **Administrador** crea las cajas en Administrar Caja y asigna usuarios si aplica.
2. **Cajero** inicia turno: abre su caja (monto inicial si se pide).
3. **Cajero** realiza ventas en “Crear venta”; esas ventas quedan asociadas a su caja.
4. **Cajero** termina turno: cierra caja, declara montos y el sistema registra el cierre.
5. **Administrador** revisa en Cierres de caja los cierres y diferencias.

---

## Medios de pago y caja

Los medios de pago (efectivo, tarjeta, Mercado Pago, etc.) se configuran en **Empresa → Cargar Medios de Pago**. En la caja y en el cierre suelen desglosarse los montos por medio de pago para facilitar el arqueo y la conciliación.

Para **crear ventas** y cobrar, ver [Ventas](Ventas). Para **configurar medios de pago**, ver [Empresa](Empresa).
