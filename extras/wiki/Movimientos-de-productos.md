# Movimientos de productos

En esta sección se describe el flujo de **movimientos de productos**: generar movimiento, validar movimiento y consultar movimientos validados. **Solo los usuarios con perfil Administrador** tienen acceso a estas pantallas.

---

## Para qué sirven los movimientos

Los **movimientos de productos** permiten registrar entradas o salidas de stock que no son una venta ni una compra directa, por ejemplo:

- Ajustes de inventario (corrección por diferencia física).
- Traslados entre depósitos o sucursales (si el sistema lo soporta).
- Mermas, devoluciones a proveedor, muestras.
- Cualquier otro ingreso o egreso de mercadería que se quiera documentar.

El flujo en tres pasos (generar → validar → ver validados) ayuda a que una persona prepare el movimiento y otra lo apruebe, manteniendo control y trazabilidad.

---

## Generar Movimiento

**Ruta:** Mov. De Productos → Generar Movimiento

Aquí se **crea un nuevo movimiento** de productos:

1. Seleccionar **tipo de movimiento** (si aplica: ingreso, egreso, ajuste, etc.).
2. Indicar **productos y cantidades** (qué se mueve y cuánto).
3. Completar **observaciones o motivo** (opcional pero recomendable).
4. Guardar o enviar el movimiento. El movimiento queda en estado “pendiente” o “nuevo” hasta que alguien lo valide.

Quien genera el movimiento suele ser un encargado de depósito o un vendedor; quien valida suele ser un responsable o el Administrador.

---

## Validar Movimiento

**Ruta:** Mov. De Productos → Validar Movimiento

En esta pantalla se listan los **movimientos pendientes de validación** (los que se generaron pero aún no se aprobaron). El validador puede:

- **Aprobar** el movimiento: se confirma y el stock se actualiza según el tipo de movimiento (suma o resta).
- **Rechazar** el movimiento: no se aplica al stock y el movimiento queda registrado como rechazado (según diseño del sistema).
- **Ver detalle** del movimiento (productos, cantidades, quien lo generó, fecha).

Una vez validado (aprobado), el movimiento pasa a la lista de “Movimientos Validados” y ya no se puede modificar; queda como historial.

---

## Movimientos Validados

**Ruta:** Mov. De Productos → Movimientos Validados

Aquí se **consultan los movimientos que ya fueron validados** (aprobados). Sirve para:

- Ver historial de ajustes y movimientos.
- Auditar quién validó y cuándo.
- Cruzar con el inventario físico.

Suele haber filtros por fecha, tipo de movimiento y usuario. No se editan ni se eliminan desde aquí; son solo consulta.

---

## Resumen del flujo

1. **Generar Movimiento:** se crea el movimiento con productos y cantidades → queda “pendiente”.
2. **Validar Movimiento:** un responsable aprueba o rechaza → si aprueba, el stock se actualiza.
3. **Movimientos Validados:** se consulta el historial de movimientos ya validados.

---

## Relación con stock

- Al **aprobar** un movimiento de tipo ingreso o ajuste positivo, el stock de los productos involucrados **aumenta**.
- Al **aprobar** un movimiento de tipo egreso o ajuste negativo, el stock **disminuye**.
- Las **ventas** y las **compras/ingresos de mercadería** también modifican el stock por sus propios flujos; los movimientos son para casos que no son venta ni compra directa.

Para **compras e ingreso de mercadería**, ver [Compras](Compras). Para **stock bajo** y reportes, ver [Reportes](Reportes) o [Productos](Productos).
