# Beneficios y funcionalidades del sistema POS Moon

Este documento explica **qué pueden hacer** los usuarios con el sistema y **en qué los beneficia** el nuevo POS Moon: seguridad, control, ventas, cobros y administración.

---

## Resumen: ¿En qué me beneficia el nuevo sistema?

- **Ingreso rápido y seguro:** Un solo intento de login, protección contra intentos fallidos y contraseñas actualizadas.
- **Control por rol:** Cada usuario ve solo lo que necesita (Administrador todo, Vendedor lo esencial).
- **Múltiples listas de precio:** Precio público, empleados, trabajadores, etc., sin duplicar productos.
- **Medios de pago a medida:** Efectivo, tarjetas, transferencia, cheque, cuenta corriente, configurados desde el sistema.
- **Cobro con Mercado Pago:** Los clientes pueden pagar con tarjeta, transferencia o efectivo desde un solo botón.
- **Formatos de balanza:** Códigos de balanza interpretados automáticamente (peso, unidad, prefijos).
- **Actualizaciones sin perder config:** Actualizar el código desde GitHub sin pisar certificados ni logos.
- **Multiempresa y multi-usuario:** Varias empresas y usuarios con permisos por pantalla.

---

## 1. Login y seguridad

### Qué pueden hacer

- Ingresar con **usuario y contraseña** en una sola vez (sin tener que repetir).
- Ver una pantalla de login **personalizable** (logo, colores, fondo) según la empresa.
- Recibir mensajes claros si la contraseña es incorrecta o si la cuenta está bloqueada temporalmente.

### En qué los beneficia

- **Menos frustración:** El login funciona al primer intento gracias a que la sesión se guarda correctamente antes de redirigir.
- **Más seguridad:** Protección contra fuerza bruta: tras varios intentos fallidos la cuenta se bloquea un tiempo; las contraseñas se guardan con algoritmos actuales.
- **Imagen de marca:** El login puede llevar el logo y colores de la empresa para que clientes y empleados identifiquen el sistema.

---

## 2. Permisos por rol (qué ve cada usuario)

### Qué pueden hacer

- **Administrador:** Acceso a todo: empresa, usuarios, listas de precio, formatos de balanza, medios de pago, permisos por rol, productos, movimientos, cajas, ventas, clientes, compras, proveedores, integraciones y reportes.
- **Vendedor:** Acceso a lo necesario para el día a día: Inicio, Productos, Imprimir precios, Caja (cajero), Ventas, Crear venta, Clientes y Asistente virtual.
- El administrador puede **asignar qué pantallas ve cada rol** desde Permisos por rol (lista de pantallas con checkboxes por rol).

### En qué los beneficia

- **Menos distracciones:** El vendedor no ve menús de configuración ni reportes que no usa; se enfoca en ventas y caja.
- **Más control:** El dueño o encargado decide quién puede modificar precios, usuarios o medios de pago.
- **Menos riesgo:** Reducir pantallas sensibles por rol ayuda a evitar cambios accidentales o mal uso.

---

## 3. Listas de precio

### Qué pueden hacer

- Crear **varias listas de precio** por empresa: Precio Público, Precio Costo, Empleados, Trabajadores Valle Grande, etc.
- Cada lista puede usar **precio de venta o precio de costo** como base y aplicar **descuento en porcentaje** (o ninguno).
- Asignar a cada **usuario** qué listas puede usar al hacer una venta o presupuesto.
- Activar/desactivar listas y ordenar cómo se muestran.

### En qué los beneficia

- **Un solo producto, varios precios:** No duplicar productos para “precio mayorista” o “empleados”; se elige la lista en el momento de la venta.
- **Descuentos claros:** Por ejemplo “Empleados 20% sobre precio de venta” se aplica automáticamente.
- **Flexibilidad:** Agregar o quitar listas y asignarlas por usuario sin tocar código.

---

## 4. Medios de pago

### Qué pueden hacer

- Definir **medios de pago** desde el sistema: Efectivo, Tarjeta Débito, Tarjeta Crédito, Cheque, Transferencia, Cuenta Corriente, etc.
- Marcar para cada medio si requiere: código de operación, banco, número de referencia, fecha de vencimiento.
- Usar estos medios en **ventas**, **presupuestos** y **cuenta corriente** (clientes y proveedores).
- Ordenar y activar/desactivar medios.

### En qué los beneficia

- **Adaptado al negocio:** No quedarse solo con “Efectivo” y “Tarjeta”; agregar cheques, transferencias o cuenta corriente según cómo cobran y pagan.
- **Datos completos:** Si un medio pide número de operación o banco, el sistema lo solicita y queda registrado para auditoría y conciliación.
- **Un solo lugar:** Los mismos medios se usan en venta, presupuesto y cuentas corrientes.

---

## 5. Formatos de balanza

### Qué pueden hacer

- Configurar **cómo se interpretan los códigos de balanza** (prefijo, posiciones de producto, posición de peso o cantidad, divisor, cantidad fija).
- Definir varios formatos por empresa (por ejemplo “prefijo 20000”, “prefijo 20”, “prefijo 21”) con reglas distintas (peso en kg, unidad con cantidad fija, etc.).
- Al escanear o cargar un código en una venta, el sistema **identifica el producto y la cantidad** automáticamente según el formato.

### En qué los beneficia

- **Menos errores:** No cargar a mano producto y peso; el código de la balanza se decodifica solo.
- **Varias balanzas:** Si tienen más de un tipo de balanza o de prefijo, cada uno puede tener su formato.
- **Agilidad en mostrador:** Escanear y listo; el sistema aplica las reglas configuradas.

---

## 6. Sistema de cobro (Mercado Pago)

### Qué pueden hacer

- Mostrar en el cabezote (o en la zona de cobro) un **resumen de deuda** del cliente (cuando el sistema está vinculado a la base central Moon).
- Ofrecer un **botón “Pagar con Mercado Pago”** para que el cliente pague con tarjeta, transferencia, efectivo en cuotas, etc., desde el link de Mercado Pago.
- Recibir **notificaciones de pago** (webhook) y actualizar el estado del cobro.
- Si no hay conexión a la base Moon o no está configurado Mercado Pago, el sistema sigue funcionando con el resto de las funciones (solo no se muestra el widget de cobro).

### En qué los beneficia

- **Más formas de pago:** El cliente elige tarjeta, transferencia o efectivo desde Mercado Pago sin que el comercio tenga que gestionar cada medio por separado.
- **Menos manejo de efectivo:** Quien prefiera puede pagar con tarjeta o transferencia desde el mismo sistema.
- **Trazabilidad:** Los pagos quedan registrados y se pueden conciliar con lo que muestra Mercado Pago.

---

## 7. Ventas, cajas y reportes

### Qué pueden hacer

- **Crear venta** desde la caja: cargar productos, elegir lista de precio, aplicar descuentos, registrar medios de pago.
- Gestionar **presupuestos** y convertirlos en ventas.
- Administrar **cajas** y **cierres de caja**.
- Ver **ventas**, **productos vendidos**, **informes de rentabilidad** y otros reportes según permisos.
- Trabajar con **clientes** y **cuenta corriente** (saldos, movimientos).
- Gestionar **compras**, **ingresos de mercadería** y **proveedores** con cuenta corriente.

### En qué los beneficia

- **Todo en un solo sistema:** Ventas, stock, clientes, proveedores y caja integrados.
- **Control de caja:** Cierres por turno o por día para saber cuánto se vendió y con qué medios.
- **Información para decidir:** Reportes para ver qué se vende más, rentabilidad y movimientos de productos.

---

## 8. Actualizaciones y despliegue

### Qué pueden hacer (administrador / técnico)

- **Actualizar el código** desde GitHub en cada servidor (pull o script) sin borrar archivos propios del negocio.
- **Excluir carpetas** como `facturacion/` (certificados AFIP) o `vistas/img/plantilla/` (logos) para que no se pisen al actualizar.
- **Clonar el sistema desde cero** en un nuevo hosting: clone del repo, creación de `.env`, carga de base de datos y copia de certificados/logo.

### En qué los beneficia

- **Sistema al día:** Recibir mejoras y correcciones sin perder la configuración local.
- **Menos riesgo:** Certificados y logos no se borran ni se sobrescriben por error al actualizar.
- **Migración ordenada:** Pasos claros para llevar el sistema a otro servidor o para dar de alta una nueva sucursal.

---

## 9. Multiempresa y configuración por empresa

### Qué pueden hacer

- Gestionar **varias empresas** en la misma instalación (cada usuario puede estar asociado a una empresa).
- Configurar por empresa: **datos fiscales**, **listas de precio**, **formatos de balanza**, **logo y colores del login**, etc.
- Cada empresa puede tener sus propios **usuarios**, **productos**, **clientes** y **proveedores** según la configuración.

### En qué los beneficia

- **Un solo sistema para varios negocios:** Útil para cadenas, franquicias o quien administra más de un local.
- **Imagen y datos propios:** Cada empresa mantiene su logo, colores y datos sin mezclarlos con otras.

---

## Dónde encontrar más detalle

- **Uso día a día:** [Inicio de sesión y perfiles](Inicio-de-sesion-y-perfiles), [Empresa](Empresa), [Productos](Productos), [Ventas](Ventas), [Crear venta – Paso a paso](Crear-venta-paso-a-paso), [Cajas](Cajas), [Clientes](Clientes), [Compras](Compras), [Proveedores](Proveedores).
- **Cobro e integraciones:** [Integraciones y cobro](Integraciones-y-cobro).
- **Informes:** [Reportes](Reportes).
- **Términos y ayuda:** [Glosario y soporte](Glosario-y-soporte).

---

*Documentación para el usuario final – Sistema POS Moon.*
