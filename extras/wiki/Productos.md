# Productos

En esta sección se describe la gestión de **productos**, **categorías**, **combos**, la **impresión de precios** y la **importación desde Excel**. Los Administradores tienen acceso completo; los Vendedores suelen tener acceso a Administrar Productos e Imprimir Precios.

---

## Administrar Productos

**Ruta (Admin):** Productos → Administrar Productos  
**Ruta (Vendedor):** menú principal → Administrar Productos

Desde aquí se hace el **ABM (Alta, Baja, Modificación)** de productos.

### Qué datos suele tener un producto

- **Código:** código interno o de barras (único).
- **Descripción / Nombre:** nombre del producto.
- **Categoría:** categoría asignada (desde Categorías).
- **Precio de venta:** precio al que se vende al público (base para listas que usan `precio_venta`).
- **Precio de compra:** costo (base para listas que usan `precio_compra` y para informes).
- **Stock:** cantidad en existencia (el sistema puede actualizarlo con ventas y movimientos).
- **Stock mínimo:** umbral para alertas de stock bajo.
- **Imagen:** foto del producto (si está habilitado).
- **Estado:** activo/inactivo. Los inactivos no suelen mostrarse en ventas.

### Acciones habituales

- **Alta:** botón “Nuevo” o “Agregar” y completar los campos.
- **Editar:** elegir el producto en la lista y usar “Editar” para cambiar datos.
- **Eliminar / Desactivar:** según configuración, puede haber baja lógica (desactivar) o eliminación. Los productos con ventas asociadas suelen desactivarse en lugar de borrarse.

### Búsqueda y filtros

La pantalla suele incluir búsqueda por código o descripción y filtros (por categoría, estado, etc.) para localizar productos rápido.

---

## Categorías

**Ruta:** Productos → Categorías  
**Perfil:** solo Administrador

Aquí se **crean y editan las categorías** de productos (ej.: Lácteos, Fiambres, Limpieza). Cada producto puede asignarse a una categoría; las categorías sirven para:

- Ordenar y filtrar productos.
- Reportes por categoría.
- Impresión de precios por categoría (si está implementado).

Acciones típicas: alta, edición y desactivación de categorías.

---

## Combos

**Ruta:** Productos → Combos  
**Perfil:** solo Administrador

Los **combos** son conjuntos de productos que se venden como una sola oferta (ej.: “Combo desayuno” = café + medialunas + jugo). En esta pantalla se:

- Crean combos (nombre, precio del combo, productos que lo integran).
- Editan o desactivan combos.

En “Crear venta”, al elegir un combo se cargan en el ticket los ítems del combo con el precio definido para el combo.

---

## Imprimir Precios

**Ruta:** Productos → Imprimir Precios  
**Perfil:** Administrador y Vendedor

Sirve para **imprimir etiquetas o listados de precios** (para góndola, mostrador, etc.). Normalmente se puede:

- Elegir productos o categorías.
- Seleccionar formato (tamaño de etiqueta, datos a mostrar: código, descripción, precio).
- Imprimir o previsualizar.

Los precios mostrados suelen ser los vigentes (precio de venta o el que corresponda según configuración).

---

## Importar Excel

**Ruta:** Productos → Importar excel  
**Perfil:** solo Administrador

Permite **cargar o actualizar productos desde un archivo Excel**:

1. Descargar o usar la plantilla que indique el sistema (columnas: código, descripción, categoría, precio venta, precio compra, etc.).
2. Completar el archivo con los productos.
3. Subir el archivo en “Importar excel”.
4. Revisar la vista previa y confirmar la importación.

Así se evita cargar productos uno por uno. Es importante que las columnas coincidan con lo que espera el sistema (código único, categorías existentes, etc.); si hay errores, el sistema suele indicar filas o columnas problemáticas.

---

## Relación con listas de precio y ventas

- En **Crear venta**, el precio del producto se calcula según la **lista de precio** elegida para esa venta (ver [Empresa – Listas de Precio](Empresa#listas-de-precio)).
- Las listas usan como base **precio_venta** o **precio_compra** del producto; por eso es importante mantener esos precios actualizados en Administrar Productos (o mediante Importar Excel).

---

## Relación con la balanza

Si usa **balanza** y **códigos de barras de balanza**, el sistema interpreta el código según los **Formatos de Balanza** configurados en Empresa (ver [Empresa – Formatos de Balanza](Empresa#formatos-de-balanza)). El código debe contener un ID de producto que exista en Administrar Productos; si el producto no existe, el sistema no podrá cargarlo desde el código de balanza.

---

## Resumen por pantalla

| Pantalla | Uso principal |
|----------|----------------|
| Administrar Productos | ABM de productos, precios, stock, categoría. |
| Categorías | ABM de categorías. |
| Combos | Definir combos (productos y precio). |
| Imprimir Precios | Imprimir etiquetas/listados de precios. |
| Importar excel | Carga masiva de productos desde Excel. |

Para **crear una venta** y cómo se aplican las listas de precio, ver [Ventas](Ventas). Para **stock bajo** y reportes de productos, ver [Reportes](Reportes) si está documentado allí.
