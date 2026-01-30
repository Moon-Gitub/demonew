# Crear venta – Paso a paso (cada clic y cada campo)

Esta página describe **cada elemento** de la pantalla **Crear venta** (punto de venta web): cada campo, cada botón y qué pasa al hacer cada clic.

---

## Acceso

- **Clic en el menú:** Menú lateral → **Ventas** → **Crear venta**.
- **Perfil:** Administrador y Vendedor.
- **Requisito:** el usuario debe tener al menos una **lista de precio** asignada (Empresa → Usuarios). Si solo ve una lista, el Administrador debe asignarle más y el usuario debe cerrar sesión y volver a entrar.

---

## Parte superior del formulario – Campos y qué hace cada uno

| Campo / Elemento | Dónde está | Qué hace |
|------------------|------------|----------|
| **Fecha de emisión** | Campo de solo lectura o con selector de fecha | Muestra la fecha de la venta. Al hacer **clic** (si es editable) puede abrir un calendario para cambiar la fecha. |
| **Hora de emisión** | Campo tipo hora | Muestra la hora actual; se puede editar para registrar la hora exacta de la venta. |
| **Lista de precio** | Desplegable (select) con nombre "Lista" o similar; en el código suele ser **radioPrecio** | Al hacer **clic** se despliegan las listas asignadas al usuario (Precio Público, Empleados, etc.). Al **elegir otra opción**, el precio de todos los productos ya cargados en la grilla se **recalcula** al instante según la nueva lista (precio base + descuento % si aplica). |
| **Tipo de comprobante / Punto de venta** (si se muestra) | Desplegables para facturación | Se usan para imprimir o facturar; según configuración de la empresa. **Clic** en el desplegable para elegir tipo y punto de venta. |
| **Cliente** | Campo de texto con autocompletado; en el código suele ser **autocompletarClienteCaja** | Al hacer **clic** dentro del campo y **empezar a escribir** (nombre, documento o código del cliente), aparece una lista de clientes que coinciden. Al hacer **clic** en uno de la lista, ese cliente queda seleccionado y su ID se guarda en un campo oculto (**seleccionarCliente**). Si no elige nadie, queda "1-Consumidor Final". La venta se registrará a nombre del cliente elegido (para cuenta corriente o solo para el comprobante). |

---

## Cargar productos – Campo de búsqueda y grilla

| Elemento | Qué hace |
|----------|----------|
| **Campo "Código" / "Producto"** (en el código suele ser **ventaCajaDetalle**) | Campo donde se escribe el código de barras o el nombre del producto. Al hacer **clic** y **escribir**: (1) Si es **código de balanza**: el sistema interpreta el código (según Formatos de Balanza) y, si encuentra producto y cantidad, **agrega el ítem a la grilla** sin tener que elegir de una lista. (2) Si es **código normal o nombre**: aparece un **menú desplegable (autocompletado)** con los productos que coinciden. Al hacer **clic** en una fila de ese menú, ese producto se **agrega a la grilla** con cantidad 1 (o la que corresponda por balanza). Si escanea un código de balanza, no hace falta clic en el menú: se agrega solo. |
| **Grilla de ítems (tabla de productos de la venta)** | Muestra los productos ya agregados. Columnas típicas: **Cantidad**, **Producto**, **Precio unitario**, **Subtotal** (y a veces acciones). **Clic en la celda de cantidad** de una fila: permite editar la cantidad; al cambiar y salir del campo (o Enter), se recalcula el subtotal de esa fila y el total general. **Clic en "Eliminar" o ícono de basura** en una fila: quita ese ítem del ticket. |
| **Botón "Agregar" / "+"** (si existe junto al campo de producto) | Equivalente a elegir el producto del autocompletado: agrega el producto actualmente seleccionado o el último buscado con cantidad 1. |

---

## Total y área de cobro

| Elemento | Qué hace |
|----------|----------|
| **Campo o etiqueta "Total" / "Total neto"** (en el código puede ser **nuevoPrecioNetoCajaForm** o similar) | Muestra el **total de la venta** (suma de los subtotales de cada fila). Se actualiza solo al agregar, quitar o cambiar cantidades/precios. No se edita a mano salvo descuento global (si existe). |
| **Campos "Descuento global" / "Interés"** (si existen) | Si el sistema los muestra, al **escribir** un monto o porcentaje se ajusta el total a cobrar. |
| **Botón "Cobrar" / "Guardar venta" / "Finalizar venta"** (en el código suele ser **btnGuardarVentaCaja**) | Al hacer **clic**: si no hay ítems en la grilla, puede mostrar un aviso ("Agregue productos"). Si hay ítems, **abre el modal de cobro** (#modalCobrarVenta) para elegir medio(s) de pago y confirmar. No guarda la venta hasta que en el modal se confirme el cobro. |

---

## Modal de cobro (#modalCobrarVenta) – Cada clic

Este modal aparece al hacer clic en **Cobrar** / **Guardar venta** cuando hay productos en el ticket.

| Elemento | Qué hace |
|----------|----------|
| **Título del modal** ("Cobrar venta", "Registrar pago", etc.) | Solo informativo. |
| **Desplegable "Método de pago"** (en el código suele ser **nuevoMetodoPagoCaja**) | Al hacer **clic** se despliegan los medios de pago configurados en Empresa → Cargar Medios de Pago (Efectivo, Tarjeta débito, Tarjeta crédito, Mercado Pago, etc.). Se elige **uno** para el monto que se va a agregar. |
| **Campo "Monto" / "Valor"** (p. ej. **nuevoValorEntrega**) | Se escribe el **monto** que se está pagando con el método elegido (ej.: 5000 en efectivo). |
| **Botón "Agregar" / "+" / "Agregar medio de pago"** (en el código suele ser **agregarMedioPago**) | Al hacer **clic**: toma el método de pago seleccionado y el monto escrito, y los **agrega como una fila** en la tabla de medios de pago (**listadoMetodosPagoMixto**). Puede limpiar el campo monto para cargar otro. Así se puede registrar un pago mixto (ej.: parte efectivo, parte tarjeta). |
| **Tabla "Medios de pago"** (**listadoMetodosPagoMixto**) | Muestra cada medio agregado y su monto. **Clic en "Eliminar" o ícono de basura** en una fila (si existe): quita ese medio de pago del listado. |
| **Total de la venta** (en el modal) | Muestra el total a pagar. Debe coincidir con la suma de los montos cargados en la tabla de medios de pago. |
| **Campo "Saldo" / "Resto"** (si existe, p. ej. **nuevoValorSaldo**) | Muestra cuánto falta por asignar (total − suma de medios ya cargados) o el vuelto si se pasó. |
| **Datos de cuenta corriente** (si el cliente tiene cuenta) | Solo informativo: puede mostrar saldo anterior del cliente. |
| **Botón "Cancelar" / "Cerrar"** (pie del modal) | Al hacer **clic** cierra el modal **sin** guardar la venta; vuelve a la pantalla Crear venta con el ticket intacto. |
| **Botón "Confirmar" / "Cobrar" / "Guardar venta"** (pie del modal) | Al hacer **clic**: valida que la suma de los medios de pago coincida con el total (o lo permitido por el sistema). Si está todo bien, **guarda la venta** en el servidor, descuenta el stock, actualiza la cuenta del cliente si corresponde, cierra el modal y suele **vaciar la grilla** o mostrar mensaje de éxito. Si la suma no cuadra, puede mostrar un aviso y no guardar. |

**Resumen del flujo en el modal:** elegir método de pago → escribir monto → clic en "Agregar medio de pago" → repetir si es pago mixto → clic en "Confirmar" / "Cobrar" para guardar la venta.

---

## Cambiar la lista de precio en medio de la venta

1. Hacer **clic** en el desplegable **Lista de precio** (**radioPrecio**).
2. Elegir **otra lista** (ej.: pasar de "Precio Público" a "Empleados").
3. Sin hacer más clics, el sistema **recalcula** el precio unitario de **todos** los ítems ya cargados según la nueva lista (base + descuento % si aplica) y actualiza subtotales y total. No hace falta volver a cargar los productos.

---

## Código de balanza – Resumen

- **Dónde:** en el **mismo campo** donde se escribe el código de producto (**ventaCajaDetalle**).
- **Qué hacer:** escanear el ticket de la balanza o **pegar** el código.
- **Qué hace el sistema:** según los **Formatos de Balanza** (Empresa → Formatos de Balanza), detecta prefijo, extrae ID de producto y cantidad (peso o unidades) y **agrega el ítem a la grilla** con ese producto y cantidad. El precio se calcula con la lista de precio actual.
- Si el código no coincide con ningún formato o el ID de producto no existe, no se agrega nada; hay que revisar formato y productos.

---

## Errores frecuentes y qué revisar

| Problema | Qué revisar |
|----------|-------------|
| No ve todas las listas de precio | Empresa → Usuarios: asignar más listas al usuario; luego cerrar sesión y volver a entrar. |
| El código de balanza no carga el producto | Que el producto exista en Administrar Productos con el ID que trae el código; que el prefijo coincida con un Formato de Balanza activo en Empresa → Formatos de Balanza. |
| El precio no cambia al cambiar la lista | Elegir **otra** opción en el desplegable Lista de precio (**radioPrecio**); el recálculo es al cambiar la selección. |
| No puede cobrar / no hay medios de pago | Empresa → Cargar Medios de Pago: el Administrador debe dar de alta al menos un medio. |
| El modal de cobro no deja confirmar | Verificar que la **suma de los montos** agregados en el modal sea igual al total de la venta. |

---

## Resumen del flujo (orden de clics)

1. **Clic** en **Ventas** → **Crear venta** en el menú.
2. (Opcional) **Clic** en el campo **Cliente** (**autocompletarClienteCaja**), escribir y **clic** en el cliente en la lista.
3. **Clic** en el desplegable **Lista de precio** (**radioPrecio**) y elegir la lista.
4. **Clic** en el campo **Código/Producto** (**ventaCajaDetalle**), escribir código o nombre y **clic** en el producto en el menú (o escanear código de balanza).
5. Repetir paso 4 para más productos; si hace falta, **clic** en la celda de cantidad en la grilla para editar.
6. Revisar el **Total**.
7. **Clic** en el botón **Cobrar** / **Guardar venta** (**btnGuardarVentaCaja**).
8. En el modal: elegir **Método de pago**, escribir **Monto**, **clic** en **Agregar medio de pago**; repetir si es mixto.
9. **Clic** en **Confirmar** / **Cobrar** en el pie del modal → la venta se guarda y el ticket se vacía.

Para **listas de precio** y **formatos de balanza**, ver [Empresa](Empresa). Para **administración de ventas** y **presupuestos**, ver [Ventas](Ventas). Para **vender sin internet**, ver [Sistema offline](Sistema-offline).
