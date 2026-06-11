# Sistema offline (POS Offline Moon)

El **Sistema offline** es una aplicación de escritorio (Python + Tkinter) que permite **vender sin conexión a internet**. Las ventas se guardan en una base local (SQLite) y se **sincronizan con el servidor** cuando hay conexión. Usa las mismas credenciales que el sistema web y valida el **estado de cuenta** antes de permitir el acceso.

---

## Para qué sirve

- **Vender sin internet:** en locales con cortes de luz o mala conexión.
- **Misma lógica que el sistema web:** productos, clientes y ventas sincronizados con el servidor.
- **Sincronización automática:** cuando hay conexión, se descargan productos y usuarios y se suben las ventas pendientes.
- **Estado de cuenta:** si la cuenta está bloqueada o vencida, no se puede ingresar (igual que en la web).

---

## Requisitos

- **Python 3.7 o superior** instalado en la PC.
- **Windows:** marcar "Add Python to PATH" al instalar Python. Tkinter suele venir incluido.
- **Linux:** `sudo apt-get install python3 python3-tk` (o equivalente).
- **Mac:** Python y Tkinter suelen venir instalados o con `brew install python-tk`.
- **Primera vez:** conexión a internet para configurar y sincronizar productos/usuarios.

---

## Instalación (qué hace cada paso)

### 1. Instalar dependencias

**Comando:** `python install.py` (o `python3 install.py` en Linux/Mac)

**Qué hace este clic/comando:**
- Crea un entorno virtual (`venv/`) en la carpeta del sistema offline.
- Instala las dependencias (requests, sqlalchemy, bcrypt, Pillow, etc.) dentro de ese entorno.
- Crea la carpeta `data/` si no existe (para la base SQLite).
- En Windows puede crear scripts `run.bat` y `setup.bat` para ejecutar sin escribir comandos.

Debe ejecutarse **una sola vez** (o cada vez que se actualicen las dependencias).

---

### 2. Configurar el sistema (primera vez)

**Comando:** `python setup.py` (o `setup.bat` en Windows)

**Qué hace:** abre un asistente que pide:
- **URL del servidor:** la dirección del sistema POS en la web (ej.: `https://tu-dominio.com`). Cada campo se completa y se confirma con Enter o el botón correspondiente.
- **ID Cliente Moon:** el número de cuenta/cliente que te dio el administrador.
- **Intervalo de sincronización:** cada cuántos segundos se intenta sincronizar (por defecto 60).

Al terminar, se guarda un archivo `config.json` con esos datos. Sin este paso, el sistema no sabe a qué servidor conectarse.

Opcionalmente el asistente puede crear **`secrets.env`** (usuario/contraseña para **login automático**) y configurar `auto_login_time` (ej. `08:00`). Ese archivo **no debe subirse a git**.

---

### 3. Ejecutar la aplicación

**Comando:** `python main.py` (o `run.bat` en Windows)

**Login automático:** `python main.py --auto-login` o el script `scripts/iniciar-pos-matutino.sh` / `.bat` en el Programador de tareas / cron.

**Credenciales:** `python main.py --setup-secrets` o `python setup.py` (pregunta al final).

**Qué hace:** si hay `secrets.env` y `auto_login_enabled`, entra directo a caja; si no, muestra **ventana de login**. Luego abre la pantalla principal (listas de precio, medios desde BD, clientes locales, atajos F1/F5/F7/F9).

**Carpeta canónica:** `extras/pos-offline-moon/` (la carpeta `extras/pos-offline/` está deprecada).

---

## Pantalla de login – Cada elemento y cada clic

| Elemento | Qué es | Qué hace |
|----------|--------|----------|
| **Campo "Usuario:"** | Caja de texto | Ahí se escribe el **nombre de usuario** del sistema web. Al hacer clic dentro, se activa para escribir. |
| **Campo "Contraseña:"** | Caja de texto (oculta) | Ahí se escribe la **contraseña**. Los caracteres se muestran como asteriscos. |
| **Texto "🟢 En línea" / "🔴 Sin conexión"** | Etiqueta de estado | Indica si hay conexión a internet. Si hay conexión, al cargar la ventana puede decir "Sincronizando..." y luego "En línea (X usuarios)". |
| **Botón "Ingresar"** | Botón principal | Al hacer **clic**: valida usuario y contraseña (primero contra la base local; si hay conexión y falla, sincroniza y vuelve a intentar). Si todo es correcto, cierra la ventana de login y abre la pantalla principal. Si falta usuario o contraseña, muestra "Complete usuario y contraseña". Si el login falla, muestra el mensaje de error (o "Cuenta bloqueada" si aplica). |
| **Botón "🔄 Sincronizar"** | Botón secundario | Al hacer **clic**: si hay conexión, lanza la sincronización (productos, usuarios, estado de cuenta) y al terminar puede mostrar "Sincronización completada". Si no hay conexión, muestra "No hay conexión a internet". Sirve para actualizar datos antes de ingresar. |
| **Tecla Enter** en el campo contraseña | Atajo | Hace lo mismo que clic en **Ingresar**. |

Si la **cuenta está bloqueada** (estado de cuenta vencido), después de "Ingresar" aparecerá "Acceso bloqueado" con el mensaje correspondiente y no se abrirá la pantalla principal.

---

## Pantalla principal – Estructura y cada clic

La pantalla principal tiene: **barra de menú arriba**, **header con título y estado**, y **tres columnas**: izquierda (búsqueda y productos), centro (cliente, carrito y cobrar), derecha (método de pago y resumen).

---

### Barra de menú (arriba)

| Menú | Opción | Qué hace al hacer clic |
|------|--------|-------------------------|
| **Archivo** | Sincronizar | Igual que el botón Sincronizar: sincroniza productos, usuarios y sube ventas pendientes si hay conexión. Muestra mensaje al terminar. |
| **Archivo** | Salir | Cierra la aplicación por completo. |
| **Productos** | Ver Catálogo | Abre una ventana con la lista completa de productos (código, descripción, precio, stock) en una tabla. Sirve para consultar sin buscar por nombre. |
| **Productos** | Recargar | Vuelve a cargar la lista de productos de la base local en la columna izquierda (útil después de sincronizar). |
| **Ventas** | Ver Ventas (30 días) | Abre una ventana con las ventas de los últimos 30 días guardadas localmente (y ya sincronizadas o pendientes). Permite revisar qué se vendió. |
| **Ayuda** | Atajos: F7=Cobrar, F5=Recargar, F1=Catálogo | Muestra un recordatorio de las teclas rápidas (en algunas versiones solo informa). |

---

### Header (franja superior oscura)

| Elemento | Qué hace |
|----------|----------|
| **Texto "POS \| Moon - Sistema Offline"** | Solo título, no es clicable. |
| **Texto "🟢 En línea" / "🔴 Sin conexión"** | Indica el estado de la conexión en tiempo real. No es un botón. |
| **Texto "👤 [nombre del usuario]"** | Muestra el usuario con el que se ingresó. No es clicable. |

---

### Columna izquierda – Búsqueda y productos

| Elemento | Qué hace |
|----------|----------|
| **Campo de búsqueda (debajo de "🔍 BUSCAR PRODUCTO")** | Al **escribir**, la lista de productos se filtra en tiempo real por código o descripción. No hace falta pulsar Enter. |
| **Botón "📋 Ver Catálogo Completo"** | Mismo efecto que menú **Productos → Ver Catálogo**: abre la ventana con todos los productos. |
| **Tabla de productos (columnas: Código, Descripción, Precio, Stock)** | Muestra los productos disponibles. **Clic en una fila**: la selecciona (queda resaltada). **Doble clic** en una fila o **Enter** con una fila seleccionada: agrega ese producto al carrito con cantidad 1. **Flechas Arriba/Abajo**: mueven la selección sin agregar. |
| **Botón "➕ Agregar Producto Seleccionado (Enter)"** | Agrega al carrito el producto que esté actualmente seleccionado en la tabla, con cantidad 1. Equivalente a pulsar Enter con una fila seleccionada. |

**Atajos:** F5 recarga la lista de productos. F1 abre el catálogo (según versión).

---

### Columna central – Cliente y carrito

| Elemento | Qué hace |
|----------|----------|
| **Campo de solo lectura "👤 CLIENTE"** | Muestra el cliente actual (por defecto "1-Consumidor Final"). No se edita directamente ahí. |
| **Botón "🔍 Buscar"** (junto al cliente) | Al hacer **clic** abre una ventana para buscar y elegir otro cliente. Si elige uno, el campo de cliente se actualiza y las próximas ventas se registrarán a ese cliente. |
| **Tabla del carrito (Cant., Producto, P. Unit., Subtotal)** | Lista los ítems agregados a la venta. **Clic en una fila**: la selecciona para las acciones Aumentar/Disminuir/Eliminar. |
| **Botón "➕ Aumentar"** | Aumenta en 1 la cantidad del ítem **seleccionado** en el carrito. Si no hay ítem seleccionado, puede no hacer nada o dar aviso. |
| **Botón "➖ Disminuir"** | Disminuye en 1 la cantidad del ítem seleccionado. Si la cantidad queda en 0, el ítem puede quitarse del carrito. |
| **Botón "🗑️ Eliminar"** | Quita del carrito el ítem **seleccionado** (una sola fila). |
| **Botón "🗑️ Limpiar Todo"** | Vacía todo el carrito y deja el total en $ 0.00. |
| **Texto "TOTAL A COBRAR:" y el monto** | Muestra el total de la venta (suma de subtotales). Se actualiza solo al agregar, quitar o cambiar cantidades. No es clicable. |
| **Botón "💳 COBRAR VENTA (F7)"** | Al hacer **clic** (o pulsar **F7**): si el carrito está vacío, puede mostrar aviso. Si hay ítems, abre el flujo de cobro: confirma la venta, la guarda en la base local y, si hay conexión, la envía al servidor. Después del cobro, el carrito se vacía. |

---

### Columna derecha – Método de pago y resumen

| Elemento | Qué hace |
|----------|----------|
| **Opciones "💵 Efectivo", "💳 Tarjeta Débito", "💳 Tarjeta Crédito", etc.** | Son **botones de opción (radio)**. Al hacer **clic** en uno, se selecciona ese método de pago para la próxima venta que se cobre. El método elegido se usa cuando se hace clic en **COBRAR VENTA (F7)**. |
| **Resto de la columna (resumen, ayuda)** | Suele mostrar resumen de la venta o textos de ayuda. Según la versión puede haber más botones o información. |

---

## Sincronización – Qué pasa en cada caso

| Situación | Qué hace el sistema |
|-----------|----------------------|
| **Al abrir el login con conexión** | Intenta sincronizar en segundo plano (usuarios, productos, estado de cuenta). La etiqueta puede decir "Sincronizando..." y luego "En línea (X usuarios)". |
| **Clic en "Sincronizar" (login o menú Archivo)** | Ejecuta la sincronización completa: descarga productos y usuarios, sube ventas pendientes, actualiza estado de cuenta. Muestra mensaje al terminar. |
| **Sin conexión** | No puede sincronizar. Las ventas se guardan solo en la base local y se subirán la próxima vez que haya conexión y se ejecute una sincronización. |
| **Cada X minutos (configurable)** | El sistema puede verificar el estado de cuenta en segundo plano. Si la cuenta pasa a bloqueada, puede cerrar la aplicación y mostrar "Cuenta bloqueada". |

---

## Ventana "Ver Ventas (30 días)"

Se abre desde **Ventas → Ver Ventas (30 días)**.

| Elemento | Qué hace |
|----------|----------|
| **Tabla de ventas** | Muestra fecha, cliente, total y estado (sincronizada o pendiente) de cada venta local. |
| **Botón Cerrar / X** | Cierra la ventana y vuelve a la pantalla principal. |

No se editan ni anulan ventas desde esta ventana; es solo consulta.

---

## Ventana "Ver Catálogo"

Se abre desde **Productos → Ver Catálogo** o el botón **📋 Ver Catálogo Completo**.

| Elemento | Qué hace |
|----------|----------|
| **Tabla de productos** | Lista todos los productos con código, descripción, precio, stock. Solo lectura. |
| **Botón Cerrar / X** | Cierra la ventana. |

---

## Resumen de atajos

| Tecla | Acción |
|-------|--------|
| **Enter** (en lista de productos con una fila seleccionada) | Agregar producto al carrito |
| **F7** | Cobrar venta (igual que el botón COBRAR VENTA) |
| **F5** | Recargar lista de productos |
| **F1** | Ver catálogo (según versión) |
| **Enter** (en campo contraseña en login) | Intentar ingresar |

---

## Dónde está el sistema y cómo actualizarlo

- **Carpeta del sistema:** en el proyecto principal suele estar en `extras/pos-offline-moon/`. Ahí están `main.py`, `gui.py`, `sync.py`, `config.json` (después de configurar), etc.
- **Base de datos local:** en `extras/pos-offline-moon/data/pos_local.db` (SQLite). No es necesario abrirla manualmente; el sistema la usa solo.
- **Configuración:** `config.json` en la misma carpeta. Puede editarse a mano para cambiar URL del servidor, ID cliente o intervalo de sincronización.
- **Actualizar:** si te pasan una nueva versión de la carpeta `pos-offline-moon`, reemplazar los archivos (manteniendo `config.json` y `data/` si no te piden borrarlos) y volver a ejecutar `python main.py` o `run.bat`.

---

## Errores frecuentes

| Mensaje o problema | Qué hacer |
|--------------------|-----------|
| "No module named 'tkinter'" | Instalar Tkinter: en Linux `sudo apt-get install python3-tk`, en Windows reinstalar Python marcando componentes estándar. |
| "No hay conexión a internet" | Revisar red/WiFi; el sistema puede usarse offline pero no sincronizará hasta que haya conexión. |
| "Cuenta bloqueada" / "Acceso bloqueado" | Regularizar el pago en el sistema web; después sincronizar desde el offline para actualizar estado de cuenta. |
| "Error de base de datos" | Si se corrompe la base local, se puede renombrar o borrar `data/pos_local.db` y volver a abrir el sistema (se creará una base nueva; habrá que sincronizar de nuevo para tener productos y usuarios). |
| Las ventas no aparecen en el sistema web | Asegurarse de tener conexión y hacer clic en **Archivo → Sincronizar** (o el botón Sincronizar en login) para subir las ventas pendientes. |

---

Para el **sistema web** (crear venta en el navegador, listas de precio, balanza), ver [Ventas](Ventas), [Crear venta – Paso a paso](Crear-venta-paso-a-paso) y [Empresa](Empresa).
