# Empresa

En el menú **Empresa** se configura todo lo que afecta a la organización: datos de la empresa, usuarios, listas de precio, formatos de balanza y medios de pago. **Solo los usuarios con perfil Administrador** pueden acceder a estas pantallas.

---

## Datos Empresa

**Ruta:** Empresa → Datos Empresa

Aquí se cargan y editan los datos legales y de contacto de la empresa, por ejemplo:

- Razón social
- CUIT / Número de identificación fiscal
- Domicilio, teléfono, correo
- Cualquier otro dato que use el sistema para facturación, reportes o integraciones

También suele configurarse aquí la **integración con Mercado Pago** (credenciales para cobros). Si existe la opción “Configuración de Mercado Pago” o similar dentro de Datos Empresa o de Integraciones, debe completarse para que el cobro con Mercado Pago funcione.

**Recomendación:** Mantener estos datos actualizados; afectan facturas, reportes y cobros.

---

## Usuarios

**Ruta:** Empresa → Usuarios

Desde aquí el Administrador:

- **Crea** nuevos usuarios (nombre de usuario, contraseña, perfil).
- **Edita** usuarios existentes (cambiar contraseña, perfil, nombre, estado activo/inactivo).
- **Asigna el perfil:** Administrador o Vendedor (ver [Inicio de sesión y perfiles](Inicio-de-sesion-y-perfiles)).
- **Asigna listas de precio:** al editar un usuario se pueden marcar qué listas de precio podrá usar ese usuario en “Crear venta”. Si un vendedor solo ve una lista, suele deberse a que en Usuarios solo tiene asignada esa lista; al guardar el usuario con todas las listas deseadas, al volver a “Crear venta” debería verlas todas.

**Importante:** Después de cambiar las listas de precio de un usuario, el usuario debe cerrar sesión y volver a entrar para que los cambios se apliquen correctamente.

---

## Listas de Precio

**Ruta:** Empresa → Listas de Precio

Las **listas de precio** definen con qué base se calcula el precio del producto en una venta (por ejemplo precio de venta o precio de compra) y si se aplica un descuento (por ejemplo porcentaje).

### Conceptos

- **Código:** identificador interno de la lista (ej.: `precio_venta`, `empleados`). No suele mostrarse al cliente.
- **Nombre:** texto que ve el usuario en “Crear venta” (ej.: “Precio Público”, “Empleados”).
- **Base de precio:** columna del producto que se usa como base:
  - `precio_venta` → precio de venta al público.
  - `precio_compra` → precio de costo (útil para listas internas o informativas).
- **Tipo de descuento:** normalmente “ninguno” o “porcentaje”.
- **Valor de descuento:** si el tipo es “porcentaje”, aquí se indica el % (ej.: 15 para 15 %).
- **Orden:** sirve para ordenar las listas en pantalla.
- **Activo:** si está activa, la lista se ofrece en ventas (y a los usuarios que tengan asignada esa lista).

### Ejemplos típicos

| Código | Nombre | Base | Descuento | Uso |
|--------|--------|------|------------|-----|
| precio_venta | Precio Público | precio_venta | ninguno | Ventas normales |
| precio_compra | Precio Costo | precio_compra | ninguno | Uso interno / referencia |
| trabajadores_valle_grande | Trabajadores Valle Grande | precio_venta | 15 % | Convenio |
| empleados | Empleados | precio_venta | 20 % | Personal |

El Administrador puede **crear, editar y desactivar** listas. Los usuarios solo ven en “Crear venta” las listas que tienen asignadas en **Empresa → Usuarios**.

Al **cambiar la lista seleccionada** en “Crear venta”, el sistema puede recalcular automáticamente los precios de los ítems ya cargados según la nueva lista.

---

## Formatos de Balanza

**Ruta:** Empresa → Formatos de Balanza

Los **formatos de balanza** le indican al sistema cómo interpretar los **códigos de barras que imprime la balanza** (peso, código de producto, etc.) cuando se escanea o se ingresa el código en “Crear venta”.

### Para qué sirve

- En “Crear venta” se puede cargar un producto escaneando el ticket de la balanza o pegando el código.
- El sistema usa la tabla de formatos para:
  - Detectar el **prefijo** del código (ej.: 20, 21, 20000).
  - Saber en qué **posición** y **longitud** está el ID de producto.
  - Saber si la cantidad es **peso** (ej. gramos) o **unidades** y en qué posición está.
  - Aplicar un **divisor** si el peso viene en gramos y se vende en kg.

Así se evita tener que configurar cada balanza a mano en el código; todo se define en esta pantalla.

### Campos principales (resumen)

- **Nombre:** descripción del formato (ej.: “Balanza 20000 (peso en kg)”).
- **Prefijo:** inicio del código que identifica este formato (ej.: 20000, 20, 21).
- **Posición y longitud del producto:** desde qué carácter y cuántos dígitos forman el ID de producto.
- **Modo cantidad:** peso (se toma de una parte del código), unidad (cantidad fija, ej. 1) o ninguno.
- **Posición y longitud de cantidad:** si es peso, de dónde se toma el número (ej. gramos).
- **Factor divisor:** por ejemplo 1000 para pasar gramos a kg.
- **Cantidad fija:** cuando el modo es “unidad”, cuántas unidades se cargan (normalmente 1).
- **Orden / Activo:** orden de evaluación y si el formato está habilitado.

El Administrador puede **crear, editar y desactivar** formatos. Los ejemplos iniciales suelen incluir formatos para prefijos 20000, 20 y 21; puede agregar más según las balanzas que use.

---

## Cargar Medios de Pago

**Ruta:** Empresa → Cargar Medios de Pago

Aquí se **definen los medios de pago** que la empresa acepta (efectivo, tarjeta débito, tarjeta crédito, transferencia, Mercado Pago, etc.). Estos medios se usan luego en ventas y en caja para registrar cómo se cobró cada operación.

El Administrador puede **dar de alta, editar y desactivar** medios de pago para que estén disponibles en el resto del sistema.

---

## Resumen por pantalla

| Pantalla | Qué se configura |
|----------|-------------------|
| Datos Empresa | Razón social, CUIT, domicilio, contacto, Mercado Pago (si aplica). |
| Usuarios | Usuarios, contraseñas, perfil (Admin/Vendedor), listas de precio por usuario. |
| Listas de Precio | Listas con base (precio_venta/precio_compra) y descuento %. |
| Formatos de Balanza | Cómo se interpretan los códigos de balanza (prefijo, producto, cantidad/peso). |
| Cargar Medios de Pago | Medios de pago disponibles en ventas y caja. |

Para **inicio de sesión y perfiles**, ver [Inicio de sesión y perfiles](Inicio-de-sesion-y-perfiles). Para **cobro con Mercado Pago**, ver [Integraciones y cobro](Integraciones-y-cobro).
