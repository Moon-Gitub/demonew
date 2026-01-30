# Glosario y soporte

En esta página se definen **términos habituales** del sistema y se indica **dónde buscar ayuda**.

---

## Glosario

| Término | Significado |
|--------|-------------|
| **ABM** | Alta, Baja y Modificación. Se refiere a las pantallas donde se crean, editan o desactivan registros (productos, clientes, proveedores, usuarios, etc.). |
| **Balanza (código de)** | Código de barras que imprime una balanza; contiene prefijo, ID de producto y a veces peso o cantidad. El sistema lo interpreta según los Formatos de Balanza. |
| **Cabezote** | Barra superior de la pantalla (logo, nombre de usuario, botón de pago, etc.). Puede incluir o no el sistema de cobro con Mercado Pago. |
| **Caja** | Punto de cobro (físico o lógico). Las ventas se asocian a una caja; al cerrar caja se hace el arqueo y el cierre del turno. |
| **Cierre de caja** | Proceso al final del turno: se declara el dinero en caja (o por medio de pago) y se cuadra con las ventas del período. |
| **Combo** | Conjunto de productos que se venden como una oferta única (ej.: Combo desayuno = café + medialunas + jugo). |
| **Cuenta corriente** | Registro de débitos (ventas a crédito, compras) y créditos (pagos) de un cliente o proveedor; el saldo es lo que se debe o lo que nos deben. |
| **Formato de balanza** | Regla configurada en Empresa que define cómo se interpreta un código de balanza (prefijo, posición del producto, cantidad/peso, divisor). |
| **Lista de precio** | Conjunto de reglas para calcular el precio en venta: base (precio_venta o precio_compra) y opcionalmente un descuento en %. Cada usuario puede tener asignadas varias listas. |
| **Modal de cobro** | Ventana que aparece en el cabezote para recordar el pago del abono del sistema (Moon); incluye el botón “Pagar con Mercado Pago”. |
| **Movimiento de productos** | Ingreso o egreso de stock que no es una venta ni una compra directa (ajustes, mermas, traslados). Se genera y luego se valida. |
| **POS** | Punto de venta (Point of Sale). En este sistema, la pantalla “Crear venta” es el POS. |
| **Presupuesto** | Cotización o venta en borrador que aún no se cobró o no se convirtió en venta definitiva. |
| **Precio de compra** | Costo del producto (base para listas que usan “precio_compra” y para informes de rentabilidad). |
| **Precio de venta** | Precio al público (base para listas que usan “precio_venta”). |
| **Stock** | Cantidad en existencia de un producto. Se actualiza con ventas, compras, ingresos de mercadería y movimientos de productos. |
| **Validar movimiento** | Aprobar un movimiento de productos pendiente; al aprobar, el stock se actualiza. |

---

## Dónde buscar ayuda

- **Dudas de uso (usuario final):** consulte esta wiki por módulo (Inicio de sesión, Empresa, Productos, Ventas, Clientes, etc.). Use el [índice en Home](Home).
- **Asistente Virtual:** dentro del sistema, use **Integraciones → Asistente Virtual** (o el enlace al chat) para preguntas rápidas sobre cómo hacer una tarea.
- **Problemas de acceso o permisos:** contacte al **Administrador** del sistema (usuarios, perfiles, listas de precio asignadas).
- **Configuración técnica (Mercado Pago, integraciones, servidor):** contacte al responsable técnico o al equipo de desarrollo/soporte que le haya entregado el sistema.
- **Documentación técnica o de instalación:** en el repositorio del proyecto suele haber una carpeta `extras/` o `documentacion/` con guías para instalación, migraciones y desarrollo; la wiki está orientada al usuario final.

---

## Enlaces rápidos de esta wiki

- [Home](Home) — Índice general
- [Inicio de sesión y perfiles](Inicio-de-sesion-y-perfiles)
- [Empresa](Empresa)
- [Productos](Productos)
- [Ventas](Ventas)
- [Crear venta – Paso a paso](Crear-venta-paso-a-paso)
- [Sistema offline](Sistema-offline)
- [Cajas](Cajas)
- [Clientes](Clientes)
- [Compras](Compras)
- [Proveedores](Proveedores)
- [Integraciones y cobro](Integraciones-y-cobro)
- [Reportes](Reportes)
